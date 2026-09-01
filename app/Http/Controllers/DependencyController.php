<?php

namespace App\Http\Controllers;

use App\Models\DependencyUpgradeHistory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Process;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DependencyController extends Controller
{
    /**
     * =========================================================
     * NPM PATH
     * =========================================================
     */
    private function npmPath(): string
    {
        $paths = [
            'C:\\Program Files\\nodejs\\npm.cmd',
            'C:\\laragon\\bin\\nodejs\\node-v18\\npm.cmd',
            'C:\\laragon\\bin\\nodejs\\node-v20\\npm.cmd',
            'C:\\laragon\\bin\\nodejs\\node-v22\\npm.cmd',
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
     * =========================================================
     * DEPENDENCY DASHBOARD
     * =========================================================
     */
    public function index(Request $request): View
    {
        /*
        |--------------------------------------------------------------------------
        | History Search
        |--------------------------------------------------------------------------
        */
        $historyQuery = DependencyUpgradeHistory::query();

        if ($request->filled('history_search')) {
            $search = trim($request->history_search);

            $historyQuery->where(function ($query) use ($search) {
                $query->where(
                    'package_name',
                    'like',
                    "%{$search}%"
                )->orWhere(
                    'old_version',
                    'like',
                    "%{$search}%"
                )->orWhere(
                    'new_version',
                    'like',
                    "%{$search}%"
                )->orWhere(
                    'message',
                    'like',
                    "%{$search}%"
                );
            });
        }

        /*
        |--------------------------------------------------------------------------
        | History Status Filter
        |--------------------------------------------------------------------------
        */
        if ($request->filled('history_status')) {
            $historyQuery->where(
                'status',
                $request->history_status
            );
        }

        /*
        |--------------------------------------------------------------------------
        | History Sorting
        |--------------------------------------------------------------------------
        */
        $historySort = $request->get(
            'history_sort',
            'created_at'
        );

        $historyOrder = $request->get(
            'history_order',
            'desc'
        );

        $allowedHistorySorts = [
            'package_name',
            'old_version',
            'new_version',
            'status',
            'created_at',
        ];

        if (! in_array(
            $historySort,
            $allowedHistorySorts,
            true
        )) {
            $historySort = 'created_at';
        }

        if (! in_array(
            $historyOrder,
            ['asc', 'desc'],
            true
        )) {
            $historyOrder = 'desc';
        }

        /*
        |--------------------------------------------------------------------------
        | History Pagination
        |--------------------------------------------------------------------------
        */
        $histories = $historyQuery
            ->orderBy(
                $historySort,
                $historyOrder
            )
            ->paginate(5, ['*'], 'history_page')
            ->withQueryString();

        /*
        |--------------------------------------------------------------------------
        | History Statistics
        |--------------------------------------------------------------------------
        */
        $totalHistory = DependencyUpgradeHistory::count();

        $successfulUpgrades =
            DependencyUpgradeHistory::where(
                'status',
                'upgraded'
            )->count();

        $failedUpgrades =
            DependencyUpgradeHistory::where(
                'status',
                'failed'
            )->count();

        /*
        |--------------------------------------------------------------------------
        | Today's Upgrades
        |--------------------------------------------------------------------------
        */
        $todayUpgrades =
            DependencyUpgradeHistory::whereDate(
                'created_at',
                today()
            )->count();

        return view(
            'dependencies.index',
            compact(
                'histories',
                'totalHistory',
                'successfulUpgrades',
                'failedUpgrades',
                'todayUpgrades'
            )
        );
    }

    /**
     * =========================================================
     * LOCAL DEPENDENCY HEALTH CHECK
     * =========================================================
     */
    public function check(): JsonResponse
    {
        try {
            $packageJsonPath = base_path(
                'package.json'
            );

            $nodeModulesPath = base_path(
                'node_modules'
            );

            /*
            |--------------------------------------------------------------------------
            | Check package.json
            |--------------------------------------------------------------------------
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
            |--------------------------------------------------------------------------
            | Read package.json
            |--------------------------------------------------------------------------
            */
            $packageJson = json_decode(
                file_get_contents(
                    $packageJsonPath
                ),
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
            |--------------------------------------------------------------------------
            | Combine dependencies
            |--------------------------------------------------------------------------
            */
            $normalDependencies =
                $packageJson['dependencies'] ?? [];

            $devDependencies =
                $packageJson['devDependencies'] ?? [];

            $declaredDependencies = array_merge(
                $normalDependencies,
                $devDependencies
            );

            $declaredCount =
                count($declaredDependencies);

            /*
            |--------------------------------------------------------------------------
            | node_modules check
            |--------------------------------------------------------------------------
            */
            if (! is_dir($nodeModulesPath)) {
                $dependencies = [];

                foreach (
                    $declaredDependencies
                    as $package => $version
                ) {
                    $dependencies[] = [
                        'name' => $package,
                        'required' => $version,
                        'installed' => null,
                        'status' => 'missing',
                        'type' =>
                        isset(
                            $devDependencies[$package]
                        )
                            ? 'devDependency'
                            : 'dependency',
                    ];
                }

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
                    'dependencies' => $dependencies,
                ]);
            }

            /*
            |--------------------------------------------------------------------------
            | npm ls
            |--------------------------------------------------------------------------
            */
            $result = Process::path(base_path())
                ->timeout(120)
                ->run([
                    $this->npmPath(),
                    'ls',
                    '--depth=0',
                    '--json',
                ]);

            $output = trim(
                $result->output()
            );

            $errorOutput = trim(
                $result->errorOutput()
            );

            $jsonOutput =
                $output !== ''
                ? $output
                : $errorOutput;

            $data = json_decode(
                $jsonOutput,
                true
            );

            /*
            |--------------------------------------------------------------------------
            | Fallback
            |--------------------------------------------------------------------------
            */
            if (! is_array($data)) {
                return $this->localDependencyCheck(
                    $declaredDependencies,
                    $devDependencies,
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

            /*
            |--------------------------------------------------------------------------
            | Build dependency list
            |--------------------------------------------------------------------------
            */
            foreach (
                $declaredDependencies
                as $package => $requiredVersion
            ) {
                $installed =
                    $installedDependencies[$package]
                    ?? null;

                $type =
                    isset(
                        $devDependencies[$package]
                    )
                    ? 'devDependency'
                    : 'dependency';

                /*
                |--------------------------------------------------------------------------
                | Missing
                |--------------------------------------------------------------------------
                */
                if (! is_array($installed)) {
                    $dependencies[] = [
                        'name' => $package,
                        'required' => $requiredVersion,
                        'installed' => null,
                        'status' => 'missing',
                        'type' => $type,
                    ];

                    $missingCount++;

                    continue;
                }

                $installedVersion =
                    $installed['version']
                    ?? null;

                /*
                |--------------------------------------------------------------------------
                | Invalid
                |--------------------------------------------------------------------------
                */
                $isInvalid =
                    isset($installed['invalid'])
                    ||
                    isset($installed['problems']);

                if ($isInvalid) {
                    $dependencies[] = [
                        'name' => $package,
                        'required' => $requiredVersion,
                        'installed' => $installedVersion,
                        'status' => 'invalid',
                        'type' => $type,
                    ];

                    $invalidCount++;

                    continue;
                }

                /*
                |--------------------------------------------------------------------------
                | Installed
                |--------------------------------------------------------------------------
                */
                $dependencies[] = [
                    'name' => $package,
                    'required' => $requiredVersion,
                    'installed' => $installedVersion,
                    'status' => 'installed',
                    'type' => $type,
                ];

                $installedCount++;
            }

            /*
            |--------------------------------------------------------------------------
            | Sort Problems First
            |--------------------------------------------------------------------------
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

                    if (
                        $aPriority !==
                        $bPriority
                    ) {
                        return
                            $aPriority
                            <=>
                            $bPriority;
                    }

                    return strcasecmp(
                        $a['name'],
                        $b['name']
                    );
                }
            );

            $healthy =
                $missingCount === 0
                &&
                $invalidCount === 0;

            /*
            |--------------------------------------------------------------------------
            | Message
            |--------------------------------------------------------------------------
            */
            if ($healthy) {
                $message =
                    'Dependency integrity is healthy. '
                    . $installedCount
                    . ' package(s) installed correctly.';
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
                    . implode(
                        ', ',
                        $problems
                    )
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
     * =========================================================
     * LOCAL FILESYSTEM FALLBACK
     * =========================================================
     */
    private function localDependencyCheck(
        array $declaredDependencies,
        array $devDependencies,
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
                        .
                        str_replace(
                            '/',
                            DIRECTORY_SEPARATOR,
                            $package
                        )
                );

            $packageJson =
                $packagePath
                .
                DIRECTORY_SEPARATOR
                .
                'package.json';

            $type =
                isset(
                    $devDependencies[$package]
                )
                ? 'devDependency'
                : 'dependency';

            if (! file_exists($packageJson)) {
                $dependencies[] = [
                    'name' => $package,
                    'required' => $requiredVersion,
                    'installed' => null,
                    'status' => 'missing',
                    'type' => $type,
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
                ? (
                    $packageData['version']
                    ?? null
                )
                : null;

            $dependencies[] = [
                'name' => $package,
                'required' => $requiredVersion,
                'installed' => $installedVersion,
                'status' => 'installed',
                'type' => $type,
            ];

            $installedCount++;
        }

        usort(
            $dependencies,
            fn($a, $b) =>
            strcasecmp(
                $a['name'],
                $b['name']
            )
        );

        $healthy =
            $missingCount === 0;

        $message =
            $healthy
            ? 'Local dependency integrity is healthy. '
            . $installedCount
            . ' package(s) found.'
            : $missingCount
            . ' dependency(s) are missing from node_modules.';

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
     * =========================================================
     * UPGRADE DEPENDENCY
     * =========================================================
     */
    public function upgrade(
        string $package
    ): JsonResponse {
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
                $this->getInstalledVersion(
                    $package
                );

            $result = Process::path(base_path())
                ->timeout(300)
                ->run([
                    $this->npmPath(),
                    'install',
                    $package . '@latest',
                    '--save',
                ]);

            $after =
                $this->getInstalledVersion(
                    $package
                );

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
                    'output' =>
                    trim(
                        $result->output()
                    ),
                ]);
            }

            $errorMessage =
                trim(
                    $result->errorOutput()
                );

            if ($errorMessage === '') {
                $errorMessage =
                    trim(
                        $result->output()
                    );
            }

            if ($errorMessage === '') {
                $errorMessage =
                    "Failed to upgrade {$package}.";
            }

            $errorMessage =
                $this->cleanNpmError(
                    $errorMessage
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
     * =========================================================
     * GET INSTALLED VERSION
     * =========================================================
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

            $output =
                trim(
                    $result->output()
                );

            if ($output === '') {
                return null;
            }

            $data =
                json_decode(
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
     * =========================================================
     * CLEAN NPM ERROR
     * =========================================================
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

        return implode(
            ' ',
            $usefulLines
        );
    }

    /**
     * =========================================================
     * DELETE SINGLE HISTORY
     * =========================================================
     */
    public function destroy(
        DependencyUpgradeHistory $history
    ): RedirectResponse {
        $history->delete();

        return redirect()
            ->route(
                'dependencies.index'
            )
            ->with(
                'success',
                'History record deleted successfully.'
            );
    }

    /**
     * =========================================================
     * CLEAR ALL HISTORY
     * =========================================================
     */
    public function clearHistory(): RedirectResponse
    {
        DependencyUpgradeHistory::query()->delete();

        return redirect()
            ->route(
                'dependencies.index'
            )
            ->with(
                'success',
                'Dependency history cleared successfully.'
            );
    }

    /**
     * =========================================================
     * BULK DELETE HISTORY
     * =========================================================
     */
    public function bulkDelete(
        Request $request
    ): RedirectResponse {
        $data = $request->validate([
            'history_ids' => [
                'required',
                'array',
                'min:1',
            ],
            'history_ids.*' => [
                'integer',
                'exists:dependency_upgrade_histories,id',
            ],
        ]);

        DependencyUpgradeHistory::whereIn(
            'id',
            $data['history_ids']
        )->delete();

        return redirect()
            ->route(
                'dependencies.index'
            )
            ->with(
                'success',
                count($data['history_ids'])
                    . ' history record(s) deleted successfully.'
            );
    }

    /**
     * =========================================================
     * EXPORT HISTORY CSV
     * =========================================================
     */
    public function exportHistory(
        Request $request
    ): StreamedResponse {
        $histories =
            DependencyUpgradeHistory::query()
            ->latest()
            ->get();

        $fileName =
            'dependency-upgrade-history-'
            . now()->format('Y-m-d-H-i-s')
            . '.csv';

        return response()->streamDownload(
            function () use ($histories) {
                $handle = fopen(
                    'php://output',
                    'w'
                );

                /*
                |--------------------------------------------------------------------------
                | CSV Header
                |--------------------------------------------------------------------------
                */
                fputcsv(
                    $handle,
                    [
                        'ID',
                        'Package',
                        'Old Version',
                        'New Version',
                        'Status',
                        'Message',
                        'Date',
                    ]
                );

                /*
                |--------------------------------------------------------------------------
                | CSV Rows
                |--------------------------------------------------------------------------
                */
                foreach ($histories as $history) {
                    fputcsv(
                        $handle,
                        [
                            $history->id,
                            $history->package_name,
                            $history->old_version,
                            $history->new_version,
                            $history->status,
                            $history->message,
                            optional(
                                $history->created_at
                            )->format(
                                'Y-m-d H:i:s'
                            ),
                        ]
                    );
                }

                fclose($handle);
            },
            $fileName,
            [
                'Content-Type' =>
                'text/csv; charset=UTF-8',
            ]
        );
    }
}
