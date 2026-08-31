<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0">

    <meta
        name="csrf-token"
        content="{{ csrf_token() }}">

    <title>React Dependency Integrity Manager</title>

    @vite(['resources/css/app.css'])

    <style>
        body {
            margin: 0;
            background: #f8fafc;
            color: #0f172a;
            font-family:
                system-ui,
                -apple-system,
                BlinkMacSystemFont,
                "Segoe UI",
                sans-serif;
        }

        .page-shell {
            min-height: 100vh;
        }

        .page-inner {
            max-width: 1200px;
            margin: 0 auto;
            padding: 3rem 1.25rem;
        }

        .hero,
        .panel {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 28px;
            box-shadow: 0 20px 70px rgba(15, 23, 42, .08);
        }

        .hero {
            padding: 2.5rem;
            margin-bottom: 1.5rem;
        }

        .hero h1 {
            margin: 0 0 .75rem;
            font-size: 2.6rem;
        }

        .hero p {
            margin: 0;
            color: #64748b;
            line-height: 1.7;
        }

        .hero-actions {
            margin-top: 1.5rem;
            display: flex;
            flex-wrap: wrap;
            gap: .75rem;
        }

        .panel {
            padding: 2rem;
            margin-bottom: 1.5rem;
        }

        .panel-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .panel-header h2 {
            margin: 0;
            font-size: 1.5rem;
        }

        .button {
            border: none;
            cursor: pointer;
            border-radius: 16px;
            padding: .8rem 1.1rem;
            font-weight: 700;
            background: #4338ca;
            color: white;
            transition: .2s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .button:hover {
            background: #3730a3;
            transform: translateY(-1px);
        }

        .button-danger {
            background: #dc2626;
        }

        .button-danger:hover {
            background: #b91c1c;
        }

        .button-secondary {
            border: 1px solid #cbd5e1;
            background: white;
            color: #334155;
        }

        .button-secondary:hover {
            background: #f8fafc;
        }

        .button:disabled {
            opacity: .6;
            cursor: not-allowed;
            transform: none;
        }

        .status {
            padding: 1rem 1.2rem;
            border-radius: 18px;
            margin-bottom: 1.5rem;
            display: none;
        }

        .status.success {
            display: block;
            background: #ecfdf5;
            color: #065f46;
            border: 1px solid #a7f3d0;
        }

        .status.error {
            display: block;
            background: #fef2f2;
            color: #991b1b;
            border: 1px solid #fecaca;
        }

        .status.warning {
            display: block;
            background: #fffbeb;
            color: #92400e;
            border: 1px solid #fde68a;
        }

        .summary-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .summary-card {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 20px;
            padding: 1.25rem;
        }

        .summary-label {
            color: #64748b;
            font-size: .8rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .04em;
        }

        .summary-value {
            display: block;
            margin-top: .4rem;
            font-size: 2rem;
            font-weight: 800;
        }

        .dependency-list {
            display: grid;
            gap: 1rem;
        }

        .dependency-card {
            border: 1px solid #e2e8f0;
            background: #f8fafc;
            border-radius: 22px;
            padding: 1.25rem;
        }

        .dependency-top {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 1rem;
        }

        .dependency-name {
            font-size: 1.1rem;
            font-weight: 800;
        }

        .dependency-type {
            color: #64748b;
            font-size: .85rem;
            margin-top: .25rem;
        }

        .dependency-info {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: .75rem;
            margin-top: 1rem;
        }

        .info-box {
            background: white;
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            padding: .9rem;
        }

        .info-label {
            display: block;
            color: #64748b;
            font-size: .75rem;
            text-transform: uppercase;
            font-weight: 700;
            margin-bottom: .3rem;
        }

        .info-value {
            font-weight: 800;
            word-break: break-word;
        }

        .dependency-actions {
            margin-top: 1rem;
            display: flex;
            justify-content: flex-end;
        }

        .empty {
            padding: 2rem;
            text-align: center;
            color: #64748b;
            border: 1px dashed #cbd5e1;
            border-radius: 20px;
        }

        .history-table-wrapper {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            padding: 1rem .75rem;
            border-bottom: 1px solid #e2e8f0;
            text-align: left;
            white-space: nowrap;
        }

        th {
            font-size: .8rem;
            color: #64748b;
            text-transform: uppercase;
        }

        .badge {
            display: inline-flex;
            padding: .4rem .7rem;
            border-radius: 999px;
            font-size: .8rem;
            font-weight: 800;
        }

        .badge-success {
            background: #dcfce7;
            color: #166534;
        }

        .badge-danger {
            background: #fee2e2;
            color: #991b1b;
        }

        .badge-warning {
            background: #fef3c7;
            color: #92400e;
        }

        .badge-info {
            background: #dbeafe;
            color: #1e40af;
        }

        .loading {
            display: none;
            color: #64748b;
            margin-bottom: 1rem;
        }

        @media (max-width: 800px) {
            .summary-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 600px) {
            .summary-grid,
            .dependency-info {
                grid-template-columns: 1fr;
            }

            .dependency-top {
                align-items: flex-start;
                flex-direction: column;
            }

            .hero h1 {
                font-size: 2rem;
            }

            .panel {
                padding: 1.25rem;
            }
        }
    </style>
</head>

<body>

<div class="page-shell">

    <div class="page-inner">

        <!-- HERO -->
        <div class="hero">

            <h1>React Dependency Manager</h1>

            <p>
                Verify your locally installed React and npm dependencies,
                detect missing or invalid packages, upgrade dependencies,
                and maintain an upgrade history.
            </p>

            <div class="hero-actions">

                <button
                    type="button"
                    id="checkButton"
                    class="button">
                    Run Integrity Check
                </button>

                <a
                    href="{{ route('products.index') }}"
                    class="button button-secondary">
                    Product Management
                </a>

            </div>

        </div>


        <!-- STATUS -->
        <div
            id="statusMessage"
            class="status">
        </div>


        <!-- DEPENDENCY INTEGRITY -->
        <div class="panel">

            <div class="panel-header">

                <div>
                    <h2>Dependency Integrity</h2>
                </div>

            </div>


            <div
                id="loading"
                class="loading">
                Checking local dependency installation...
            </div>


            <!-- SUMMARY -->
            <div
                id="summaryGrid"
                class="summary-grid"
                style="display:none;">

                <div class="summary-card">

                    <span class="summary-label">
                        Declared
                    </span>

                    <span
                        id="declaredCount"
                        class="summary-value">
                        0
                    </span>

                </div>


                <div class="summary-card">

                    <span class="summary-label">
                        Installed
                    </span>

                    <span
                        id="installedCount"
                        class="summary-value">
                        0
                    </span>

                </div>


                <div class="summary-card">

                    <span class="summary-label">
                        Missing
                    </span>

                    <span
                        id="missingCount"
                        class="summary-value">
                        0
                    </span>

                </div>


                <div class="summary-card">

                    <span class="summary-label">
                        Invalid
                    </span>

                    <span
                        id="invalidCount"
                        class="summary-value">
                        0
                    </span>

                </div>

            </div>


            <div
                id="dependencyList"
                class="dependency-list">

                <div class="empty">

                    <strong>
                        Dependency check not run yet
                    </strong>

                    <br><br>

                    Click
                    <strong>Run Integrity Check</strong>
                    to verify your locally installed npm packages.

                </div>

            </div>

        </div>


        <!-- HISTORY -->
        <div class="panel">

            <div class="panel-header">

                <div>
                    <h2>Upgrade History</h2>
                </div>

                @if ($histories->count() > 0)

                    <form
                        action="{{ route('dependencies.history.clear') }}"
                        method="POST"
                        onsubmit="return confirm('Clear all dependency history?')">

                        @csrf
                        @method('DELETE')

                        <button
                            type="submit"
                            class="button button-danger">

                            Clear History

                        </button>

                    </form>

                @endif

            </div>


            @if (session('success'))

                <div class="status success">
                    {{ session('success') }}
                </div>

            @endif


            @if ($histories->count())

                <div class="history-table-wrapper">

                    <table>

                        <thead>

                        <tr>
                            <th>Package</th>
                            <th>Old Version</th>
                            <th>New Version</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th>Action</th>
                        </tr>

                        </thead>

                        <tbody>

                        @foreach ($histories as $history)

                            <tr>

                                <td>
                                    <strong>
                                        {{ $history->package_name }}
                                    </strong>
                                </td>

                                <td>
                                    {{ $history->old_version ?? '-' }}
                                </td>

                                <td>
                                    {{ $history->new_version ?? '-' }}
                                </td>

                                <td>

                                    @if ($history->status === 'upgraded')

                                        <span class="badge badge-success">
                                            Upgraded
                                        </span>

                                    @elseif ($history->status === 'failed')

                                        <span class="badge badge-danger">
                                            Failed
                                        </span>

                                    @else

                                        <span class="badge badge-info">
                                            {{ ucfirst($history->status) }}
                                        </span>

                                    @endif

                                </td>

                                <td>
                                    {{ $history->created_at->format('d M Y H:i') }}
                                </td>

                                <td>

                                    <form
                                        action="{{ route('dependencies.history.destroy', $history) }}"
                                        method="POST"
                                        onsubmit="return confirm('Delete this history record?')">

                                        @csrf
                                        @method('DELETE')

                                        <button
                                            type="submit"
                                            class="button button-secondary">

                                            Delete

                                        </button>

                                    </form>

                                </td>

                            </tr>

                        @endforeach

                        </tbody>

                    </table>

                </div>

            @else

                <div class="empty">
                    No dependency upgrade history yet.
                </div>

            @endif

        </div>

    </div>

</div>


<script>

    const checkButton =
        document.getElementById('checkButton');

    const dependencyList =
        document.getElementById('dependencyList');

    const loading =
        document.getElementById('loading');

    const statusMessage =
        document.getElementById('statusMessage');

    const summaryGrid =
        document.getElementById('summaryGrid');

    const declaredCount =
        document.getElementById('declaredCount');

    const installedCount =
        document.getElementById('installedCount');

    const missingCount =
        document.getElementById('missingCount');

    const invalidCount =
        document.getElementById('invalidCount');


    const checkUrl =
        @json(route('dependencies.check'));


    const upgradeBaseUrl =
        @json(url('/dependencies'));


    /*
    |--------------------------------------------------------------------------
    | Status
    |--------------------------------------------------------------------------
    */

    function showStatus(
        message,
        type = 'success'
    ) {

        statusMessage.textContent =
            message;

        statusMessage.className =
            `status ${type}`;
    }


    /*
    |--------------------------------------------------------------------------
    | Escape HTML
    |--------------------------------------------------------------------------
    */

    function escapeHtml(value) {

        const div =
            document.createElement('div');

        div.textContent =
            value ?? '';

        return div.innerHTML;
    }


    /*
    |--------------------------------------------------------------------------
    | Render summary
    |--------------------------------------------------------------------------
    */

    function renderSummary(summary) {

        summaryGrid.style.display =
            'grid';

        declaredCount.textContent =
            summary.declared ?? 0;

        installedCount.textContent =
            summary.installed ?? 0;

        missingCount.textContent =
            summary.missing ?? 0;

        invalidCount.textContent =
            summary.invalid ?? 0;
    }


    /*
    |--------------------------------------------------------------------------
    | Render dependencies
    |--------------------------------------------------------------------------
    */

    function renderDependencies(
        dependencies
    ) {

        if (!dependencies.length) {

            dependencyList.innerHTML = `
                <div class="empty">
                    No dependencies were found.
                </div>
            `;

            return;
        }


        dependencyList.innerHTML =
            dependencies.map(
                dependency => {

                    let badgeClass =
                        'badge-success';

                    let badgeText =
                        'Installed';

                    if (
                        dependency.status ===
                        'missing'
                    ) {

                        badgeClass =
                            'badge-danger';

                        badgeText =
                            'Missing';

                    } else if (
                        dependency.status ===
                        'invalid'
                    ) {

                        badgeClass =
                            'badge-warning';

                        badgeText =
                            'Invalid';
                    }


                    const upgradeButton =
                        dependency.status ===
                        'installed'
                            ? `
                                <button
                                    type="button"
                                    class="button upgrade-button"
                                    data-package="${escapeHtml(dependency.name)}">

                                    Upgrade to Latest

                                </button>
                              `
                            : '';


                    return `
                        <div class="dependency-card">

                            <div class="dependency-top">

                                <div>

                                    <div class="dependency-name">
                                        ${escapeHtml(dependency.name)}
                                    </div>

                                    <div class="dependency-type">
                                        ${escapeHtml(dependency.type)}
                                    </div>

                                </div>

                                <span class="badge ${badgeClass}">
                                    ${badgeText}
                                </span>

                            </div>


                            <div class="dependency-info">

                                <div class="info-box">

                                    <span class="info-label">
                                        Required
                                    </span>

                                    <span class="info-value">
                                        ${escapeHtml(dependency.required)}
                                    </span>

                                </div>


                                <div class="info-box">

                                    <span class="info-label">
                                        Installed
                                    </span>

                                    <span class="info-value">
                                        ${escapeHtml(
                                            dependency.installed ?? '-'
                                        )}
                                    </span>

                                </div>

                            </div>


                            <div class="dependency-actions">

                                ${upgradeButton}

                            </div>

                        </div>
                    `;

                }
            ).join('');


        document
            .querySelectorAll('.upgrade-button')
            .forEach(button => {

                button.addEventListener(
                    'click',
                    function () {

                        upgradeDependency(
                            this.dataset.package,
                            this
                        );

                    }
                );

            });
    }


    /*
    |--------------------------------------------------------------------------
    | Run local integrity check
    |--------------------------------------------------------------------------
    */

    async function checkDependencies() {

        checkButton.disabled =
            true;

        loading.style.display =
            'block';

        summaryGrid.style.display =
            'none';

        dependencyList.innerHTML = `
            <div class="empty">
                Checking locally installed dependencies...
            </div>
        `;


        showStatus(
            'Checking local dependency integrity...',
            'success'
        );


        try {

            const response =
                await fetch(
                    checkUrl,
                    {
                        method: 'GET',

                        headers: {
                            'Accept':
                                'application/json',

                            'X-Requested-With':
                                'XMLHttpRequest'
                        }
                    }
                );


            const data =
                await response.json();


            if (
                !response.ok ||
                !data.success
            ) {

                throw new Error(
                    data.message ||
                    'Dependency integrity check failed.'
                );
            }


            renderSummary(
                data.summary
            );


            renderDependencies(
                Array.isArray(
                    data.dependencies
                )
                    ? data.dependencies
                    : []
            );


            if (data.healthy) {

                showStatus(
                    data.message,
                    'success'
                );

            } else {

                showStatus(
                    data.message,
                    'warning'
                );
            }


        } catch (error) {

            dependencyList.innerHTML = `
                <div class="empty">
                    Unable to perform dependency integrity check.
                </div>
            `;


            showStatus(
                error.message ||
                'Dependency integrity check failed.',
                'error'
            );

        } finally {

            checkButton.disabled =
                false;

            loading.style.display =
                'none';
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Upgrade dependency
    |--------------------------------------------------------------------------
    */

    async function upgradeDependency(
        packageName,
        button
    ) {

        if (!packageName) {

            showStatus(
                'Invalid dependency name.',
                'error'
            );

            return;
        }


        const confirmed =
            confirm(
                `Upgrade ${packageName} to the latest version?`
            );


        if (!confirmed) {
            return;
        }


        button.disabled =
            true;

        button.textContent =
            'Upgrading...';


        try {

            const upgradeUrl =
                `${upgradeBaseUrl}/${encodeURIComponent(packageName)}/upgrade`;


            const csrfToken =
                document
                    .querySelector(
                        'meta[name="csrf-token"]'
                    )
                    ?.getAttribute('content');


            const response =
                await fetch(
                    upgradeUrl,
                    {
                        method: 'POST',

                        headers: {

                            'Accept':
                                'application/json',

                            'Content-Type':
                                'application/json',

                            'X-CSRF-TOKEN':
                                csrfToken || '',

                            'X-Requested-With':
                                'XMLHttpRequest'
                        }
                    }
                );


            const data =
                await response.json();


            if (
                !response.ok ||
                !data.success
            ) {

                throw new Error(
                    data.message ||
                    `Failed to upgrade ${packageName}.`
                );
            }


            showStatus(
                `${packageName} upgraded from ` +
                `${data.old_version ?? '-'} to ` +
                `${data.new_version ?? '-'}.`,
                'success'
            );


            await checkDependencies();


            setTimeout(
                () => {
                    window.location.reload();
                },
                800
            );


        } catch (error) {

            showStatus(
                error.message ||
                `Failed to upgrade ${packageName}.`,
                'error'
            );


            button.disabled =
                false;

            button.textContent =
                'Upgrade to Latest';
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Check button
    |--------------------------------------------------------------------------
    */

    checkButton.addEventListener(
        'click',
        checkDependencies
    );

</script>

</body>

</html>