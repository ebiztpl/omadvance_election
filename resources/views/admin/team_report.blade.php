@php
    $pageTitle = 'टीम रिपोर्ट';
    $breadcrumbs = [
        'एडमिन' => '#',
        'टीम रिपोर्ट' => '#',
    ];
@endphp

@extends('layouts.app')
@section('title', 'Team Report')

@section('content')
    <div class="container">
        <div class="row page-titles mx-0">
            <div class="col-md-12">
                <form method="GET" id="complaintFilterForm">
                    <div class="row mt-1 align-items-end">
                        <div class="col-md-2">
                            <label>तिथि से</label>
                            <input type="date" name="from_date" id="from_date" class="form-control"
                                value="{{ request('from_date') }}">
                        </div>
                        <div class="col-md-2">
                            <label>तिथि तक</label>
                            <input type="date" name="to_date" id="to_date" class="form-control"
                                value="{{ request('to_date') }}">
                        </div>

                        <div class="col-md-3 d-flex flex-wrap align-items-center mt-2">
                            @php
                                $filterOptions = [
                                    'manager' => 'मैनेजर',
                                    'operator' => 'ऑपरेटर',
                                    'member' => 'कमांडर',
                                ];
                            @endphp
                            @foreach ($filterOptions as $val => $label)
                                <div class="form-check form-check-inline big-radio-box">
                                    <input class="form-check-input summaryRadio" type="radio" name="summary"
                                        id="summary_{{ $val }}" value="{{ $val }}"
                                        {{ request('summary') == $val ? 'checked' : '' }}>
                                    <label class="form-check-label"
                                        for="summary_{{ $val }}">{{ $label }}</label>
                                </div>
                            @endforeach
                        </div>

                        <div class="col-md-3 teamprintExcel">
                            <button type="button" class="btn btn-sm btn-success" onclick="printReport()"
                                style="font-size: 12px; margin-left: 14px">प्रिंट रिपोर्ट</button>
                            <button type="button" class="btn btn-info" style="font-size: 12px;"
                                onclick="exportExcel()">Excel</button>
                        </div>
                    </div>

                    <div class="row mt-1 mb-1">
                        <div class="col-md-2">
                            <label>मैनेजर</label>
                            <select name="manager_id" id="managerSelect" class="form-control" disabled>
                                <option value="">-- सभी --</option>
                                @foreach ($managers as $manager)
                                    <option value="{{ $manager->admin_id }}"
                                        {{ request('manager_id') == $manager->admin_id ? 'selected' : '' }}>
                                        {{ $manager->admin_name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-2">
                            <label>ऑपरेटर</label>
                            <select name="operator_id" id="operatorSelect" class="form-control" disabled>
                                <option value="">-- सभी --</option>
                                @foreach ($offices as $operator)
                                    <option value="{{ $operator->admin_id }}"
                                        {{ request('operator_id') == $operator->admin_id ? 'selected' : '' }}>
                                        {{ $operator->admin_name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-2">
                            <label>कमांडर</label>
                            <select name="commander_id" id="commanderSelect" class="form-control" disabled>
                                <option value="">-- सभी --</option>
                                @foreach ($fields as $commander)
                                    <option value="{{ $commander->member_id }}"
                                        {{ request('commander_id') == $commander->member_id ? 'selected' : '' }}>
                                        {{ $commander->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-1 mt-1">
                            <br>
                            <button type="submit" class="btn btn-primary" id="filterBtn"
                                style="font-size: 12px">फ़िल्टर</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        @php
            $fromDate = request('from_date') ? \Carbon\Carbon::parse(request('from_date'))->format('d-m-Y') : null;
            $toDate = request('to_date') ? \Carbon\Carbon::parse(request('to_date'))->format('d-m-Y') : null;
            $dateRangeText =
                $fromDate && $toDate
                    ? "$fromDate से $toDate"
                    : ($fromDate
                        ? "$fromDate से"
                        : ($toDate
                            ? "$toDate तक"
                            : now()->format('d-m-Y') . ' (तक)'));
        @endphp

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body" id="report-results" style="color: black">
                        @php
                            function sumFraction($current, $addition)
                            {
                                if (strpos($current, '/') === false) {
                                    $current = $current . '/0';
                                }
                                if (strpos($addition, '/') === false) {
                                    $addition = $addition . '/0';
                                }

                                [$curA, $curB] = explode('/', $current);
                                [$addA, $addB] = explode('/', $addition);

                                return $curA + $addA . '/' . ($curB + $addB);
                            }
                        @endphp
                        {{-- @if (!empty($managerReport))
                            @foreach ($managerReport as $managerId => $reports)
                                @if (!empty($reports))
                                    <div class="step-header bg-dark text-white p-2 rounded mt-4">
                                        मैनेजर {{ $managers->firstWhere('admin_id', $managerId)->admin_name ?? '' }}
                                        रिपोर्ट : {{ $dateRangeText }}
                                    </div>

                                    @php
                                        // Initialize totals before the loop
                                        $totals = [
                                            'total' => 0,
                                            'total_replies' => '0/0',
                                            'replies' => 0,
                                            'reply_from' => 0,
                                            'not_forward' => 0,
                                            'total_solved' => 0,
                                            'total_cancelled' => 0,
                                            'total_reviewed' => '0/0',
                                            'total_updates' => 0,
                                        ];
                                    @endphp


                                    <table class="table table-bordered table-sm mt-2" style="color: black">
                                        <thead style="background-color: blanchedalmond">
                                            <tr>
                                                <th>प्रकार</th>
                                                <th>कुल प्राप्त</th>
                                                <th>कुल फॉरवर्ड</th>
                                                <th>कुल जवाब दर्ज</th>
                                                <th>कुल जवाब आगे भेजे</th>
                                                <th>कुल जवाब आगे नहीं भेजे</th>
                                                <th>कुल समाधान</th>
                                                <th>कुल निरस्त</th>
                                                <th>कुल रीव्यू पर</th>
                                                <th>कुल अपडेट</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($reports as $prakar => $data)
                                                @php
                                                    // Sum numeric values
                                                    foreach (
                                                        [
                                                            'total',
                                                            'replies',
                                                            'reply_from',
                                                            'total_solved',
                                                            'not_forward',
                                                            'total_cancelled',
                                                            'total_updates',
                                                        ]
                                                        as $key
                                                    ) {
                                                        $totals[$key] +=
                                                            isset($data[$key]) && is_numeric($data[$key])
                                                                ? $data[$key]
                                                                : 0;
                                                    }
                                                    // Sum fraction values
                                                    foreach (['total_replies', 'total_reviewed'] as $key) {
                                                        if (isset($data[$key])) {
                                                            $totals[$key] = sumFraction($totals[$key], $data[$key]);
                                                        }
                                                    }
                                                @endphp
                                                <tr>
                                                    <td style="font-weight:bold">{{ $prakar }}</td>
                                                    <td>{{ $data['total'] ?? 0 }}</td>
                                                    <td>{{ $data['total_replies'] ?? 0 }}</td>
                                                    <td>{{ $data['replies'] ?? 0 }}</td>
                                                    <td>{{ $data['reply_from'] ?? 0 }}</td>
                                                    <td>{{ $data['not_forward'] ?? 0 }}</td>
                                                    <td>{{ $data['total_solved'] ?? 0 }}</td>
                                                    <td>{{ $data['total_cancelled'] ?? 0 }}</td>
                                                    <td>{{ $data['total_reviewed'] ?? 0 }}</td>
                                                    <td>{{ $data['total_updates'] ?? 0 }}</td>
                                                </tr>
                                            @endforeach
                                            <tr style="font-weight:bold; background-color: #e2e3e5;">
                                                <td>कुल</td>
                                                <td>{{ $totals['total'] }}</td>
                                                <td>{{ $totals['total_replies'] }}</td>
                                                <td>{{ $totals['replies'] }}</td>
                                                <td>{{ $totals['reply_from'] }}</td>
                                                <td>{{ $totals['not_forward'] }}</td>
                                                <td>{{ $totals['total_solved'] }}</td>
                                                <td>{{ $totals['total_cancelled'] }}</td>
                                                <td>{{ $totals['total_reviewed'] }}</td>
                                                <td>{{ $totals['total_updates'] }}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                @endif
                            @endforeach
                        @endif --}}

                        @if (!empty($managerReport))
                            @foreach ($managerReport as $managerId => $reports)
                                @if (!empty($reports))
                                    <div class="step-header bg-dark text-white p-2 rounded mt-4">
                                        मैनेजर {{ $managers->firstWhere('admin_id', $managerId)->admin_name ?? '' }}
                                        रिपोर्ट : {{ $dateRangeText }}
                                    </div>

                                    @php
                                        // Initialize totals before the loop
                                        $totals = [
                                            'total' => 0,
                                            'total_replies' => '0/0',
                                            'replies' => 0,
                                            'reply_from' => 0,
                                            'not_forward' => 0,
                                            'total_solved' => 0,
                                            'total_cancelled' => 0,
                                            'total_reviewed' => '0/0',
                                            'total_updates' => 0,
                                        ];
                                    @endphp

                                    <table class="table table-bordered table-sm mt-2" style="color: black">
                                        <thead style="background-color: blanchedalmond">
                                            <tr>
                                                <th>प्रकार</th>
                                                <th>कुल प्राप्त</th>
                                                <th>कुल फॉरवर्ड</th>
                                                <th>कुल जवाब दर्ज</th>
                                                <th>कुल जवाब आगे भेजे</th>
                                                <th>कुल जवाब आगे नहीं भेजे</th>
                                                <th>कुल समाधान</th>
                                                <th>कुल निरस्त</th>
                                                <th>कुल रीव्यू पर</th>
                                                <th>कुल अपडेट</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($reports as $prakar => $data)
                                                @php
                                                    // Sum numeric values
                                                    foreach (
                                                        [
                                                            'total',
                                                            'replies',
                                                            'reply_from',
                                                            'total_solved',
                                                            'not_forward',
                                                            'total_cancelled',
                                                            'total_updates',
                                                        ]
                                                        as $key
                                                    ) {
                                                        $totals[$key] +=
                                                            isset($data[$key]) && is_numeric($data[$key])
                                                                ? $data[$key]
                                                                : 0;
                                                    }
                                                    // Sum fraction values
                                                    foreach (['total_replies', 'total_reviewed'] as $key) {
                                                        if (isset($data[$key])) {
                                                            $totals[$key] = sumFraction($totals[$key], $data[$key]);
                                                        }
                                                    }

                                                    // Determine base routes based on complaint type
                                                    $isSuchnaType = in_array($prakar, ['शुभ सुचना', 'अशुभ सुचना']);

                                                    if ($isSuchnaType) {
                                                        $operatorRoute = 'operator.suchnas.view';
                                                        $commanderRoute = 'commander.suchnas.view';
                                                    } else {
                                                        $operatorRoute = 'operator.complaint.view';
                                                        $commanderRoute = 'commander.complaint.view';
                                                    }

                                                    // Create base link parameters
                                                    $baseLinkParams = [
                                                        'complaint_type' => $prakar,
                                                        'manager_id' =>
                                                            $managerId !== 'Grand Total' ? $managerId : null,
                                                        'from_date' => request('from_date'),
                                                        'to_date' => request('to_date'),
                                                    ];

                                                    $baseLinkParams = array_filter($baseLinkParams, function ($value) {
                                                        return $value !== null && $value !== '';
                                                    });

                                                    $totalLinkOperator = route(
                                                        $operatorRoute,
                                                        array_merge($baseLinkParams, [
                                                            'managerfilter' => 'total',
                                                            'complaint_ids' =>
                                                                $data['total_link_params']['complaint_ids'] ?? '',
                                                        ]),
                                                    );

                                                    $totalLinkCommander = route(
                                                        $commanderRoute,
                                                        array_merge($baseLinkParams, [
                                                            'managerfilter' => 'total',
                                                            'complaint_ids' =>
                                                                $data['total_link_params']['complaint_ids'] ?? '',
                                                        ]),
                                                    );

                                                    // Replies links
                                                    $repliesLinkOperator = route(
                                                        $operatorRoute,
                                                        array_merge($baseLinkParams, [
                                                            'managerfilter' => 'replies',
                                                            'complaint_ids' =>
                                                                $data['total_replies_link_params']['complaint_ids'] ??
                                                                '',
                                                        ]),
                                                    );

                                                    $repliesLinkCommander = route(
                                                        $commanderRoute,
                                                        array_merge($baseLinkParams, [
                                                            'managerfilter' => 'replies',
                                                            'complaint_ids' =>
                                                                $data['total_replies_link_params']['complaint_ids'] ??
                                                                '',
                                                        ]),
                                                    );

                                                    // Total Replies links
                                                    $totalRepliesLinkOperator = route(
                                                        $operatorRoute,
                                                        array_merge($baseLinkParams, [
                                                            'managerfilter' => 'total_replies',
                                                            'complaint_ids' =>
                                                                $data['total_replies_link_params']['complaint_ids'] ??
                                                                '',
                                                        ]),
                                                    );

                                                    $totalRepliesLinkCommander = route(
                                                        $commanderRoute,
                                                        array_merge($baseLinkParams, [
                                                            'managerfilter' => 'total_replies',
                                                            'complaint_ids' =>
                                                                $data['total_replies_link_params']['complaint_ids'] ??
                                                                '',
                                                        ]),
                                                    );

                                                    // Reply From links
                                                    $replyFromLinkOperator = route(
                                                        $operatorRoute,
                                                        array_merge($baseLinkParams, [
                                                            'managerfilter' => 'reply_from',
                                                            'complaint_ids' =>
                                                                $data['reply_from_link_params']['complaint_ids'] ?? '',
                                                        ]),
                                                    );

                                                    $replyFromLinkCommander = route(
                                                        $commanderRoute,
                                                        array_merge($baseLinkParams, [
                                                            'managerfilter' => 'reply_from',
                                                            'complaint_ids' =>
                                                                $data['reply_from_link_params']['complaint_ids'] ?? '',
                                                        ]),
                                                    );

                                                    // Not Forward links
                                                    $notForwardLinkOperator = route(
                                                        $operatorRoute,
                                                        array_merge($baseLinkParams, [
                                                            'managerfilter' => 'not_forward',
                                                            'complaint_ids' =>
                                                                $data['not_forward_link_params']['complaint_ids'] ?? '',
                                                        ]),
                                                    );

                                                    $notForwardLinkCommander = route(
                                                        $commanderRoute,
                                                        array_merge($baseLinkParams, [
                                                            'managerfilter' => 'not_forward',
                                                            'complaint_ids' =>
                                                                $data['not_forward_link_params']['complaint_ids'] ?? '',
                                                        ]),
                                                    );

                                                    // Total Solved links
                                                    $totalSolvedLinkOperator = route(
                                                        $operatorRoute,
                                                        array_merge($baseLinkParams, [
                                                            'managerfilter' => 'total_solved',
                                                            'complaint_ids' =>
                                                                $data['total_solved_link_params']['complaint_ids'] ??
                                                                '',
                                                        ]),
                                                    );

                                                    $totalSolvedLinkCommander = route(
                                                        $commanderRoute,
                                                        array_merge($baseLinkParams, [
                                                            'managerfilter' => 'total_solved',
                                                            'complaint_ids' =>
                                                                $data['total_solved_link_params']['complaint_ids'] ??
                                                                '',
                                                        ]),
                                                    );

                                                    // Total Cancelled links
                                                    $totalCancelledLinkOperator = route(
                                                        $operatorRoute,
                                                        array_merge($baseLinkParams, [
                                                            'managerfilter' => 'total_cancelled',
                                                            'complaint_ids' =>
                                                                $data['total_cancelled_link_params']['complaint_ids'] ??
                                                                '',
                                                        ]),
                                                    );

                                                    $totalCancelledLinkCommander = route(
                                                        $commanderRoute,
                                                        array_merge($baseLinkParams, [
                                                            'managerfilter' => 'total_cancelled',
                                                            'complaint_ids' =>
                                                                $data['total_cancelled_link_params']['complaint_ids'] ??
                                                                '',
                                                        ]),
                                                    );

                                                    // Total Reviewed links
                                                    $totalReviewedLinkOperator = route(
                                                        $operatorRoute,
                                                        array_merge($baseLinkParams, [
                                                            'managerfilter' => 'total_reviewed',
                                                            'complaint_ids' =>
                                                                $data['total_reviewed_link_params']['complaint_ids'] ??
                                                                '',
                                                        ]),
                                                    );

                                                    $totalReviewedLinkCommander = route(
                                                        $commanderRoute,
                                                        array_merge($baseLinkParams, [
                                                            'managerfilter' => 'total_reviewed',
                                                            'complaint_ids' =>
                                                                $data['total_reviewed_link_params']['complaint_ids'] ??
                                                                '',
                                                        ]),
                                                    );

                                                    // Total Updates links
                                                    $totalUpdatesLinkOperator = route(
                                                        $operatorRoute,
                                                        array_merge($baseLinkParams, [
                                                            'managerfilter' => 'updates_operator',
                                                            'complaint_ids' =>
                                                                $data['updates_operator_link_params'][
                                                                    'complaint_ids'
                                                                ] ?? '',
                                                        ]),
                                                    );

                                                    $totalUpdatesLinkCommander = route(
                                                        $commanderRoute,
                                                        array_merge($baseLinkParams, [
                                                            'managerfilter' => 'updates_commander',
                                                            'complaint_ids' =>
                                                                $data['updates_commander_link_params'][
                                                                    'complaint_ids'
                                                                ] ?? '',
                                                        ]),
                                                    );

                                                    // Check if counts are zero
                                                    $hasTotal = ($data['total'] ?? 0) > 0;
                                                    $hasTotalReplies = ($data['total_replies'] ?? '0 / 0') !== '0 / 0';
                                                    $hasReplies = ($data['replies'] ?? 0) > 0;
                                                    $hasReplyFrom = ($data['reply_from'] ?? 0) > 0;
                                                    $hasNotForward = ($data['not_forward'] ?? 0) > 0;
                                                    $hasTotalSolved = ($data['total_solved'] ?? 0) > 0;
                                                    $hasTotalCancelled = ($data['total_cancelled'] ?? 0) > 0;
                                                    $hasTotalReviewed =
                                                        ($data['total_reviewed'] ?? '0 / 0') !== '0 / 0';
                                                    $hasTotalUpdates = ($data['total_updates'] ?? 0) > 0;

                                                    $repliesCommanderCount = $data['replies_commander'] ?? 0;
                                                    $repliesOperatorCount = $data['replies_operator'] ?? 0;

                                                    $totalRepliesCommanderCount =
                                                        $data['total_replies_commander'] ?? '0/0';
                                                    $totalRepliesOperatorCount =
                                                        $data['total_replies_operator'] ?? '0/0';

                                                    $replyFromCommanderCount = $data['reply_from_commander'] ?? 0;
                                                    $replyFromOperatorCount = $data['reply_from_operator'] ?? 0;

                                                    $notForwardCommanderCount = $data['not_forward_commander'] ?? 0;
                                                    $notForwardOperatorCount = $data['not_forward_operator'] ?? 0;

                                                    $solvedCommanderCount = $data['total_solved_commander'] ?? 0;
                                                    $solvedOperatorCount = $data['total_solved_operator'] ?? 0;

                                                    $cancelledCommanderCount = $data['total_cancelled_commander'] ?? 0;
                                                    $cancelledOperatorCount = $data['total_cancelled_operator'] ?? 0;

                                                    $totalReviewedCommanderCount =
                                                        $data['total_reviewed_commander'] ?? '0/0';
                                                    $totalReviewedOperatorCount =
                                                        $data['total_reviewed_operator'] ?? '0/0';

                                                    $updatesCommanderCount = $data['total_updates_commander'] ?? 0;
                                                    $updatesOperatorCount = $data['total_updates_operator'] ?? 0;

                                                    // Set styles
                                                    $totalStyle = $hasTotal
                                                        ? 'cursor: pointer; color: #593bdb;'
                                                        : 'cursor: default; text-decoration: none; color: #000000;';
                                                    $totalRepliesStyle = $hasTotalReplies
                                                        ? 'cursor: pointer; color: #593bdb;'
                                                        : 'cursor: default; text-decoration: none; color: #000000;';
                                                    $repliesStyle = $hasReplies
                                                        ? 'cursor: pointer; color: #593bdb;'
                                                        : 'cursor: default; text-decoration: none; color: #000000;';
                                                    $replyFromStyle = $hasReplyFrom
                                                        ? 'cursor: pointer; color: #593bdb;'
                                                        : 'cursor: default; text-decoration: none; color: #000000;';
                                                    $notForwardStyle = $hasNotForward
                                                        ? 'cursor: pointer; color: #593bdb;'
                                                        : 'cursor: default; text-decoration: none; color: #000000;';
                                                    $totalSolvedStyle = $hasTotalSolved
                                                        ? 'cursor: pointer; color: #593bdb;'
                                                        : 'cursor: default; text-decoration: none; color: #000000;';
                                                    $totalCancelledStyle = $hasTotalCancelled
                                                        ? 'cursor: pointer; color: #593bdb;'
                                                        : 'cursor: default; text-decoration; color: #000000;';
                                                    $totalReviewedStyle = $hasTotalReviewed
                                                        ? 'cursor: pointer; color: #593bdb;'
                                                        : 'cursor: default; text-decoration: none; color: #000000;';
                                                    $totalUpdatesStyle = $hasTotalUpdates
                                                        ? 'cursor: pointer; color: #593bdb;'
                                                        : 'cursor: default; text-decoration: none; color: #000000;';
                                                @endphp

                                                <tr>
                                                    <td style="font-weight:bold">{{ $prakar }}</td>

                                                    {{-- Total Complaints --}}
                                                    <td>
                                                        @if ($hasTotal)
                                                            <div class="dropdown">
                                                                <a style="{{ $totalStyle }}" class="dropdown-toggle"
                                                                    data-bs-toggle="dropdown" href="#">
                                                                    {{ $data['total'] ?? 0 }}
                                                                </a>
                                                                <ul class="dropdown-menu">
                                                                    <li><a class="dropdown-item" target="_blank"
                                                                            href="{{ $totalLinkOperator }}">
                                                                            ऑपरेटर ({{ $data['operator_total'] ?? 0 }})
                                                                        </a></li>
                                                                    <li><a class="dropdown-item" target="_blank"
                                                                            href="{{ $totalLinkCommander }}">
                                                                            कमांडर ({{ $data['commander_total'] ?? 0 }})
                                                                        </a></li>
                                                                </ul>
                                                            </div>
                                                        @else
                                                            <span
                                                                style="{{ $totalStyle }}">{{ $data['total'] ?? 0 }}</span>
                                                        @endif
                                                    </td>

                                                    {{-- Total Replies (Complaints/Replies) --}}
                                                    <td>
                                                        @if ($hasTotalReplies)
                                                            <div class="dropdown">
                                                                <a style="{{ $totalRepliesStyle }}" class="dropdown-toggle"
                                                                    data-bs-toggle="dropdown" href="#">
                                                                    {{ $data['total_replies'] ?? '0 / 0' }}
                                                                </a>
                                                                <ul class="dropdown-menu">
                                                                    <li><a class="dropdown-item" target="_blank"
                                                                            href="{{ $totalRepliesLinkOperator }}">
                                                                            ऑपरेटर ({{ $totalRepliesOperatorCount }})
                                                                        </a></li>
                                                                    <li><a class="dropdown-item" target="_blank"
                                                                            href="{{ $totalRepliesLinkCommander }}">
                                                                            कमांडर ({{ $totalRepliesCommanderCount }})
                                                                        </a></li>
                                                                </ul>
                                                            </div>
                                                        @else
                                                            <span
                                                                style="{{ $totalRepliesStyle }}">{{ $data['total_replies'] ?? '0/0' }}</span>
                                                        @endif
                                                    </td>

                                                    {{-- Replies Count --}}
                                                    <td>
                                                        @if ($hasReplies)
                                                            <div class="dropdown">
                                                                <a style="{{ $repliesStyle }}" class="dropdown-toggle"
                                                                    data-bs-toggle="dropdown" href="#">
                                                                    {{ $data['replies'] ?? 0 }}
                                                                </a>
                                                                <ul class="dropdown-menu">
                                                                    <li><a class="dropdown-item" target="_blank"
                                                                            href="{{ $repliesLinkOperator }}">
                                                                            ऑपरेटर ({{ $repliesOperatorCount }})
                                                                        </a></li>
                                                                    <li><a class="dropdown-item" target="_blank"
                                                                            href="{{ $repliesLinkCommander }}">
                                                                            कमांडर ({{ $repliesCommanderCount }})
                                                                        </a></li>
                                                                </ul>
                                                            </div>
                                                        @else
                                                            <span
                                                                style="{{ $repliesStyle }}">{{ $data['replies'] ?? 0 }}</span>
                                                        @endif
                                                    </td>

                                                    {{-- Reply From --}}
                                                    <td>
                                                        @if ($hasReplyFrom)
                                                            <div class="dropdown">
                                                                <a style="{{ $replyFromStyle }}" class="dropdown-toggle"
                                                                    data-bs-toggle="dropdown" href="#">
                                                                    {{ $data['reply_from'] ?? 0 }}
                                                                </a>
                                                                <ul class="dropdown-menu">
                                                                    <li><a class="dropdown-item" target="_blank"
                                                                            href="{{ $replyFromLinkOperator }}">
                                                                            ऑपरेटर ({{ $replyFromOperatorCount }})
                                                                        </a></li>
                                                                    <li><a class="dropdown-item" target="_blank"
                                                                            href="{{ $replyFromLinkCommander }}">
                                                                            कमांडर ({{ $replyFromCommanderCount }})
                                                                        </a></li>
                                                                </ul>
                                                            </div>
                                                        @else
                                                            <span
                                                                style="{{ $replyFromStyle }}">{{ $data['reply_from'] ?? 0 }}</span>
                                                        @endif
                                                    </td>

                                                    {{-- Not Forward --}}
                                                    <td>
                                                        @if ($hasNotForward)
                                                            <div class="dropdown">
                                                                <a style="{{ $notForwardStyle }}" class="dropdown-toggle"
                                                                    data-bs-toggle="dropdown" href="#">
                                                                    {{ $data['not_forward'] ?? 0 }}
                                                                </a>
                                                                <ul class="dropdown-menu">
                                                                    <li><a class="dropdown-item" target="_blank"
                                                                            href="{{ $notForwardLinkOperator }}">
                                                                            ऑपरेटर ({{ $notForwardOperatorCount }})
                                                                        </a></li>
                                                                    <li><a class="dropdown-item" target="_blank"
                                                                            href="{{ $notForwardLinkCommander }}">
                                                                            कमांडर ({{ $notForwardCommanderCount }})
                                                                        </a></li>
                                                                </ul>
                                                            </div>
                                                        @else
                                                            <span
                                                                style="{{ $notForwardStyle }}">{{ $data['not_forward'] ?? 0 }}</span>
                                                        @endif
                                                    </td>

                                                    {{-- Total Solved --}}
                                                    <td>
                                                        @if ($hasTotalSolved)
                                                            <div class="dropdown">
                                                                <a style="{{ $totalSolvedStyle }}"
                                                                    class="dropdown-toggle" data-bs-toggle="dropdown"
                                                                    href="#">
                                                                    {{ $data['total_solved'] ?? 0 }}
                                                                </a>
                                                                <ul class="dropdown-menu">
                                                                    <li><a class="dropdown-item" target="_blank"
                                                                            href="{{ $totalSolvedLinkOperator }}">
                                                                            ऑपरेटर ({{ $solvedOperatorCount }})
                                                                        </a></li>
                                                                    <li><a class="dropdown-item" target="_blank"
                                                                            href="{{ $totalSolvedLinkCommander }}">
                                                                            कमांडर ({{ $solvedCommanderCount }})
                                                                        </a></li>
                                                                </ul>
                                                            </div>
                                                        @else
                                                            <span
                                                                style="{{ $totalSolvedStyle }}">{{ $data['total_solved'] ?? 0 }}</span>
                                                        @endif
                                                    </td>

                                                    {{-- Total Cancelled --}}
                                                    <td>
                                                        @if ($hasTotalCancelled)
                                                            <div class="dropdown">
                                                                <a style="{{ $totalCancelledStyle }}"
                                                                    class="dropdown-toggle" data-bs-toggle="dropdown"
                                                                    href="#">
                                                                    {{ $data['total_cancelled'] ?? 0 }}
                                                                </a>
                                                                <ul class="dropdown-menu">
                                                                    <li><a class="dropdown-item" target="_blank"
                                                                            href="{{ $totalCancelledLinkOperator }}">
                                                                            ऑपरेटर ({{ $cancelledOperatorCount }})
                                                                        </a></li>
                                                                    <li><a class="dropdown-item" target="_blank"
                                                                            href="{{ $totalCancelledLinkCommander }}">
                                                                            कमांडर ({{ $cancelledCommanderCount }})
                                                                        </a></li>
                                                                </ul>
                                                            </div>
                                                        @else
                                                            <span
                                                                style="{{ $totalCancelledStyle }}">{{ $data['total_cancelled'] ?? 0 }}</span>
                                                        @endif
                                                    </td>

                                                    {{-- Total Reviewed (Complaints/Replies) --}}
                                                    <td>
                                                        @if ($hasTotalReviewed)
                                                            <div class="dropdown">
                                                                <a style="{{ $totalReviewedStyle }}"
                                                                    class="dropdown-toggle" data-bs-toggle="dropdown"
                                                                    href="#">
                                                                    {{ $data['total_reviewed'] ?? '0/0' }}
                                                                </a>
                                                                <ul class="dropdown-menu">
                                                                    <li><a class="dropdown-item" target="_blank"
                                                                            href="{{ $totalReviewedLinkOperator }}">
                                                                            ऑपरेटर ({{ $totalReviewedOperatorCount }})
                                                                        </a></li>
                                                                    <li><a class="dropdown-item" target="_blank"
                                                                            href="{{ $totalReviewedLinkCommander }}">
                                                                            कमांडर ({{ $totalReviewedCommanderCount }})
                                                                        </a></li>
                                                                </ul>
                                                            </div>
                                                        @else
                                                            <span
                                                                style="{{ $totalReviewedStyle }}">{{ $data['total_reviewed'] ?? '0/0' }}</span>
                                                        @endif
                                                    </td>

                                                    {{-- Total Updates --}}
                                                    <td>
                                                        @if ($hasTotalUpdates)
                                                            <div class="dropdown">
                                                                <a style="{{ $totalUpdatesStyle }}"
                                                                    class="dropdown-toggle" data-bs-toggle="dropdown"
                                                                    href="#">
                                                                    {{ $data['total_updates'] ?? 0 }}
                                                                </a>
                                                                <ul class="dropdown-menu">
                                                                    <li><a class="dropdown-item" target="_blank"
                                                                            href="{{ $totalUpdatesLinkOperator }}">
                                                                            ऑपरेटर ({{ $updatesOperatorCount }})
                                                                        </a></li>
                                                                    <li><a class="dropdown-item" target="_blank"
                                                                            href="{{ $totalUpdatesLinkCommander }}">
                                                                            कमांडर ({{ $updatesCommanderCount }})
                                                                        </a></li>
                                                                </ul>
                                                            </div>
                                                        @else
                                                            <span
                                                                style="{{ $totalUpdatesStyle }}">{{ $data['total_updates'] ?? 0 }}</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                            <tr style="font-weight:bold; background-color: #e2e3e5;">
                                                <td>कुल</td>
                                                <td>{{ $totals['total'] }}</td>
                                                <td>{{ $totals['total_replies'] }}</td>
                                                <td>{{ $totals['replies'] }}</td>
                                                <td>{{ $totals['reply_from'] }}</td>
                                                <td>{{ $totals['not_forward'] }}</td>
                                                <td>{{ $totals['total_solved'] }}</td>
                                                <td>{{ $totals['total_cancelled'] }}</td>
                                                <td>{{ $totals['total_reviewed'] }}</td>
                                                <td>{{ $totals['total_updates'] }}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                @endif
                            @endforeach
                        @endif

                        {{-- Operator Report --}}
                        {{-- @if (!empty($reportOperator))
                            @foreach ($reportOperator as $operatorId => $data)
                                @if (!empty($data))
                                    <div class="step-header bg-dark text-white p-2 rounded mt-4">
                                        {{ $offices->firstWhere('admin_id', $operatorId)->admin_name ?? 'ऑपरेटर रिपोर्ट' }}:
                                        {{ $dateRangeText }}
                                    </div>

                                    @php
                                        $totals = [
                                            'total' => 0,
                                            'followups' => '0/0',
                                            'completed_followups' => 0,
                                            'overall_incoming' => 0,
                                            'incoming_reason1' => '0/0',
                                            'incoming_reason2' => 0,
                                            'incoming_reason3' => 0,
                                        ];
                                    @endphp


                                    <table class="table table-bordered table-sm mt-2" style="color: black">
                                        <thead style="background-color: blanchedalmond">
                                            <tr>
                                                <th>प्रकार</th>
                                                <th>कुल पंजीकृत</th>
                                                <th>कुल फ़ॉलोअप</th>
                                                <th>पूर्ण फ़ॉलोअप</th>
                                                <th>कुल प्राप्त कॉल</th>
                                                <th>प्राप्त फ़ॉलोअप प्रतिक्रिया</th>
                                                <th>समस्या स्थिति</th>
                                                <th>समस्या स्थिति अपडेट</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($data as $prakar => $d)
                                                @php
                                                    // Sum numeric values
                                                    foreach (
                                                        [
                                                            'total',
                                                            'completed_followups',
                                                            'overall_incoming',
                                                            'incoming_reason2',
                                                            'incoming_reason3',
                                                        ]
                                                        as $key
                                                    ) {
                                                        $totals[$key] +=
                                                            isset($d[$key]) && is_numeric($d[$key]) ? $d[$key] : 0;
                                                    }

                                                    // Sum fraction-like values
                                                    if (isset($d['followups'])) {
                                                        $totals['followups'] = sumFraction(
                                                            $totals['followups'],
                                                            $d['followups'],
                                                        );
                                                    }

                                                    if (isset($d['incoming_reason1'])) {
                                                        $totals['incoming_reason1'] = sumFraction(
                                                            $totals['incoming_reason1'],
                                                            $d['incoming_reason1'],
                                                        );
                                                    }
                                                @endphp

                                                <tr>
                                                    <td style="font-weight:bold">{{ $prakar }}</td>
                                                    <td>{{ $d['total'] ?? 0 }}</td>
                                                    <td>{{ $d['followups'] ?? '-' }}</td>
                                                    <td>{{ $d['completed_followups'] ?? '-' }}</td>
                                                    <td>{{ $d['overall_incoming'] ?? '-' }}</td>
                                                    <td>{{ $d['incoming_reason1'] ?? '-' }}</td>
                                                    <td>{{ $d['incoming_reason2'] ?? '-' }}</td>
                                                    <td>{{ $d['incoming_reason3'] ?? '-' }}</td>
                                                </tr>
                                            @endforeach
                                            <tr style="font-weight:bold; background-color: #e2e3e5;">
                                                <td>कुल</td>
                                                <td>{{ $totals['total'] }}</td>
                                                <td>{{ $totals['followups'] }}</td>
                                                <td>{{ $totals['completed_followups'] }}</td>
                                                <td>{{ $totals['overall_incoming'] }}</td>
                                                <td>{{ $totals['incoming_reason1'] }}</td>
                                                <td>{{ $totals['incoming_reason2'] }}</td>
                                                <td>{{ $totals['incoming_reason3'] }}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                @endif
                            @endforeach
                        @endif --}}

                        @if (!empty($reportOperator))
                            @foreach ($reportOperator as $operatorId => $data)
                                @if (!empty($data))
                                    <div class="step-header bg-dark text-white p-2 rounded mt-4">
                                        {{ $offices->firstWhere('admin_id', $operatorId)->admin_name ?? 'ऑपरेटर रिपोर्ट' }}:
                                        {{ $dateRangeText }}
                                    </div>

                                    @php
                                        $totals = [
                                            'total' => 0,
                                            'followups' => '0/0',
                                            'completed_followups' => 0,
                                            'overall_incoming' => 0,
                                            'incoming_reason1' => '0/0',
                                            'incoming_reason2' => 0,
                                            'incoming_reason3' => 0,
                                        ];
                                    @endphp

                                    <table class="table table-bordered table-sm mt-2" style="color: black">
                                        <thead style="background-color: blanchedalmond">
                                            <tr>
                                                <th>प्रकार</th>
                                                <th>कुल पंजीकृत</th>
                                                <th>कुल फ़ॉलोअप</th>
                                                <th>पूर्ण फ़ॉलोअप</th>
                                                <th>कुल प्राप्त कॉल</th>
                                                <th>प्राप्त फ़ॉलोअप प्रतिक्रिया</th>
                                                <th>समस्या स्थिति</th>
                                                <th>समस्या स्थिति अपडेट</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($data as $prakar => $d)
                                                @php
                                                    foreach (
                                                        [
                                                            'total',
                                                            'completed_followups',
                                                            'overall_incoming',
                                                            'incoming_reason2',
                                                            'incoming_reason3',
                                                        ]
                                                        as $key
                                                    ) {
                                                        $totals[$key] +=
                                                            isset($d[$key]) && is_numeric($d[$key]) ? $d[$key] : 0;
                                                    }
                                                    foreach (['followups', 'incoming_reason1'] as $key) {
                                                        if (isset($d[$key])) {
                                                            $totals[$key] = sumFraction($totals[$key], $d[$key]);
                                                        }
                                                    }

                                                    $isSuchnaType = in_array($prakar, [
                                                        'शुभ सुचना',
                                                        'अशुभ सुचना',
                                                        'सुझाव',
                                                    ]);

                                                    // Determine base routes based on complaint type
                                                    if ($isSuchnaType) {
                                                        $operatorRoute = 'operator.suchnas.view';
                                                        $commanderRoute = 'commander.suchnas.view';
                                                    } else {
                                                        $operatorRoute = 'operator.complaint.view';
                                                        $commanderRoute = 'commander.complaint.view';
                                                    }

                                                    $baseLinkParams = [
                                                        'complaint_type' => $prakar,
                                                        'operator_id' =>
                                                            $operatorId !== 'Grand Total' ? $operatorId : null,
                                                        'from_date' => request('from_date'),
                                                        'to_date' => request('to_date'),
                                                    ];

                                                    // Total complaints link
                                                    $totalLink = route(
                                                        $operatorRoute,
                                                        array_merge($baseLinkParams, ['filter' => 'all']),
                                                    );

                                                    // Followups links - using original link parameters
                                                      $followupLinkOperator = route(
        $operatorRoute,
        array_merge($baseLinkParams, [
            'operatorfilter' => 'followups',
            'followup_complaint_ids' => $d['followup_link_params']['followup_complaint_ids'] ?? '',
        ]),
    );

    $followupLinkCommander = route(
        $commanderRoute,
        array_merge($baseLinkParams, [
            'operatorfilter' => 'followups',
            'followup_complaint_ids' => $d['followup_link_params_commander']['followup_complaint_ids'] ?? '',
        ]),
    );

                                                    // Completed Followups links - using original link parameters
                                                    $completedFollowupLinkOperator = route(
                                                        $operatorRoute,
                                                        array_merge($baseLinkParams, [
                                                            'operatorfilter' => 'completed_followups',
                                                            'followup_status' => 2,
                                                        ]),
                                                    );

                                                    $completedFollowupLinkCommander = route(
                                                        $commanderRoute,
                                                        array_merge($baseLinkParams, [
                                                            'operatorfilter' => 'completed_followups',
                                                            'followup_status' => 2,
                                                        ]),
                                                    );

                                                    // Overall Incoming links - using original link parameters
                                                    $overallIncomingLinkOperator = route(
                                                        $operatorRoute,
                                                        array_merge($baseLinkParams, [
                                                            'operatorfilter' => 'overall_incoming',
                                                            'incoming_complaint_ids' =>
                                                                $d['overall_incoming_link_params'][
                                                                    'incoming_complaint_ids'
                                                                ] ?? '',
                                                        ]),
                                                    );

                                                    $overallIncomingLinkCommander = route(
                                                        $commanderRoute,
                                                        array_merge($baseLinkParams, [
                                                            'operatorfilter' => 'overall_incoming',
                                                            'incoming_complaint_ids' =>
                                                                $d['overall_incoming_link_params'][
                                                                    'incoming_complaint_ids'
                                                                ] ?? '',
                                                        ]),
                                                    );

                                                    // Incoming Reason 1 links - using original link parameters
                                                    $incomingReason1LinkOperator = route(
                                                        $operatorRoute,
                                                        array_merge($baseLinkParams, [
                                                            'operatorfilter' => 'incoming_reason1',
                                                            'incoming_complaint_ids' =>
                                                                $d['incoming_reason1_link_params'][
                                                                    'incoming_complaint_ids'
                                                                ] ?? '',
                                                        ]),
                                                    );

                                                    $incomingReason1LinkCommander = route(
                                                        $commanderRoute,
                                                        array_merge($baseLinkParams, [
                                                            'operatorfilter' => 'incoming_reason1',
                                                            'incoming_complaint_ids' =>
                                                                $d['incoming_reason1_link_params'][
                                                                    'incoming_complaint_ids'
                                                                ] ?? '',
                                                        ]),
                                                    );

                                                    // Incoming Reason 2 links - using original link parameters
                                                    $incomingReason2LinkOperator = route(
                                                        $operatorRoute,
                                                        array_merge($baseLinkParams, [
                                                            'operatorfilter' => 'incoming_reason2',
                                                            'incoming_complaint_ids' =>
                                                                $d['incoming_reason2_link_params'][
                                                                    'incoming_complaint_ids'
                                                                ] ?? '',
                                                        ]),
                                                    );

                                                    $incomingReason2LinkCommander = route(
                                                        $commanderRoute,
                                                        array_merge($baseLinkParams, [
                                                            'operatorfilter' => 'incoming_reason2',
                                                            'incoming_complaint_ids' =>
                                                                $d['incoming_reason2_link_params'][
                                                                    'incoming_complaint_ids'
                                                                ] ?? '',
                                                        ]),
                                                    );

                                                    // Incoming Reason 3 links - using original link parameters
                                                    $incomingReason3LinkOperator = route(
                                                        $operatorRoute,
                                                        array_merge($baseLinkParams, [
                                                            'operatorfilter' => 'incoming_reason3',
                                                            'incoming_complaint_ids' =>
                                                                $d['incoming_reason3_link_params'][
                                                                    'incoming_complaint_ids'
                                                                ] ?? '',
                                                        ]),
                                                    );

                                                    $incomingReason3LinkCommander = route(
                                                        $commanderRoute,
                                                        array_merge($baseLinkParams, [
                                                            'operatorfilter' => 'incoming_reason3',
                                                            'incoming_complaint_ids' =>
                                                                $d['incoming_reason3_link_params'][
                                                                    'incoming_complaint_ids'
                                                                ] ?? '',
                                                        ]),
                                                    );

                                                    // Check if counts are zero
                                                    $hasTotal = ($d['total'] ?? 0) > 0;
                                                    $hasFollowups = ($d['followups'] ?? '0 / 0') !== '0 / 0';
                                                    $hasCompletedFollowups = ($d['completed_followups'] ?? 0) > 0;
                                                    $hasOverallIncoming = ($d['overall_incoming'] ?? 0) > 0;
                                                    $hasIncomingReason1 =
                                                        ($d['incoming_reason1'] ?? '0 / 0') !== '0 / 0';
                                                    $hasIncomingReason2 = ($d['incoming_reason2'] ?? 0) > 0;
                                                    $hasIncomingReason3 = ($d['incoming_reason3'] ?? 0) > 0;

                                                    // Set CSS styles
                                                    $totalStyle = $hasTotal
                                                        ? 'cursor: pointer; color: #593bdb;'
                                                        : 'cursor: default; text-decoration: none; color: #000000;';
                                                    $followupStyle = $hasFollowups
                                                        ? 'cursor: pointer; color: #593bdb;'
                                                        : 'cursor: default; text-decoration: none; color: #000000;';
                                                    $completedFollowupStyle = $hasCompletedFollowups
                                                        ? 'cursor: pointer; color: #593bdb;'
                                                        : 'cursor: default; text-decoration: none; color: #000000;';
                                                    $overallIncomingStyle = $hasOverallIncoming
                                                        ? 'cursor: pointer; color: #593bdb;'
                                                        : 'cursor: default; text-decoration: none; color: #000000;';
                                                    $incomingReason1Style = $hasIncomingReason1
                                                        ? 'cursor: pointer; color: #593bdb;'
                                                        : 'cursor: default; text-decoration: none; color: #000000;';
                                                    $incomingReason2Style = $hasIncomingReason2
                                                        ? 'cursor: pointer; color: #593bdb;'
                                                        : 'cursor: default; text-decoration: none; color: #000000;';
                                                    $incomingReason3Style = $hasIncomingReason3
                                                        ? 'cursor: pointer; color: #593bdb;'
                                                        : 'cursor: default; text-decoration: none; color: #000000;';
                                                @endphp

                                                <tr>
                                                    <td style="font-weight:bold">{{ $prakar }}</td>

                                                    {{-- Total Complaints --}}
                                                    <td>
                                                        @if ($hasTotal)
                                                            <a style="{{ $totalStyle }}" target="_blank"
                                                                href="{{ $totalLink }}">
                                                                {{ $d['total'] ?? 0 }}
                                                            </a>
                                                        @else
                                                            <span
                                                                style="{{ $totalStyle }}">{{ $d['total'] ?? 0 }}</span>
                                                        @endif
                                                    </td>

                                                    {{-- Followups (Complaints/Replies) --}}
                                                    <td>
                                                        @if ($hasFollowups)
                                                            <div class="dropdown">
                                                                <a style="{{ $followupStyle }}" class="dropdown-toggle"
                                                                    data-bs-toggle="dropdown" href="#">
                                                                    {{ $d['followups'] ?? '0/0' }}
                                                                </a>
                                                                <ul class="dropdown-menu">
                                                                    <li><a class="dropdown-item" target="_blank"
                                                                            href="{{ $followupLinkOperator }}">
                                                                            ऑपरेटर ({{ $d['followups_operator'] ?? 0 }})
                                                                        </a></li>
                                                                    <li><a class="dropdown-item" target="_blank"
                                                                            href="{{ $followupLinkCommander }}">
                                                                            कमांडर ({{ $d['followups_commander'] ?? 0 }})
                                                                        </a></li>
                                                                </ul>
                                                            </div>
                                                        @else
                                                            <span
                                                                style="{{ $followupStyle }}">{{ $d['followups'] ?? '0/0' }}</span>
                                                        @endif
                                                    </td>

                                                    {{-- Completed Followups --}}
                                                    <td>
                                                        @if ($hasCompletedFollowups)
                                                            <div class="dropdown">
                                                                <a style="{{ $completedFollowupStyle }}"
                                                                    class="dropdown-toggle" data-bs-toggle="dropdown"
                                                                    href="#">
                                                                    {{ $d['completed_followups'] ?? 0 }}
                                                                </a>
                                                                <ul class="dropdown-menu">
                                                                    <li><a class="dropdown-item" target="_blank"
                                                                            href="{{ $completedFollowupLinkOperator }}">
                                                                            ऑपरेटर
                                                                            ({{ $d['completed_followups_operator'] ?? 0 }})
                                                                        </a></li>
                                                                    <li><a class="dropdown-item" target="_blank"
                                                                            href="{{ $completedFollowupLinkCommander }}">
                                                                            कमांडर
                                                                            ({{ $d['completed_followups_commander'] ?? 0 }})
                                                                        </a></li>
                                                                </ul>
                                                            </div>
                                                        @else
                                                            <span
                                                                style="{{ $completedFollowupStyle }}">{{ $d['completed_followups'] ?? 0 }}</span>
                                                        @endif
                                                    </td>

                                                    {{-- Overall Incoming --}}
                                                    <td>
                                                        @if ($hasOverallIncoming)
                                                            <div class="dropdown">
                                                                <a style="{{ $overallIncomingStyle }}"
                                                                    class="dropdown-toggle" data-bs-toggle="dropdown"
                                                                    href="#">
                                                                    {{ $d['overall_incoming'] ?? 0 }}
                                                                </a>
                                                                <ul class="dropdown-menu">
                                                                    <li><a class="dropdown-item" target="_blank"
                                                                            href="{{ $overallIncomingLinkOperator }}">
                                                                            ऑपरेटर
                                                                            ({{ $d['overall_incoming_operator'] ?? 0 }})
                                                                        </a></li>
                                                                    <li><a class="dropdown-item" target="_blank"
                                                                            href="{{ $overallIncomingLinkCommander }}">
                                                                            कमांडर
                                                                            ({{ $d['overall_incoming_commander'] ?? 0 }})
                                                                        </a></li>
                                                                </ul>
                                                            </div>
                                                        @else
                                                            <span
                                                                style="{{ $overallIncomingStyle }}">{{ $d['overall_incoming'] ?? 0 }}</span>
                                                        @endif
                                                    </td>

                                                    {{-- Incoming Reason 1 --}}
                                                    <td>
                                                        @if ($hasIncomingReason1)
                                                            <div class="dropdown">
                                                                <a style="{{ $incomingReason1Style }}"
                                                                    class="dropdown-toggle" data-bs-toggle="dropdown"
                                                                    href="#">
                                                                    {{ $d['incoming_reason1'] ?? '0/0' }}
                                                                </a>
                                                                <ul class="dropdown-menu">
                                                                    <li><a class="dropdown-item" target="_blank"
                                                                            href="{{ $incomingReason1LinkOperator }}">
                                                                            ऑपरेटर
                                                                            ({{ $d['incoming_reason1_operator'] ?? 0 }})
                                                                        </a></li>
                                                                    <li><a class="dropdown-item" target="_blank"
                                                                            href="{{ $incomingReason1LinkCommander }}">
                                                                            कमांडर
                                                                            ({{ $d['incoming_reason1_commander'] ?? 0 }})
                                                                        </a></li>
                                                                </ul>
                                                            </div>
                                                        @else
                                                            <span
                                                                style="{{ $incomingReason1Style }}">{{ $d['incoming_reason1'] ?? '0/0' }}</span>
                                                        @endif
                                                    </td>

                                                    {{-- Incoming Reason 2 --}}
                                                    <td>
                                                        @if ($hasIncomingReason2)
                                                            <div class="dropdown">
                                                                <a style="{{ $incomingReason2Style }}"
                                                                    class="dropdown-toggle" data-bs-toggle="dropdown"
                                                                    href="#">
                                                                    {{ $d['incoming_reason2'] ?? 0 }}
                                                                </a>
                                                                <ul class="dropdown-menu">
                                                                    <li><a class="dropdown-item" target="_blank"
                                                                            href="{{ $incomingReason2LinkOperator }}">
                                                                            ऑपरेटर
                                                                            ({{ $d['incoming_reason2_operator'] ?? 0 }})
                                                                        </a></li>
                                                                    <li><a class="dropdown-item" target="_blank"
                                                                            href="{{ $incomingReason2LinkCommander }}">
                                                                            कमांडर
                                                                            ({{ $d['incoming_reason2_commander'] ?? 0 }})
                                                                        </a></li>
                                                                </ul>
                                                            </div>
                                                        @else
                                                            <span
                                                                style="{{ $incomingReason2Style }}">{{ $d['incoming_reason2'] ?? 0 }}</span>
                                                        @endif
                                                    </td>

                                                    {{-- Incoming Reason 3 --}}
                                                    <td>
                                                        @if ($hasIncomingReason3)
                                                            <div class="dropdown">
                                                                <a style="{{ $incomingReason3Style }}"
                                                                    class="dropdown-toggle" data-bs-toggle="dropdown"
                                                                    href="#">
                                                                    {{ $d['incoming_reason3'] ?? 0 }}
                                                                </a>
                                                                <ul class="dropdown-menu">
                                                                    <li><a class="dropdown-item" target="_blank"
                                                                            href="{{ $incomingReason3LinkOperator }}">
                                                                            ऑपरेटर
                                                                            ({{ $d['incoming_reason3_operator'] ?? 0 }})
                                                                        </a></li>
                                                                    <li><a class="dropdown-item" target="_blank"
                                                                            href="{{ $incomingReason3LinkCommander }}">
                                                                            कमांडर
                                                                            ({{ $d['incoming_reason3_commander'] ?? 0 }})
                                                                        </a></li>
                                                                </ul>
                                                            </div>
                                                        @else
                                                            <span
                                                                style="{{ $incomingReason3Style }}">{{ $d['incoming_reason3'] ?? 0 }}</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                            <tr style="font-weight:bold; background-color: #e2e3e5;">
                                                <td>कुल</td>
                                                <td>{{ $totals['total'] }}</td>
                                                <td>{{ $totals['followups'] }}</td>
                                                <td>{{ $totals['completed_followups'] }}</td>
                                                <td>{{ $totals['overall_incoming'] }}</td>
                                                <td>{{ $totals['incoming_reason1'] }}</td>
                                                <td>{{ $totals['incoming_reason2'] }}</td>
                                                <td>{{ $totals['incoming_reason3'] }}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                @endif
                            @endforeach
                        @endif

                        {{-- Member Report --}}
                        {{-- @if (!empty($reportMember))
                            @php $grandTotalMembers = 0; @endphp

                            @foreach ($reportMember as $memberId => $memberData)
                                @if (!empty($memberData))
                                    <div class="step-header bg-dark text-white p-2 rounded mt-4">
                                        {{ $fields->firstWhere('member_id', $memberId)->name ?? 'कमांडर रिपोर्ट' }}:
                                        {{ $dateRangeText }}
                                    </div>

                                    @php $memberTotal = 0; @endphp

                                    <table class="table table-bordered table-sm mt-2" style="color: black">
                                        <thead style="background-color: blanchedalmond">
                                            <tr>
                                                <th>प्रकार</th>
                                                <th>कुल पंजीकृत</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($memberData as $prakar => $data)
                                                @php
                                                    $value =
                                                        isset($data['total']) && is_numeric($data['total'])
                                                            ? (int) $data['total']
                                                            : 0;
                                                    $memberTotal += $value;
                                                @endphp
                                                <tr>
                                                    <td style="font-weight:bold">{{ $prakar }}</td>
                                                    <td>{{ $value }}</td>
                                                </tr>
                                            @endforeach

                                            <tr style="font-weight:bold; background-color: #e2e3e5;">
                                                <td>कुल</td>
                                                <td>{{ $memberTotal }}</td>
                                            </tr>
                                        </tbody>
                                    </table>

                                    @php $grandTotalMembers += $memberTotal; @endphp
                                @endif
                            @endforeach
                        @endif --}}

                        @if (!empty($reportMember))
                            @php $grandTotalMembers = 0; @endphp

                            @foreach ($reportMember as $memberId => $memberData)
                                @if (!empty($memberData))
                                    <div class="step-header bg-dark text-white p-2 rounded mt-4">
                                        {{ $fields->firstWhere('member_id', $memberId)->name ?? 'कमांडर रिपोर्ट' }}:
                                        {{ $dateRangeText }}
                                    </div>

                                    @php $memberTotal = 0; @endphp

                                    <table class="table table-bordered table-sm mt-2" style="color: black">
                                        <thead style="background-color: blanchedalmond">
                                            <tr>
                                                <th>प्रकार</th>
                                                <th>कुल पंजीकृत</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($memberData as $prakar => $data)
                                                @php
                                                    $value =
                                                        isset($data['total']) && is_numeric($data['total'])
                                                            ? (int) $data['total']
                                                            : 0;
                                                    $memberTotal += $value;
                                                    $link = '';
                                                    $prakarNormalized = trim($prakar);

                                                    if (
                                                        $prakarNormalized === 'समस्या' ||
                                                        $prakarNormalized === 'विकास'
                                                    ) {
                                                        $routeName = 'commander.complaint.view';
                                                    } elseif (
                                                        $prakarNormalized === 'शुभ सुचना' ||
                                                        $prakarNormalized === 'अशुभ सुचना'
                                                    ) {
                                                        $routeName = 'commander.suchnas.view';
                                                    } else {
                                                        $routeName = null;
                                                    }

                                                    if ($routeName) {
                                                        if (!empty($memberId) && $memberId !== 'Grand Total') {
                                                            $link = route($routeName, [
                                                                'created_by_member' => $memberId,
                                                                'complaint_type' => $prakarNormalized,
                                                            ]);
                                                        } elseif ($memberId === 'Grand Total') {
                                                            $link = route($routeName, [
                                                                'complaint_type' => $prakarNormalized,
                                                            ]);
                                                        }
                                                    }
                                                @endphp


                                                <tr>
                                                    <td style="font-weight:bold">{{ $prakar }}</td>
                                                    <td>
                                                        @if ($link)
                                                            <a class="text-primary" style="cursor: pointer;"
                                                                target="_blank" href="{{ $link }}">
                                                                {{ $value }}
                                                            </a>
                                                        @else
                                                            {{ $value }}
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach


                                            <tr style="font-weight:bold; background-color: #e2e3e5;">
                                                <td>कुल</td>
                                                <td>{{ $memberTotal }}</td>
                                            </tr>
                                        </tbody>
                                    </table>

                                    @php $grandTotalMembers += $memberTotal; @endphp
                                @endif
                            @endforeach
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            function exportExcel() {
                let form = document.getElementById('complaintFilterForm');
                let url = new URL(form.action, window.location.origin);
                let params = new URLSearchParams(new FormData(form));
                params.append('export', 'excel');
                window.location.href = url.pathname + '?' + params.toString();
            }



            function enableDropdowns() {
                const val = document.querySelector('.summaryRadio:checked')?.value;
                document.getElementById('managerSelect').disabled = val !== 'manager';
                document.getElementById('operatorSelect').disabled = val !== 'operator';
                document.getElementById('commanderSelect').disabled = val !== 'member';
            }

            document.querySelectorAll('.summaryRadio').forEach(radio => {
                radio.addEventListener('change', enableDropdowns);
            });

            enableDropdowns();

            window.addEventListener('load', function() {
                if (window.location.search) {
                    const cleanUrl = window.location.origin + window.location.pathname;
                    window.history.replaceState({}, document.title, cleanUrl);
                }
            });

            function printReport() {
                const content = document.getElementById('report-results').innerHTML;
                const printWindow = window.open('', '', 'height=800,width=1200');
                printWindow.document.write(`
                        <html>
                        <head>
                            <title>Reference Report</title>
                            <style>
                                body {
                                    font-family: Arial, sans-serif;
                                    -webkit-print-color-adjust: exact !important;
                                    print-color-adjust: exact !important;
                                }
                                .step-header {
                                    background-color: #343a40 !important;
                                    color: white !important;
                                    padding-left: 4px !important; 
                                    border-radius: 6px;          
                                    margin-bottom: 10px;        
                                    margin-top: 10px;        
                                    display: flex;
                                    justify-content: space-between;
                                    align-items: center;
                                }
                                .badge {
                                    background-color: #f8f9fa !important;
                                    color: #000 !important;
                                    border: 1px solid #000;
                                    padding: 5px 10px;
                                    border-radius: 6px;
                                }
                                .complaint-type-title {
                                    background-color: #4a54e9 !important;
                                    color: white !important;
                                    font-size: 1.2rem;
                                    font-weight: bold;
                                    text-align: center;
                                    padding: 8px;
                                    border-radius: 6px;
                                    margin-bottom: 12px;
                                }
                                table {
                                    width: 100%;
                                    border-collapse: collapse;
                                }
                                table th, table td {
                                    border: 1px solid #000;
                                    padding: 6px;
                                    text-align: left;
                                }
                                table thead tr {
                                    background-color: blanchedalmond !important;
                                    font-weight: bold;
                                }
                                     table tbody tr:last-child { background-color: #e2e3e5 !important; font-weight: bold; }
                            </style>
                        </head>
                        <body>
                            ${content}
                        </body>
                        </html>
                    `);

                printWindow.document.close();
                printWindow.focus();
                printWindow.print();
                printWindow.close();
            }
        </script>
    @endpush

@endsection
