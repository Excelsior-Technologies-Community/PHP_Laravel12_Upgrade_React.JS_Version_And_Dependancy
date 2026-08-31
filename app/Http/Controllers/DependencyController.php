<?php

namespace App\Http\Controllers;

use App\Models\DependencyUpgradeHistory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Process;
use Illuminate\View\View;

class DependencyController extends Controller
{
    /**
     * Absolute path to Windows npm executable.
     */
    private function npmPath(): string
    {
        $paths = [
            'C:\\Program Files\\nodejs\\npm.cmd',
            'C:\\laragon\\bin\\nodejs\\node-v18\\npm.cmd',
        ];

        foreach ($paths as $path) {
            if (file_exists($path)) {
                return $path;
            }
        }

        throw new \RuntimeException(
            'npm.cmd was not found. Please check your Node.js installation.'
        );
    }

    /**
     * Dependency dashboard.
     */
    public function index(): View
    {
        return view('dependencies.index', [
            'histories' => DependencyUpgradeHistory::latest()->get(),
        ]);
    }

    /**
     * Run local dependency integrity check.
     *
     * This does NOT use npm outdated.
     * It does NOT contact the npm registry.
     *
     * It checks the locally installed node_modules tree.
     */
    public function check(): JsonResponse
    {
        try {
            $packageJsonPath = base_path('package.json');
            $nodeModulesPath = base_path('node_modules');

            /*
             * Check package.json.
             */
            if (! file_exists($packageJsonPath)) {
                return response()->json([
                    'success' => false,
                    'healthy' => false,
                    'message' => 'package.json was not found.',
                    'summary' => [
                        'declared' => 0,
                        'installed' => 0,
                        'missing' => 0,
                        'invalid' => 0,
                    ],
                    'dependencies' => [],
                ], 500);
            }

            /*
             * Read package.json.
             */
            $packageJson = json_decode(
                file_get_contents($packageJsonPath),
                true
            );

            if (! is_array($packageJson)) {
                return response()->json([
                    'success' => false,
                    'healthy' => false,
                    'message' => 'Unable to read package.json.',
                    'summary' => [
                        'declared' => 0,
                        'installed' => 0,
                        'missing' => 0,
                        'invalid' => 0,
                    ],
                    'dependencies' => [],
                ], 500);
            }

            /*
             * Combine normal and development dependencies.
             */
            $declaredDependencies = array_merge(
                $packageJson['dependencies'] ?? [],
                $packageJson['devDependencies'] ?? []
            );

            $declaredCount = count($declaredDependencies);

            /*
             * node_modules must exist.
             */
            if (! is_dir($nodeModulesPath)) {
                return response()->json([
                    'success' => true,
                    'healthy' => false,
                    'message' =>
                        'node_modules directory was not found. Run npm install first.',
                    'summary' => [
                        'declared' => $declaredCount,
                        'installed' => 0,
                        'missing' => $declaredCount,
                        'invalid' => 0,
                    ],
                    'dependencies' => array_map(
                        function ($version, $package) {
                            return [
                                'name' => $package,
                                'required' => $version,
                                'installed' => null,
                                'status' => 'missing',
                                'type' => 'dependency',
                            ];
                        },
                        $declaredDependencies,
                        array_keys($declaredDependencies)
                    ),
                ]);
            }

            /*
             * npm ls checks the local dependency tree.
             *
             * npm ls can return exit code 1 when missing or
             * invalid dependencies exist. Therefore we inspect
             * the JSON instead of relying only on successful().
             */
            $result = Process::path(base_path())
                ->timeout(120)
                ->run([
                    $this->npmPath(),
                    'ls',
                    '--depth=0',
                    '--json',
                ]);

            $output = trim($result->output());

            $errorOutput = trim($result->errorOutput());

            /*
             * Prefer stdout.
             */
            $jsonOutput = $output !== ''
                ? $output
                : $errorOutput;

            /*
             * Decode npm response.
             */
            $data = json_decode(
                $jsonOutput,
                true
            );

            /*
             * If JSON cannot be decoded, perform a direct
             * local filesystem check.
             */
            if (! is_array($data)) {
                return $this->localDependencyCheck(
                    $declaredDependencies,
                    $declaredCount,
                    $errorOutput
                );
            }

            $installedDependencies =
                $data['dependencies'] ?? [];

            $dependencies = [];

            $missingCount = 0;
            $invalidCount = 0;
            $installedCount = 0;

            foreach (
                $declaredDependencies
                as $package => $requiredVersion
            ) {
                $installed =
                    $installedDependencies[$package]
                    ?? null;

                /*
                 * Package does not exist.
                 */
                if (! is_array($installed)) {
                    $dependencies[] = [
                        'name' => $package,
                        'required' => $requiredVersion,
                        'installed' => null,
                        'status' => 'missing',
                        'type' => 'dependency',
                    ];

                    $missingCount++;

                    continue;
                }

                $installedVersion =
                    $installed['version'] ?? null;

                /*
                 * npm marks problematic packages using
                 * the "invalid" field.
                 */
                $isInvalid =
                    isset($installed['invalid'])
                    || isset($installed['problems']);

                if ($isInvalid) {
                    $dependencies[] = [
                        'name' => $package,
                        'required' => $requiredVersion,
                        'installed' => $installedVersion,
                        'status' => 'invalid',
                        'type' => 'dependency',
                    ];

                    $invalidCount++;

                    continue;
                }

                $dependencies[] = [
                    'name' => $package,
                    'required' => $requiredVersion,
                    'installed' => $installedVersion,
                    'status' => 'installed',
                    'type' => 'dependency',
                ];

                $installedCount++;
            }

            /*
             * Sort:
             * missing -> invalid -> installed
             */
            usort(
                $dependencies,
                function ($a, $b) {
                    $priority = [
                        'missing' => 1,
                        'invalid' => 2,
                        'installed' => 3,
                    ];

                    $aPriority =
                        $priority[$a['status']] ?? 99;

                    $bPriority =
                        $priority[$b['status']] ?? 99;

                    if ($aPriority !== $bPriority) {
                        return $aPriority <=> $bPriority;
                    }

                    return strcasecmp(
                        $a['name'],
                        $b['name']
                    );
                }
            );

            $healthy =
                $missingCount === 0 &&
                $invalidCount === 0;

            /*
             * Build message.
             */
            if ($healthy) {
                $message =
                    "Dependency integrity is healthy. "
                    . $installedCount
                    . " package(s) installed correctly.";
            } else {
                $problems = [];

                if ($missingCount > 0) {
                    $problems[] =
                        $missingCount
                        . ' missing';
                }

                if ($invalidCount > 0) {
                    $problems[] =
                        $invalidCount
                        . ' invalid';
                }

                $message =
                    'Dependency integrity found: '
                    . implode(', ', $problems)
                    . '.';
            }

            return response()->json([
                'success' => true,

                'healthy' => $healthy,

                'message' => $message,

                'summary' => [
                    'declared' => $declaredCount,
                    'installed' => $installedCount,
                    'missing' => $missingCount,
                    'invalid' => $invalidCount,
                ],

                'dependencies' => $dependencies,

            ]);

        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'healthy' => false,
                'message' => $e->getMessage(),
                'summary' => [
                    'declared' => 0,
                    'installed' => 0,
                    'missing' => 0,
                    'invalid' => 0,
                ],
                'dependencies' => [],
            ], 500);
        }
    }

    /**
     * Direct local dependency check fallback.
     *
     * This does not use the npm registry.
     */
    private function localDependencyCheck(
        array $declaredDependencies,
        int $declaredCount,
        string $npmError = ''
    ): JsonResponse {
        $dependencies = [];

        $installedCount = 0;
        $missingCount = 0;

        foreach (
            $declaredDependencies
            as $package => $requiredVersion
        ) {
            $packagePath =
                base_path(
                    'node_modules/'
                    . str_replace('/', DIRECTORY_SEPARATOR, $package)
                );

            $packageJson =
                $packagePath
                . DIRECTORY_SEPARATOR
                . 'package.json';

            if (! file_exists($packageJson)) {
                $dependencies[] = [
                    'name' => $package,
                    'required' => $requiredVersion,
                    'installed' => null,
                    'status' => 'missing',
                    'type' => 'dependency',
                ];

                $missingCount++;

                continue;
            }

            $packageData = json_decode(
                file_get_contents($packageJson),
                true
            );

            $installedVersion =
                is_array($packageData)
                ? ($packageData['version'] ?? null)
                : null;

            $dependencies[] = [
                'name' => $package,
                'required' => $requiredVersion,
                'installed' => $installedVersion,
                'status' => 'installed',
                'type' => 'dependency',
            ];

            $installedCount++;
        }

        usort(
            $dependencies,
            fn ($a, $b) =>
                strcasecmp($a['name'], $b['name'])
        );

        $healthy = $missingCount === 0;

        if ($healthy) {
            $message =
                "Local dependency integrity is healthy. "
                . $installedCount
                . " package(s) found.";
        } else {
            $message =
                $missingCount
                . " dependency(s) are missing from node_modules.";
        }

        return response()->json([
            'success' => true,

            'healthy' => $healthy,

            'message' => $message,

            'summary' => [
                'declared' => $declaredCount,
                'installed' => $installedCount,
                'missing' => $missingCount,
                'invalid' => 0,
            ],

            'dependencies' => $dependencies,

            'npm_error' => $npmError,
        ]);
    }

    /**
     * Upgrade a selected npm dependency to latest.
     */
    public function upgrade(string $package): JsonResponse
    {
        if (
            ! preg_match(
                '/^(?:@[a-z0-9._-]+\/)?[a-z0-9._-]+$/i',
                $package
            )
        ) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid package name.',
            ], 422);
        }

        $before = null;
        $after = null;

        try {
            $before =
                $this->getInstalledVersion($package);

            $result = Process::path(base_path())
                ->timeout(300)
                ->run([
                    $this->npmPath(),
                    'install',
                    $package . '@latest',
                    '--save',
                ]);

            $after =
                $this->getInstalledVersion($package);

            if ($result->successful()) {
                DependencyUpgradeHistory::create([
                    'package_name' => $package,
                    'old_version' => $before,
                    'new_version' => $after,
                    'status' => 'upgraded',
                    'message' =>
                        'Dependency upgraded successfully.',
                ]);

                return response()->json([
                    'success' => true,
                    'message' =>
                        "{$package} upgraded successfully.",
                    'old_version' => $before,
                    'new_version' => $after,
                    'output' => trim(
                        $result->output()
                    ),
                ]);
            }

            $errorMessage =
                trim($result->errorOutput());

            if ($errorMessage === '') {
                $errorMessage =
                    trim($result->output());
            }

            if ($errorMessage === '') {
                $errorMessage =
                    "Failed to upgrade {$package}.";
            }

            $errorMessage =
                $this->cleanNpmError($errorMessage);

            DependencyUpgradeHistory::create([
                'package_name' => $package,
                'old_version' => $before,
                'new_version' => $after,
                'status' => 'failed',
                'message' => $errorMessage,
            ]);

            return response()->json([
                'success' => false,
                'message' => $errorMessage,
                'old_version' => $before,
                'new_version' => $after,
            ], 500);

        } catch (\Throwable $e) {
            $errorMessage =
                $this->cleanNpmError(
                    $e->getMessage()
                );

            DependencyUpgradeHistory::create([
                'package_name' => $package,
                'old_version' => $before,
                'new_version' => $after,
                'status' => 'failed',
                'message' => $errorMessage,
            ]);

            return response()->json([
                'success' => false,
                'message' => $errorMessage,
                'old_version' => $before,
                'new_version' => $after,
            ], 500);
        }
    }

    /**
     * Get installed package version.
     */
    private function getInstalledVersion(
        string $package
    ): ?string {
        try {
            $result = Process::path(base_path())
                ->timeout(60)
                ->run([
                    $this->npmPath(),
                    'list',
                    $package,
                    '--depth=0',
                    '--json',
                ]);

            $output = trim(
                $result->output()
            );

            if ($output === '') {
                return null;
            }

            $data = json_decode(
                $output,
                true
            );

            if (! is_array($data)) {
                return null;
            }

            return
                $data['dependencies'][$package]['version']
                ?? null;

        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * Clean npm error.
     */
    private function cleanNpmError(
        string $error
    ): string {
        $lines = preg_split(
            '/\r\n|\r|\n/',
            $error
        );

        $usefulLines = [];

        foreach ($lines as $line) {
            $line = trim($line);

            if ($line === '') {
                continue;
            }

            if (
                str_starts_with(
                    strtolower($line),
                    'npm notice'
                )
            ) {
                continue;
            }

            $usefulLines[] = $line;
        }

        if (empty($usefulLines)) {
            return 'Unable to communicate with npm.';
        }

        return implode(' ', $usefulLines);
    }

    /**
     * Delete history record.
     */
    public function destroy(
        DependencyUpgradeHistory $history
    ): RedirectResponse {
        $history->delete();

        return redirect()
            ->route('dependencies.index')
            ->with(
                'success',
                'History record deleted successfully.'
            );
    }

    /**
     * Clear all dependency history.
     */
    public function clearHistory(): RedirectResponse
    {
        DependencyUpgradeHistory::query()->delete();

        return redirect()
            ->route('dependencies.index')
            ->with(
                'success',
                'Dependency history cleared successfully.'
            );
    }
}