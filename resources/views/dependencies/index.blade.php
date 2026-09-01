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

    <title>React Dependency Manager</title>

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

        * {
            box-sizing: border-box;
        }

        .page-shell {
            min-height: 100vh;
        }

        .page-inner {
            max-width: 1250px;
            margin: 0 auto;
            padding: 3rem 1.25rem;
        }

        .hero,
        .panel {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 28px;
            box-shadow:
                0 20px 70px rgba(15, 23, 42, .08);
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
            flex-wrap: wrap;
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

        .button-warning {
            background: #d97706;
        }

        .button-warning:hover {
            background: #b45309;
        }

        .button-success {
            background: #059669;
        }

        .button-success:hover {
            background: #047857;
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

        /*
        |--------------------------------------------------------------------------
        | Status
        |--------------------------------------------------------------------------
        */

        .status {
            padding: 1rem 1.2rem;
            border-radius: 18px;
            margin-bottom: 1.5rem;
        }

        .status.success {
            background: #ecfdf5;
            color: #065f46;
            border: 1px solid #a7f3d0;
        }

        .status.error {
            background: #fef2f2;
            color: #991b1b;
            border: 1px solid #fecaca;
        }

        .status.warning {
            background: #fffbeb;
            color: #92400e;
            border: 1px solid #fde68a;
        }

        /*
        |--------------------------------------------------------------------------
        | Statistics
        |--------------------------------------------------------------------------
        */

        .stats-grid {
            display: grid;
            grid-template-columns:
                repeat(4, 1fr);
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .stat-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 22px;
            padding: 1.4rem;
            box-shadow:
                0 10px 30px rgba(15, 23, 42, .05);
        }

        .stat-label {
            color: #64748b;
            font-size: .8rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: .04em;
        }

        .stat-value {
            display: block;
            margin-top: .45rem;
            font-size: 2.1rem;
            font-weight: 900;
        }

        .stat-description {
            display: block;
            margin-top: .25rem;
            color: #64748b;
            font-size: .85rem;
        }

        /*
        |--------------------------------------------------------------------------
        | Search
        |--------------------------------------------------------------------------
        */

        .filter-grid {
            display: grid;
            grid-template-columns:
                minmax(250px, 1fr)
                180px
                180px
                180px;
            gap: .75rem;
            margin-bottom: 1.5rem;
        }

        .form-input,
        .form-select {
            width: 100%;
            border: 1px solid #cbd5e1;
            border-radius: 16px;
            padding: .85rem 1rem;
            background: white;
            color: #0f172a;
            font-size: .95rem;
        }

        .form-input:focus,
        .form-select:focus {
            outline: none;
            border-color: #6366f1;
            box-shadow:
                0 0 0 4px rgba(99, 102, 241, .12);
        }

        /*
        |--------------------------------------------------------------------------
        | Dependency List
        |--------------------------------------------------------------------------
        */

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

        .dependency-card.hidden {
            display: none;
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
            gap: .5rem;
        }

        /*
        |--------------------------------------------------------------------------
        | Badge
        |--------------------------------------------------------------------------
        */

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

        /*
        |--------------------------------------------------------------------------
        | Pagination
        |--------------------------------------------------------------------------
        */

        .pagination-wrapper {
            margin-top: 1.5rem;
            display: flex;
            justify-content: center;
            gap: .4rem;
            flex-wrap: wrap;
        }

        .pagination-button {
            min-width: 40px;
            height: 40px;
            padding: 0 .7rem;
            border: 1px solid #cbd5e1;
            background: white;
            color: #334155;
            border-radius: 12px;
            cursor: pointer;
            font-weight: 700;
        }

        .pagination-button:hover {
            background: #f1f5f9;
        }

        .pagination-button.active {
            background: #4338ca;
            color: white;
            border-color: #4338ca;
        }

        /*
        |--------------------------------------------------------------------------
        | History
        |--------------------------------------------------------------------------
        */

        .history-filter-grid {
            display: grid;
            grid-template-columns:
                minmax(250px, 1fr)
                180px
                180px
                180px;
            gap: .75rem;
            margin-bottom: 1.5rem;
        }

        .history-actions {
            display: flex;
            flex-wrap: wrap;
            gap: .5rem;
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

        .checkbox {
            width: 18px;
            height: 18px;
            cursor: pointer;
        }

        /*
        |--------------------------------------------------------------------------
        | Empty
        |--------------------------------------------------------------------------
        */

        .empty {
            padding: 2rem;
            text-align: center;
            color: #64748b;
            border: 1px dashed #cbd5e1;
            border-radius: 20px;
        }

        /*
        |--------------------------------------------------------------------------
        | Loading
        |--------------------------------------------------------------------------
        */

        .loading {
            display: none;
            color: #64748b;
            margin-bottom: 1rem;
        }

        /*
        |--------------------------------------------------------------------------
        | Responsive
        |--------------------------------------------------------------------------
        */

        @media (max-width: 1000px) {

            .stats-grid {
                grid-template-columns:
                    repeat(2, 1fr);
            }

            .filter-grid,
            .history-filter-grid {
                grid-template-columns:
                    repeat(2, 1fr);
            }

        }

        @media (max-width: 600px) {

            .stats-grid,
            .filter-grid,
            .history-filter-grid,
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

            .page-inner {
                padding: 1.25rem;
            }

        }

    </style>

</head>

<body>

<div class="page-shell">

<div class="page-inner">

    <!-- =====================================================
         HERO
    ====================================================== -->

    <div class="hero">

        <h1>
            React Dependency Manager
        </h1>

        <p>
            Verify locally installed React and npm dependencies,
            detect missing or invalid packages, upgrade dependencies,
            search and filter packages, and maintain upgrade history.
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

            <a
                href="{{ route('dependencies.history.export') }}"
                class="button button-success">

                Export History CSV

            </a>

        </div>

    </div>


    <!-- =====================================================
         SESSION MESSAGE
    ====================================================== -->

    @if (session('success'))

        <div class="status success">
            {{ session('success') }}
        </div>

    @endif


    <!-- =====================================================
         AJAX STATUS
    ====================================================== -->

    <div
        id="statusMessage"
        class="status"
        style="display:none;">
    </div>


    <!-- =====================================================
         STATISTICS
    ====================================================== -->

    <div class="stats-grid">

        <div class="stat-card">

            <span class="stat-label">
                Total History
            </span>

            <span class="stat-value">
                {{ $totalHistory }}
            </span>

            <span class="stat-description">
                All upgrade attempts
            </span>

        </div>


        <div class="stat-card">

            <span class="stat-label">
                Successful
            </span>

            <span class="stat-value">
                {{ $successfulUpgrades }}
            </span>

            <span class="stat-description">
                Successful upgrades
            </span>

        </div>


        <div class="stat-card">

            <span class="stat-label">
                Failed
            </span>

            <span class="stat-value">
                {{ $failedUpgrades }}
            </span>

            <span class="stat-description">
                Failed upgrades
            </span>

        </div>


        <div class="stat-card">

            <span class="stat-label">
                Today
            </span>

            <span class="stat-value">
                {{ $todayUpgrades }}
            </span>

            <span class="stat-description">
                Today's upgrades
            </span>

        </div>

    </div>


    <!-- =====================================================
         DEPENDENCY INTEGRITY
    ====================================================== -->

    <div class="panel">

        <div class="panel-header">

            <div>

                <h2>
                    Dependency Integrity
                </h2>

                <p style="color:#64748b;">
                    Local node_modules health status
                </p>

            </div>

        </div>


        <!-- =================================================
             DEPENDENCY FILTERS
        ================================================== -->

        <div class="filter-grid">

            <input
                type="text"
                id="dependencySearch"
                class="form-input"
                placeholder="Search dependency...">


            <select
                id="dependencyStatus"
                class="form-select">

                <option value="all">
                    All Status
                </option>

                <option value="installed">
                    Installed
                </option>

                <option value="missing">
                    Missing
                </option>

                <option value="invalid">
                    Invalid
                </option>

            </select>


            <select
                id="dependencySort"
                class="form-select">

                <option value="name-asc">
                    Name A-Z
                </option>

                <option value="name-desc">
                    Name Z-A
                </option>

                <option value="status">
                    Status
                </option>

            </select>


            <button
                type="button"
                id="resetFilters"
                class="button button-secondary">

                Reset Filters

            </button>

        </div>


        <div
            id="loading"
            class="loading">

            Checking locally installed dependencies...

        </div>


        <!-- =================================================
             SUMMARY
        ================================================== -->

        <div
            id="summaryGrid"
            class="stats-grid"
            style="display:none;">

            <div class="stat-card">

                <span class="stat-label">
                    Declared
                </span>

                <span
                    id="declaredCount"
                    class="stat-value">
                    0
                </span>

            </div>


            <div class="stat-card">

                <span class="stat-label">
                    Installed
                </span>

                <span
                    id="installedCount"
                    class="stat-value">
                    0
                </span>

            </div>


            <div class="stat-card">

                <span class="stat-label">
                    Missing
                </span>

                <span
                    id="missingCount"
                    class="stat-value">
                    0
                </span>

            </div>


            <div class="stat-card">

                <span class="stat-label">
                    Invalid
                </span>

                <span
                    id="invalidCount"
                    class="stat-value">
                    0
                </span>

            </div>

        </div>


        <!-- =================================================
             DEPENDENCY LIST
        ================================================== -->

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


        <!-- =================================================
             DEPENDENCY PAGINATION
        ================================================== -->

        <div
            id="dependencyPagination"
            class="pagination-wrapper">
        </div>

    </div>


    <!-- =====================================================
         HISTORY
    ====================================================== -->

    <div class="panel">

        <div class="panel-header">

            <div>

                <h2>
                    Upgrade History
                </h2>

                <p style="color:#64748b;">
                    Track dependency upgrade activity
                </p>

            </div>

            <div class="history-actions">

                @if ($histories->total() > 0)

                    <a
                        href="{{ route('dependencies.history.export') }}"
                        class="button button-success">

                        Export CSV

                    </a>

                @endif

                @if ($totalHistory > 0)

                    <form
                        action="{{ route('dependencies.history.clear') }}"
                        method="POST"
                        onsubmit="return confirm('Clear all dependency history?')">

                        @csrf
                        @method('DELETE')

                        <button
                            type="submit"
                            class="button button-danger">

                            Clear All

                        </button>

                    </form>

                @endif

            </div>

        </div>


        <!-- =================================================
             HISTORY FILTERS
        ================================================== -->

        <form
            method="GET"
            action="{{ route('dependencies.index') }}">

            <div class="history-filter-grid">

                <input
                    type="text"
                    name="history_search"
                    value="{{ request('history_search') }}"
                    class="form-input"
                    placeholder="Search history...">


                <select
                    name="history_status"
                    class="form-select">

                    <option value="">
                        All Status
                    </option>

                    <option
                        value="upgraded"
                        @selected(request('history_status') === 'upgraded')>

                        Upgraded

                    </option>

                    <option
                        value="failed"
                        @selected(request('history_status') === 'failed')>

                        Failed

                    </option>

                </select>


                <select
                    name="history_sort"
                    class="form-select">

                    <option
                        value="created_at"
                        @selected(request('history_sort', 'created_at') === 'created_at')>

                        Date

                    </option>

                    <option
                        value="package_name"
                        @selected(request('history_sort') === 'package_name')>

                        Package

                    </option>

                    <option
                        value="status"
                        @selected(request('history_sort') === 'status')>

                        Status

                    </option>

                    <option
                        value="old_version"
                        @selected(request('history_sort') === 'old_version')>

                        Old Version

                    </option>

                    <option
                        value="new_version"
                        @selected(request('history_sort') === 'new_version')>

                        New Version

                    </option>

                </select>


                <select
                    name="history_order"
                    class="form-select">

                    <option
                        value="desc"
                        @selected(request('history_order', 'desc') === 'desc')>

                        Descending

                    </option>

                    <option
                        value="asc"
                        @selected(request('history_order') === 'asc')>

                        Ascending

                    </option>

                </select>

            </div>


            <div style="display:flex; gap:.5rem; margin-bottom:1.5rem;">

                <button
                    type="submit"
                    class="button">

                    Apply Filters

                </button>

                <a
                    href="{{ route('dependencies.index') }}"
                    class="button button-secondary">

                    Reset

                </a>

            </div>

        </form>


        <!-- =================================================
             BULK DELETE FORM
        ================================================== -->

        @if ($histories->count())

            <form
                id="bulkDeleteForm"
                action="{{ route('dependencies.history.bulk-delete') }}"
                method="POST">

                @csrf
                @method('DELETE')


                <div
                    style="
                        display:flex;
                        justify-content:space-between;
                        align-items:center;
                        gap:1rem;
                        margin-bottom:1rem;
                        flex-wrap:wrap;
                    ">

                    <label
                        style="
                            display:flex;
                            align-items:center;
                            gap:.5rem;
                            font-weight:700;
                        ">

                        <input
                            type="checkbox"
                            id="selectAll"
                            class="checkbox">

                        Select All

                    </label>


                    <button
                        type="submit"
                        id="bulkDeleteButton"
                        class="button button-danger"
                        disabled
                        onclick="return confirm('Delete selected history records?')">

                        Delete Selected

                    </button>

                </div>


                <div class="history-table-wrapper">

                    <table>

                        <thead>

                        <tr>

                            <th>
                                #
                            </th>

                            <th>
                                Package
                            </th>

                            <th>
                                Old Version
                            </th>

                            <th>
                                New Version
                            </th>

                            <th>
                                Status
                            </th>

                            <th>
                                Date
                            </th>

                            <th>
                                Action
                            </th>

                        </tr>

                        </thead>


                        <tbody>

                        @foreach ($histories as $history)

                            <tr>

                                <td>

                                    <input
                                        type="checkbox"
                                        name="history_ids[]"
                                        value="{{ $history->id }}"
                                        class="history-checkbox checkbox">

                                </td>


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

            </form>


            <!-- =================================================
                 HISTORY PAGINATION
            ================================================== -->

            <div class="pagination-wrapper">

                @if ($histories->onFirstPage())

                    <span class="pagination-button">
                        Previous
                    </span>

                @else

                    <a
                        href="{{ $histories->previousPageUrl() }}"
                        class="pagination-button">

                        Previous

                    </a>

                @endif


                @for (
                    $page = 1;
                    $page <= $histories->lastPage();
                    $page++
                )

                    @if ($page == $histories->currentPage())

                        <span class="pagination-button active">
                            {{ $page }}
                        </span>

                    @else

                        <a
                            href="{{ $histories->url($page) }}"
                            class="pagination-button">

                            {{ $page }}

                        </a>

                    @endif

                @endfor


                @if ($histories->hasMorePages())

                    <a
                        href="{{ $histories->nextPageUrl() }}"
                        class="pagination-button">

                        Next

                    </a>

                @else

                    <span class="pagination-button">
                        Next
                    </span>

                @endif

            </div>

        @else

            <div class="empty">

                No dependency upgrade history found.

            </div>

        @endif

    </div>

</div>

</div>


<script>

    /*
    |--------------------------------------------------------------------------
    | Elements
    |--------------------------------------------------------------------------
    */

    const checkButton =
        document.getElementById(
            'checkButton'
        );

    const dependencyList =
        document.getElementById(
            'dependencyList'
        );

    const dependencyPagination =
        document.getElementById(
            'dependencyPagination'
        );

    const loading =
        document.getElementById(
            'loading'
        );

    const statusMessage =
        document.getElementById(
            'statusMessage'
        );

    const summaryGrid =
        document.getElementById(
            'summaryGrid'
        );

    const declaredCount =
        document.getElementById(
            'declaredCount'
        );

    const installedCount =
        document.getElementById(
            'installedCount'
        );

    const missingCount =
        document.getElementById(
            'missingCount'
        );

    const invalidCount =
        document.getElementById(
            'invalidCount'
        );

    const dependencySearch =
        document.getElementById(
            'dependencySearch'
        );

    const dependencyStatus =
        document.getElementById(
            'dependencyStatus'
        );

    const dependencySort =
        document.getElementById(
            'dependencySort'
        );

    const resetFilters =
        document.getElementById(
            'resetFilters'
        );


    /*
    |--------------------------------------------------------------------------
    | URLs
    |--------------------------------------------------------------------------
    */

    const checkUrl =
        @json(route('dependencies.check'));

    const upgradeBaseUrl =
        @json(url('/dependencies'));


    /*
    |--------------------------------------------------------------------------
    | Global dependency data
    |--------------------------------------------------------------------------
    */

    let allDependencies = [];

    let currentDependencyPage = 1;

    const dependencyPerPage = 5;


    /*
    |--------------------------------------------------------------------------
    | Show status
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

        statusMessage.style.display =
            'block';
    }


    /*
    |--------------------------------------------------------------------------
    | Escape HTML
    |--------------------------------------------------------------------------
    */

    function escapeHtml(value) {

        const div =
            document.createElement(
                'div'
            );

        div.textContent =
            value ?? '';

        return div.innerHTML;
    }


    /*
    |--------------------------------------------------------------------------
    | Render summary
    |--------------------------------------------------------------------------
    */

    function renderSummary(
        summary
    ) {

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
    | Filter dependencies
    |--------------------------------------------------------------------------
    */

    function getFilteredDependencies() {

        const search =
            dependencySearch
                .value
                .trim()
                .toLowerCase();

        const status =
            dependencyStatus.value;

        let filtered =
            allDependencies.filter(
                dependency => {

                    const matchesSearch =
                        dependency.name
                            .toLowerCase()
                            .includes(search);

                    const matchesStatus =
                        status === 'all'
                        ||
                        dependency.status === status;

                    return (
                        matchesSearch
                        &&
                        matchesStatus
                    );
                }
            );


        /*
        |--------------------------------------------------------------------------
        | Sorting
        |--------------------------------------------------------------------------
        */

        const sort =
            dependencySort.value;

        if (sort === 'name-asc') {

            filtered.sort(
                (a, b) =>
                    a.name.localeCompare(
                        b.name
                    )
            );

        } else if (sort === 'name-desc') {

            filtered.sort(
                (a, b) =>
                    b.name.localeCompare(
                        a.name
                    )
            );

        } else if (sort === 'status') {

            const priority = {
                missing: 1,
                invalid: 2,
                installed: 3
            };

            filtered.sort(
                (a, b) =>
                    (
                        priority[a.status]
                        -
                        priority[b.status]
                    )
            );
        }

        return filtered;
    }


    /*
    |--------------------------------------------------------------------------
    | Render dependency pagination
    |--------------------------------------------------------------------------
    */

    function renderDependencyPagination(
        total
    ) {

        const totalPages =
            Math.ceil(
                total /
                dependencyPerPage
            );

        dependencyPagination.innerHTML =
            '';

        if (totalPages <= 1) {
            return;
        }

        for (
            let page = 1;
            page <= totalPages;
            page++
        ) {

            const button =
                document.createElement(
                    'button'
                );

            button.type =
                'button';

            button.className =
                'pagination-button'
                +
                (
                    page ===
                    currentDependencyPage
                        ? ' active'
                        : ''
                );

            button.textContent =
                page;

            button.addEventListener(
                'click',
                () => {

                    currentDependencyPage =
                        page;

                    renderDependencies();
                }
            );

            dependencyPagination.appendChild(
                button
            );
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Render dependencies
    |--------------------------------------------------------------------------
    */

    function renderDependencies() {

        const filtered =
            getFilteredDependencies();

        const total =
            filtered.length;

        const totalPages =
            Math.ceil(
                total /
                dependencyPerPage
            );

        if (
            currentDependencyPage >
            totalPages
        ) {
            currentDependencyPage =
                Math.max(
                    1,
                    totalPages
                );
        }

        const start =
            (
                currentDependencyPage
                -
                1
            )
            *
            dependencyPerPage;

        const paginated =
            filtered.slice(
                start,
                start +
                dependencyPerPage
            );


        if (!paginated.length) {

            dependencyList.innerHTML = `
                <div class="empty">
                    No dependencies match your search/filter.
                </div>
            `;

            dependencyPagination.innerHTML =
                '';

            return;
        }


        dependencyList.innerHTML =
            paginated.map(
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

                                        ${escapeHtml(
                                            dependency.name
                                        )}

                                    </div>

                                    <div class="dependency-type">

                                        ${escapeHtml(
                                            dependency.type
                                        )}

                                    </div>

                                </div>


                                <span
                                    class="badge ${badgeClass}">

                                    ${badgeText}

                                </span>

                            </div>


                            <div class="dependency-info">

                                <div class="info-box">

                                    <span class="info-label">
                                        Required
                                    </span>

                                    <span class="info-value">

                                        ${escapeHtml(
                                            dependency.required
                                        )}

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


        /*
        |--------------------------------------------------------------------------
        | Upgrade buttons
        |--------------------------------------------------------------------------
        */

        document
            .querySelectorAll(
                '.upgrade-button'
            )
            .forEach(
                button => {

                    button.addEventListener(
                        'click',
                        function () {

                            upgradeDependency(
                                this.dataset.package,
                                this
                            );

                        }
                    );

                }
            );


        renderDependencyPagination(
            total
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Run dependency check
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
                !response.ok
                ||
                !data.success
            ) {

                throw new Error(
                    data.message
                    ||
                    'Dependency integrity check failed.'
                );
            }


            renderSummary(
                data.summary
            );


            allDependencies =
                Array.isArray(
                    data.dependencies
                )
                    ? data.dependencies
                    : [];


            currentDependencyPage =
                1;


            renderDependencies();


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
                error.message
                ||
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
                    ?.getAttribute(
                        'content'
                    );


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
                                csrfToken
                                ||
                                '',

                            'X-Requested-With':
                                'XMLHttpRequest'

                        }
                    }
                );


            const data =
                await response.json();


            if (
                !response.ok
                ||
                !data.success
            ) {

                throw new Error(
                    data.message
                    ||
                    `Failed to upgrade ${packageName}.`
                );
            }


            showStatus(
                `${packageName} upgraded from `
                +
                `${data.old_version ?? '-'} to `
                +
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
                error.message
                ||
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
    | Search
    |--------------------------------------------------------------------------
    */

    dependencySearch.addEventListener(
        'input',
        () => {

            currentDependencyPage =
                1;

            renderDependencies();

        }
    );


    /*
    |--------------------------------------------------------------------------
    | Status Filter
    |--------------------------------------------------------------------------
    */

    dependencyStatus.addEventListener(
        'change',
        () => {

            currentDependencyPage =
                1;

            renderDependencies();

        }
    );


    /*
    |--------------------------------------------------------------------------
    | Sort
    |--------------------------------------------------------------------------
    */

    dependencySort.addEventListener(
        'change',
        () => {

            currentDependencyPage =
                1;

            renderDependencies();

        }
    );


    /*
    |--------------------------------------------------------------------------
    | Reset
    |--------------------------------------------------------------------------
    */

    resetFilters.addEventListener(
        'click',
        () => {

            dependencySearch.value =
                '';

            dependencyStatus.value =
                'all';

            dependencySort.value =
                'name-asc';

            currentDependencyPage =
                1;

            renderDependencies();

        }
    );


    /*
    |--------------------------------------------------------------------------
    | Check button
    |--------------------------------------------------------------------------
    */

    checkButton.addEventListener(
        'click',
        checkDependencies
    );


    /*
    |--------------------------------------------------------------------------
    | Select All
    |--------------------------------------------------------------------------
    */

    const selectAll =
        document.getElementById(
            'selectAll'
        );

    const historyCheckboxes =
        document.querySelectorAll(
            '.history-checkbox'
        );

    const bulkDeleteButton =
        document.getElementById(
            'bulkDeleteButton'
        );


    function updateBulkDeleteButton() {

        const selected =
            document.querySelectorAll(
                '.history-checkbox:checked'
            ).length;

        if (!bulkDeleteButton) {
            return;
        }

        bulkDeleteButton.disabled =
            selected === 0;

        bulkDeleteButton.textContent =
            selected > 0
                ? `Delete Selected (${selected})`
                : 'Delete Selected';
    }


    if (selectAll) {

        selectAll.addEventListener(
            'change',
            function () {

                historyCheckboxes.forEach(
                    checkbox => {

                        checkbox.checked =
                            this.checked;

                    }
                );

                updateBulkDeleteButton();

            }
        );

    }


    historyCheckboxes.forEach(
        checkbox => {

            checkbox.addEventListener(
                'change',
                updateBulkDeleteButton
            );

        }
    );

</script>

</body>

</html>