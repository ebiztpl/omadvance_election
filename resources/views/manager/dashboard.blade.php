@php
    $pageTitle = 'मैनेजर डैशबोर्ड';
    $breadcrumbs = [
        'मैनेजर' => '#',
        'मैनेजर डैशबोर्ड' => '#',
    ];
@endphp

@extends('layouts.app')
@section('title', 'Manager Dashboard')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-xl-6 col-lg-6 col-md-6">
                <div class="row">
                    <div class="col-sm-3">
                        <div class="card">
                            <div class="card-body">
                                <div class="stat-text">नए मतदाता</div>
                                <div class="stat-digit new-voters"></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-3">
                        <div class="card">
                            <div class="card-body">
                                <div class="stat-text">नए संपर्क</div>
                                <div class="stat-digit new-contacts"></div>
                            </div>
                        </div>
                    </div>

                    <div class="col-sm-3">
                        <div class="card">
                            <div class="card-body">
                                <div class="stat-text">कुल मतदाता</div>
                                <div class="stat-digit total-voters"></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-3">
                        <div class="card">
                            <div class="card-body">
                                <div class="stat-text">कुल संपर्क</div>
                                <div class="stat-digit total-contacts"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-xl-12 col-lg-12 col-md-12">
                        <div class="card">
                            <div class="card-body">
                                <h4 class="card-title text-center">नई सूचना</h4>
                                <div class="mb-2 float-right">
                                    <span class="badge badge-success">शुभ सूचना</span>
                                    <span class="badge badge-danger">अशुभ सूचना</span>
                                </div>

                                <div class="table-responsive">
                                    <div class="overflow-auto" style="max-height: 300px; overflow-y: auto;">
                                        <table class="table table-bordered">
                                            <thead class="thead-dark">
                                                <tr>
                                                    <th>नाम</th>
                                                    <th>नंo</th>
                                                    <th>क्षेत्र</th>
                                                    <th>विषय</th>
                                                </tr>
                                            </thead>
                                            <tbody id="today-table" style="color: black"></tbody>


                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-6 col-lg-6 col-md-6 d-flex">
                <div class="card w-100 d-flex flex-column">
                    <div class="card-body d-flex flex-column">
                        <div class="text-center">
                            <h4 class="card-title">मासिक जानकारी कैलेंडर</h4>
                        </div>
                        <div id="calendar-controls" class="d-flex justify-content-between align-items-center my-3">
                            <button class="btn btn-sm btn-outline-primary" onclick="changeMonth(-1)">← पिछला</button>
                            <h5 id="month-year" class="m-0"></h5>
                            <button class="btn btn-sm btn-outline-primary" onclick="changeMonth(1)">अगला →</button>
                        </div>
                        <div id="calendar" class="table-responsive flex-grow-1"></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-xl-6 col-lg-6 col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">समस्या/विकास और शुभ/अशुभ सूचना सारणी</h4>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-xl-12">
                                <table style="color: black" id="dynamicTable" class="table table-bordered text-center">
                                    <thead>
                                        <tr>
                                            <th rowspan="2">समय</th>
                                            <th colspan="3">समस्या / विकास</th>
                                            <th colspan="3">शुभ / अशुभ सूचना</th>
                                        </tr>
                                        <tr>
                                            <th>कार्यालय</th>
                                            <th>कमांडर</th>
                                            <th>समाधान योग</th>
                                            <th>कार्यालय</th>
                                            <th>कमांडर</th>
                                            <th>समाधान योग</th>
                                        </tr>
                                    </thead>
                                    <tbody>

                                    </tbody>
                                </table>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-lg-3 col-md-3">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">विभाग समस्या</h4>
                    </div>
                    <div class="card-body">
                        <div style="max-height: 400px; overflow-y: auto;">
                            <ul id="vibhaag-count-list" style="list-style-type: none; padding: 0;"></ul>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-lg-3 col-md-3">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">स्थिति</h4>
                    </div>
                    <div class="card-body">
                        <ul id="status-count-list" style="list-style-type: none; padding: 0;"></ul>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-6 col-md-6 col-sm-6">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title text-center">कल की सूचना</h4>
                        <div class="mb-2 float-right">
                            <span class="badge badge-success">शुभ सूचना</span>
                            <span class="badge badge-danger">अशुभ सूचना</span>
                        </div>
                        <div class="table-responsive">
                            <div class="overflow-auto" style="max-height: 500px; overflow-y: auto;">
                                <table class="table table-bordered">
                                    <thead class="thead-dark">
                                        <tr>
                                            <th>नाम</th>
                                            <th>नंo</th>
                                            <th>क्षेत्र</th>
                                            <th>विषय</th>
                                        </tr>
                                    </thead>
                                    <tbody id="yesterday-table" style="color: black"></tbody>

                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-6 col-md-6 col-sm-6">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title text-center">सप्ताह की सूचना</h4>

                        <div class="mb-2 float-right">
                            <span class="badge badge-success">शुभ सूचना</span>
                            <span class="badge badge-danger">अशुभ सूचना</span>
                        </div>

                        <div class="table-responsive">
                            <div class="overflow-auto" style="max-height: 500px; overflow-y: auto;">
                                <table class="table table-bordered">
                                    <thead class="thead-dark">
                                        <tr>
                                            <th>नाम</th>
                                            <th>नंo</th>
                                            <th>क्षेत्र</th>
                                            <th>विषय</th>
                                        </tr>
                                    </thead>
                                    <tbody id="week-table" style="color: black"></tbody>

                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


    @push('scripts')
        <script>
            $(document).ready(function() {
                $.ajax({
                    url: "/complaint-summary",
                    method: "GET",
                    dataType: "json",
                    success: function(data) {
                        const $tbody = $("#dynamicTable tbody");
                        $tbody.empty();
                        data.forEach(row => {
                            const totalSamasyaOperator = Number(row.samasya.operator) + Number(row
                                .vikash.operator);
                            const totalSamasyaCommander = Number(row.samasya.commander) + Number(row
                                .vikash.commander);
                            const totalSamasyaReplies = Number(row.replies.operator) + Number(row
                                .replies.commander);

                            const totalSuchnaOperator = Number(row.shubh.operator) + Number(row
                                .asubh.operator);
                            const totalSuchnaCommander = Number(row.shubh.commander) + Number(row
                                .asubh.commander);
                            const totalSuchnaReplies = totalSuchnaOperator + totalSuchnaCommander;
                            const tr = `
                            <tr>
                                <td><strong>${row.samay}</strong></td>

                                <td>
                                    <span class="badge bg-danger" data-bs-toggle="tooltip" 
                                        title="कार्यालय → समस्या: ${row.samasya.operator}, विकास: ${row.vikash.operator}">
                                        ${totalSamasyaOperator}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-danger" data-bs-toggle="tooltip" 
                                        title="कमांडर → समस्या: ${row.samasya.commander}, विकास: ${row.vikash.commander}">
                                        ${totalSamasyaCommander}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-warning" data-bs-toggle="tooltip" 
                                        title="समाधान → कार्यालय: ${row.replies.operator}, कमांडर: ${row.replies.commander}">
                                        ${totalSamasyaReplies}
                                    </span>
                                </td>

                                <td>
                                    <span class="badge bg-success" data-bs-toggle="tooltip" 
                                        title="कार्यालय → शुभ: ${row.shubh.operator}, अशुभ: ${row.asubh.operator}">
                                        ${totalSuchnaOperator}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-success" data-bs-toggle="tooltip" 
                                        title="कमांडर → शुभ: ${row.shubh.commander}, अशुभ: ${row.asubh.commander}">
                                        ${totalSuchnaCommander}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-warning" data-bs-toggle="tooltip" 
                                        title="समाधान: कार्यालय ${totalSuchnaOperator} + कमांडर ${totalSuchnaCommander}">
                                        ${totalSuchnaReplies}
                                    </span>
                                </td>
                            </tr>
                        `;

                            $tbody.append(tr);
                        });

                        const tooltipTriggerList = [].slice.call(document.querySelectorAll(
                            '[data-bs-toggle="tooltip"]'));
                        tooltipTriggerList.map(function(tooltipTriggerEl) {
                            return new bootstrap.Tooltip(tooltipTriggerEl);
                        });
                    },
                    error: function(xhr, status, error) {
                        console.error("Error loading data:", error);
                    }
                });

                $.ajax({
                    url: "/fetch-suchna",
                    method: "GET",
                    dataType: "json",
                    success: function(response) {
                        renderSuchnaTable(response.today, "#today-table");
                        renderSuchnaTable(response.yesterday, "#yesterday-table");
                        renderSuchnaTable(response.week, "#week-table");
                    },
                    error: function(xhr, status, error) {
                        console.error("Error:", error);
                    }
                });

                function renderSuchnaTable(data, selector) {
                    const $tbody = $(selector);
                    $tbody.empty();

                    if (data.length === 0) {
                        $tbody.append(`
                            <tr>
                                <td colspan="4" class="text-center text-muted">कोई सूचना नहीं है</td>
                            </tr>
                                 `);
                        return;
                    }

                    data.forEach(row => {
                        const rowColor = row.complaint_type === "शुभ सुचना" ? "table-success" : "table-danger";
                        $tbody.append(`
                            <tr class="${rowColor}">
                                <td>${row.name}</td>
                                <td>${row.mobile_number}</td>
                                <td>${row.area_name}</td>
                                <td>${row.issue_description}</td>
                            </tr>
                        `);
                    });
                }

                $.ajax({
                    url: "/fetch-vibhaag-count",
                    method: "GET",
                    dataType: "json",
                    success: function(data) {
                        const $list = $("#vibhaag-count-list");
                        $list.empty();

                        if (data.length === 0) {
                            $list.append(`<li class="text-muted text-center">कोई डेटा उपलब्ध नहीं</li>`);
                        } else {
                            data.forEach(item => {
                                $list.append(`
                            <li class="d-flex justify-content-between border-bottom py-1">
                                <span>${item.department}</span>
                                <span class="badge bg-secondary">${item.total}</span>
                            </li>
                        `);
                            });
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error("Error fetching vibhaag:", error);
                        $("#vibhaag-count-list").html(
                            `<li class="text-danger">डेटा लोड करने में त्रुटि</li>`);
                    }
                });

                $.ajax({
                    url: "/fetch-status",
                    method: "GET",
                    dataType: "json",
                    success: function(data) {
                        const $list = $("#status-count-list");
                        $list.empty();

                        if (data.length === 0) {
                            $list.append(`<li class="text-muted text-center">कोई डेटा उपलब्ध नहीं</li>`);
                        } else {
                            data.forEach(item => {
                                $list.append(`
                            <li class="d-flex justify-content-between border-bottom py-1">
                                <span>${item.status}</span>
                                <span class="badge bg-success">${item.total}</span>
                            </li>
                        `);
                            });
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error("Error fetching Status:", error);
                        $("#status-count-list").html(
                            `<li class="text-danger">डेटा लोड करने में त्रुटि</li>`);
                    }
                });

                $.ajax({
                    url: '/dashboard/stats',
                    method: 'GET',
                    success: function(data) {
                        $('.new-voters').html('<i class="fa fa-users"></i> ' + data.new_voters);
                        $('.new-contacts').html('<i class="fa fa-phone"></i> ' + data.new_contacts);
                        $('.total-voters').html('<i class="fa fa-check-square"></i> ' + data.total_voters);
                        $('.total-contacts').html('<i class="fa fa-address-book"></i> ' + data
                            .total_contacts);
                    }
                });
            });


            let today = new Date();
            let currentMonth = today.getMonth();
            let currentYear = today.getFullYear();

            const monthNames = ["जनवरी", "फ़रवरी", "मार्च", "अप्रैल", "मई", "जून", "जुलाई", "अगस्त", "सितंबर", "अक्टूबर",
                "नवंबर", "दिसंबर"
            ];

            function changeMonth(offset) {
                currentMonth += offset;
                if (currentMonth > 11) {
                    currentMonth = 0;
                    currentYear++;
                } else if (currentMonth < 0) {
                    currentMonth = 11;
                    currentYear--;
                }
                loadCalendar(currentMonth, currentYear);
            }

            function loadCalendar(month, year) {
                $("#month-year").text(`${monthNames[month]} ${year}`);

                $.ajax({
                    url: `/calendar-data`,
                    method: "GET",
                    data: {
                        month: month + 1,
                        year: year
                    },
                    dataType: "json",
                    success: function(data) {
                        renderCalendar(month, year, data);
                    },
                    error: function(xhr, status, error) {
                        console.error("Error fetching calendar data:", error);
                        $("#calendar").html(`<div class="text-danger">कैलेंडर डेटा लोड नहीं हुआ।</div>`);
                    }
                });
            }

            function renderCalendar(month, year, calendarData) {
                const calendar = $("#calendar");
                const firstDay = new Date(year, month, 1).getDay();
                const daysInMonth = new Date(year, month + 1, 0).getDate();

                let table = '<table class="table table-bordered text-center calendar-table" style="color: gray"><thead><tr>';
                const days = ["रवि", "सोम", "मंगल", "बुध", "गुरु", "शुक्र", "शनि"];
                days.forEach(day => table += `<th>${day}</th>`);
                table += '</tr></thead><tbody><tr>';

                let date = 1;
                for (let i = 0; i < 6; i++) {
                    for (let j = 0; j < 7; j++) {
                        if (i === 0 && j < firstDay) {
                            table += '<td></td>';
                        } else if (date > daysInMonth) {
                            break;
                        } else {
                            const fullDate = `${year}-${String(month + 1).padStart(2, '0')}-${String(date).padStart(2, '0')}`;
                            const info = calendarData[fullDate];
                            const total = info ? (info.samasya + info.vikash + info.shubh + info.asubh) : 0;

                            table += `<td class="p-1"><div><strong>${date}</strong></div>`;

                            if (total > 0) {
                                const tooltip = `
                        समस्या: ${info.samasya}
                        विकास: ${info.vikash}
                        शुभ सूचना: ${info.shubh}
                        अशुभ सूचना: ${info.asubh}
                    `.trim().replace(/\n/g, ' ').replace(/\s+/g, ' ');

                                table += `
                        <div class="calendar-dot mx-auto mt-1" data-bs-toggle="tooltip" title="${tooltip}">
                            <span class="dot-text">${total}</span>
                        </div>
                    `;
                            }

                            table += `</td>`;
                            date++;
                        }
                    }

                    table += '</tr>';
                    if (date > daysInMonth) break;
                    table += '<tr>';
                }

                table += '</tbody></table>';
                calendar.html(table);

                $('[data-bs-toggle="tooltip"]').tooltip();
            }

            $(document).ready(function() {
                loadCalendar(currentMonth, currentYear);
            });
        </script>
    @endpush
@endsection
