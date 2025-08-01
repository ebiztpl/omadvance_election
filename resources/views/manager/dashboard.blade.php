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
            <div class="col-xl-8 col-lg-8 col-md-8">
                <div class="row">
                    <div class="col-sm-3">
                        <div class="card" style="background-color: #c9f5b0">
                            <div class="card-body">
                                <div class="stat-text">नए मतदाता</div>
                                <div class="stat-digit new-voters"></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-3">
                        <div class="card" style="background-color: #c9f5b0">
                            <div class="card-body">
                                <div class="stat-text">नए संपर्क</div>
                                <div class="stat-digit new-contacts"></div>
                            </div>
                        </div>
                    </div>

                    <div class="col-sm-3">
                        <div class="card" style="background-color: bisque">
                            <div class="card-body">
                                <div class="stat-text">कुल मतदाता</div>
                                <div class="stat-digit total-voters"></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-3">
                        <div class="card" style="background-color: bisque">
                            <div class="card-body">
                                <div class="stat-text">कुल संपर्क</div>
                                <div class="stat-digit total-contacts"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-xl-12 col-lg-12 col-md-12">
                        <div class="card" style="background-color: #F6F7C4; height: 350px">
                            <div class="card-body">
                                <div class="card-header" style="border-bottom: 2px solid gray;">
                                    <h4 class="card-title suchna">नई सूचना</h4>
                                    <div class="d-flex justify-content-end align-items-center suchna-badges">
                                    </div>
                                </div>


                                <div class="table-responsive mt-3">
                                    <div class="overflow-auto" style="max-height: 300px; overflow-y: auto;">
                                        <table class="table custom-bordered-table">
                                            <thead class="thead-dark">
                                                <tr>
                                                    <th>नाम</th>
                                                    <th>नंo</th>
                                                    <th>क्षेत्र</th>
                                                    <th>विषय</th>
                                                    <th>कार्यक्रम समय</th>
                                                    <th>विवरण</th>
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

            <div class="col-xl-4 col-lg-4 col-md-4 w-100">
                <div class="p-3 mb-4" style="background-color: #EEEFE0">
                    <div class="section-title text-center">
                        <h4 class="font-weight-bold">समस्या / विकास </h4>
                    </div>
                    <div class="row justify-content-center">
                        <div class="col-md-6">
                            <a id="forward1" href="/complaints/forwarded?direction=to" target="_blank"
                                class="forwarded-btn d-flex justify-content-between align-items-center p-3">
                                <div class="text-left">
                                    <div class="subtitle font-weight-bold">आपको निर्देशित</div>
                                </div>
                                <span class="count-badge" id="count-you">0</span>
                            </a>
                        </div>

                        <div class="col-md-6">
                            <a id="forward1" href="/complaints/forwarded?direction=others" target="_blank"
                                class="forwarded-btn d-flex justify-content-between align-items-center p-3">
                                <div class="text-left">
                                    <div class="subtitle font-weight-bold">अन्य निर्देशित</div>
                                </div>
                                <span class="count-badge" id="count-others">0</span>
                            </a>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-xl-12 col-lg-12 col-md-12">
                        <div class="card w-100 d-flex flex-column" style="background-color: #e3e4ee">
                            <div class="card-body d-flex flex-column">
                                <div class="card-header" style="border-bottom: 2px solid gray;">
                                    <h4 class="card-title suchna mb-0">मासिक जानकारी कैलेंडर</h4>
                                </div>
                                <div id="calendar-controls" class="d-flex justify-content-between align-items-center my-3">
                                    <button class="btn btn-sm btn-outline-primary" onclick="changeMonth(-1)">←
                                        पिछला</button>
                                    <h5 id="month-year" class="m-0"></h5>
                                    <button class="btn btn-sm btn-outline-primary" onclick="changeMonth(1)">अगला →</button>
                                </div>
                                <div id="calendar" class="table-responsive flex-grow-1"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-xl-8 col-lg-8 col-md-8">
                <div class="row">
                    <div class="col-xl-12 col-lg-12 col-md-12">
                        <div class="card" style="background-color: #f5e2dc">
                            <div class="card-body">
                                <div class="card-header" style="border-bottom: 2px solid gray;">
                                    <h4 class="card-title suchna mb-0">समस्या और विकास सारणी</h4>
                                </div>
                                <div class="row mt-3">
                                    <div class="col-xl-12">
                                        <table id="dynamicTable" class="table text-center custom-bordered-table"
                                            style="color: black;">
                                            <thead>
                                                <tr>
                                                    <th rowspan="2">समय</th>
                                                    <th colspan="3">समस्या</th>
                                                    <th colspan="3">विकास</th>
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
                </div>

                <div class="row">
                    <div class="col-xl-12 col-lg-12 col-md-12">
                        <div class="card" style="background-color: #FEFAE0">
                            <div class="card-body">
                                <div class="card-header" style="border-bottom: 2px solid gray;">
                                    <h4 class="card-title suchna">आगामी कल की सूचना</h4>

                                    <div class="mt-4 mb-2 d-flex justify-content-end align-items-center suchna-badges">
                                    </div>
                                </div>


                                <div class="table-responsive mt-3">
                                    <div class="overflow-auto" style="max-height: 500px; overflow-y: auto;">
                                        <table class="table table-bordered">
                                            <thead class="thead-dark">
                                                <tr>
                                                    <th>नाम</th>
                                                    <th>नंo</th>
                                                    <th>क्षेत्र</th>
                                                    <th>विषय</th>
                                                    <th>कार्यक्रम समय</th>
                                                    <th>विवरण</th>
                                                </tr>
                                            </thead>
                                            <tbody id="tomorrow-table" style="color: black"></tbody>

                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-xl-12 col-lg-12 col-md-12">
                        <div class="card" style="background-color: #ECF2FF">
                            <div class="card-body">
                                <div class="card-header" style="border-bottom: 2px solid gray;">
                                    <h4 class="card-title suchna mb-0">आगामी सप्ताह की सूचना</h4>

                                    <div class="mt-4 mb-2 d-flex justify-content-end align-items-center suchna-badges">
                                    </div>
                                </div>



                                <div class="table-responsive mt-3">
                                    <div class="overflow-auto" style="max-height: 500px; overflow-y: auto;">
                                        <table class="table table-bordered">
                                            <thead class="thead-dark fixed">
                                                <tr>
                                                    <th>नाम</th>
                                                    <th>नंo</th>
                                                    <th>क्षेत्र</th>
                                                    <th>विषय</th>
                                                    <th>कार्यक्रम दिनांक</th>
                                                    <th>कार्यक्रम समय</th>
                                                    <th>विवरण</th>
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

            <div class="col-xl-4 col-lg-4 col-md-4">
                <div class="row">
                    <div class="col-xl-12 col-lg-12 col-md-12">
                        <div class="card" style="background-color: rgb(201, 247, 231)">

                            <div class="card-body">
                                <div class="card-header" style="border-bottom: 2px solid gray;">
                                    <h4 class="card-title suchna mb-0">विभाग समस्या</h4>
                                </div>
                                <div class="mt-3" style="max-height: 400px; overflow-y: auto;">
                                    <ul id="vibhaag-count-list"
                                        style="list-style-type: none; padding: 0; font-weight: bold"></ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-xl-12 col-lg-12 col-md-12">
                        <div class="card" style="background-color: rgb(247, 247, 198)">
                            <div class="card-body">
                                <div class="card-header" style="border-bottom: 2px solid gray;">
                                    <h4 class="card-title suchna mb-0">स्थिति</h4>
                                </div>
                                <ul class="mt-3" id="status-count-list"
                                    style="list-style-type: none; padding: 0; font-weight: bold">
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>





        <!-- सूचना विवरण Modal -->
        <div class="modal fade" id="complaintModal" tabindex="-1" aria-labelledby="complaintModalLabel"
            aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content rounded-3 shadow-lg">
                    <div class="modal-header bg-light text-white d-flex justify-content-between align-items-center">
                        <h5 class="modal-title fw-bold mb-0" id="complaintModalLabel">
                            सूचना विवरण <span id="modal-complaint-number" class="ml-2"></span>
                        </h5>
                        <div id="modal-status-button"></div>
                    </div>


                    <div class="modal-body px-4 py-3">
                        <div class="table-responsive">
                            <table class="table table-bordered mb-0">
                                <tbody style="font-size: 14px; color: black">
                                    <tr>
                                        <th class="bg-light">नाम</th>
                                        <th class="bg-light">मोबाइल</th>
                                        <th class="bg-light">क्षेत्र</th>
                                        <th class="bg-light">आवेदक</th>
                                        <th class="bg-light">मतदाता</th>
                                        <th class="bg-light">कार्यक्रम समय</th>
                                        <th class="bg-light">कार्यक्रम दिनांक</th>
                                    </tr>
                                    <tr>
                                        <td id="modal-name"></td>
                                        <td id="modal-mobile"></td>
                                        <td id="modal-area"></td>
                                        <td id="modal-applicant"></td>
                                        <td id="modal-voter"></td>
                                        <td id="modal-time"></td>
                                        <td id="modal-date"></td>
                                    </tr>
                                </tbody>
                            </table>

                            <div class="row no-gutters mt-5">
                                <div class="col-md-4 pr-md-3 mb-2">
                                    <label style="font-weight: bold">विषय शीर्षक</label>
                                    <input id="modal-title" type="text" class="form-control" readonly>
                                </div>
                                <div class="col-md-8">
                                    <label style="font-weight: bold">विषय विवरण</label>
                                    <textarea id="modal-issue" class="form-control" rows="3" readonly
                                        style="resize: none; overflow-wrap: break-word;"></textarea>
                                </div>
                            </div>

                            <div class="mt-3" id="modal-image-button-container" style="display: none;">
                                <a id="modal-image-button" class="btn btn-info" href="#" target="_blank">
                                    भेजी गई फ़ाइल
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer justify-content-end">
                        <button type="button" class="btn btn-warning" data-dismiss="modal">बंद करें</button>
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
                            const totalSamasyaOperator = Number(row.samasya.operator);
                            const totalSamasyaCommander = Number(row.samasya.commander);
                            const totalSamasyaReplies = Number(row.replies.operator) + Number(row
                                .replies.commander);

                            const totalVikashOperator = Number(row.vikash.operator);
                            const totalVikashCommander = Number(row.vikash.commander)
                            const totalVikashReplies = Number(row.repliesvikash.operator) + Number(
                                row
                                .repliesvikash.commander);

                            const makeLink = (section, type, user) =>
                                `/complaints/${section}?type=${type}&user=${user}`;



                            const tr = `
                            <tr>
                                <td><strong>${row.samay}</strong></td>

                                <td>
                                    <a href="${makeLink(row.section, 'समस्या', 'operator')}" target="_blank">  <span class="badge bg-danger text-white" data-bs-toggle="tooltip">
                                        ${totalSamasyaOperator}
                                    </span></a>
                                </td>
                                <td>
                                     <a href="${makeLink(row.section, 'समस्या', 'commander')}" target="_blank"><span class="badge bg-danger text-white" data-bs-toggle="tooltip">
                                        ${totalSamasyaCommander}
                                    </span>
                                    </a>
                                </td>
                                <td>
                                    <span class="badge bg-warning" data-bs-toggle="tooltip">
                                        ${totalSamasyaReplies}
                                    </span>
                                </td>

                                <td>
                                    <a href="${makeLink(row.section, 'विकास', 'operator')}" target="_blank"><span class="badge bg-success text-white" data-bs-toggle="tooltip">
                                        ${totalVikashOperator}
                                    </span>
                                    </a>
                                </td>
                                <td>
                                     <a href="${makeLink(row.section, 'विकास', 'commander')}" target="_blank"><span class="badge bg-success text-white" data-bs-toggle="tooltip">
                                        ${totalVikashCommander}
                                    </span>
                                    </a>
                                </td>
                                <td>
                                    <span class="badge bg-warning" data-bs-toggle="tooltip">
                                        ${totalVikashReplies}
                                    </span>
                                </td>
                            </tr>
                        `;

                            $tbody.append(tr);
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
                        renderSuchnaTable(response.tomorrow, "#tomorrow-table");
                        renderSuchnaTable(response.week, "#week-table");
                    },
                    error: function(xhr, status, error) {
                        console.error("Error:", error);
                    }
                });

                function renderSuchnaTable(data, selector) {
                    const $tbody = $(selector);
                    const $card = $tbody.closest(".card");
                    const columnCount = $tbody.closest("table").find("thead th").length;

                    $tbody.empty();

                    // Count badges
                    let shubhCount = 0;
                    let ashubhCount = 0;

                    data.forEach(row => {
                        if (row.complaint_type === "शुभ सुचना") shubhCount++;
                        else if (row.complaint_type === "अशुभ सुचना") ashubhCount++;
                    });

                    const badgeHTML = `
                            <h4><span class="badge bg-success text-white mr-3">शुभ सूचना: ${shubhCount}</span></h4>
                            <h4><span class="badge bg-danger  text-white">अशुभ सूचना: ${ashubhCount}</span></h4>
                        `;

                    $card.find(".suchna-badges").html(badgeHTML);

                    if (data.length === 0) {
                        $tbody.append(`
                                <tr>
                                    <td colspan="${columnCount}" class="text-center text-muted">कोई सूचना नहीं है</td>
                                </tr>
                            `);
                        return;
                    }

                    data.forEach(row => {
                        const rowColor = row.complaint_type === "शुभ सुचना" ? "table-success" :
                            "table-danger";

                        const rowHTML = `
                                <tr class="${rowColor}">
                                    <td>${row.name}</td>
                                    <td>${row.mobile_number}</td>
                                    <td>${row.area_name}</td>
                                    <td>${row.issue_description}</td>
                                    ${selector === "#week-table" ? `<td>${row.program_date}</td>` : ''}
                                     <td>${row.news_time}</td>
                                    <td>
                                       <button class="btn btn-sm btn-primary view-details-btn" data-id="${row.complaint_id}">
                                            विवरण
                                        </button>
                                    </td>
                                </tr>
                            `;
                        $tbody.append(rowHTML);
                    });
                }

                $(document).on('click', '.view-details-btn', function() {
                    const id = $(this).data('id');
                    openComplaintModal(id);
                });

                function openComplaintModal(id) {
                    if (!id) {
                        alert("Invalid ID");
                        return;
                    }

                    $.ajax({
                        url: '/detail_suchna/' + id,
                        method: 'GET',
                        success: function(data) {
                            $('#modal-name').text(data.name || '—');
                            $('#modal-mobile').text(data.mobile_number || '—');
                            $('#modal-area').text(data.area?.area_name || '—');
                            $('#modal-issue').text(data.issue_description || '—');
                            $('#modal-title').val(data.issue_title || '—');
                            $('#modal-applicant').text(data.aavedak || '—');
                            $('#modal-complaint-number').text('#' + (data.complaint_number || '—'));
                            $('#modal-voter').text(data.voter_id || '—');
                            $('#modal-time').text(data.news_time || '—');
                            $('#modal-date').text(data.program_date || '—');
                            $('#modal-status-button').html(data.status_text || '');

                            if (data.issue_attachment) {
                                let fullUrl = '/assets/upload/complaints/' + data.issue_attachment;

                                let imageButtonHtml = `
                                    <a href="${fullUrl}" class="btn btn-sm btn-info" target="_blank">
                                        अटैचमेंट खोलें
                                    </a>
                                `;

                                $('#modal-image-button-container').html(imageButtonHtml).show();
                            } else {
                                $('#modal-image-button-container').html(`
                                    <button class="btn btn-sm btn-secondary" disabled>
                                        कोई अटैचमेंट नहीं है
                                    </button>
                                `).show();
                            }

                            const modal = new bootstrap.Modal(document.getElementById('complaintModal'));
                            modal.show();
                        },
                        error: function() {
                            alert("डाटा लोड करने में त्रुटि हुई।");
                        }
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
                                  <a href="/complaints/vibhag-details?department=${item.department}" target="_blank" class="text-decoration-none" style="color: black">
                            <li class="d-flex justify-content-between border-bottom py-1">
                                <span>${item.department}</span>
                                <span class="badge bg-secondary text-white">${item.total}</span>
                            </li>
                            </a>
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
                                 <a href="/complaints/status-details?status=${item.status}" target="_blank" class="text-decoration-none" style="color: black">
                            <li class="d-flex justify-content-between border-bottom py-1">
                                <span>${item.status}</span>
                                <span class="badge bg-success text-white">${item.total}</span>
                            </li>
                                    </a>
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
                        $('.new-voters').html(
                            `<a href="/voters/details?filter=today-voters" target="_blank" class="text-decoration-none" style="color: black">
                                <i class="fa fa-users"></i> ${data.new_voters}
                            </a>`
                        );

                        $('.new-contacts').html(
                            `<a href="/voters/details?filter=today-contacts" target="_blank" class="text-decoration-none" style="color: black">
                                <i class="fa fa-phone"></i> ${data.new_contacts}
                            </a>`
                        );

                        $('.total-voters').html(
                            `<a href="/voters/details?filter=total-voters" target="_blank" class="text-decoration-none" style="color: black">
                                <i class="fa fa-check-square"></i> ${data.total_voters}
                            </a>`
                        );

                        $('.total-contacts').html(
                            `<a href="/voters/details?filter=total-contacts" target="_blank" class="text-decoration-none" style="color: black">
                                <i class="fa fa-address-book"></i> ${data.total_contacts}
                            </a>`
                        );
                    }
                });


                fetchForwardedCounts();

                function fetchForwardedCounts() {
                    $.ajax({
                        url: "{{ route('ajax.forwarded.counts') }}",
                        method: 'GET',
                        dataType: 'json',
                        success: function(data) {
                            $('#count-you').text(data.forwarded_to_you);
                            $('#count-others').text(data.forwarded_to_others);
                        },
                        error: function(xhr, status, error) {
                            console.error('Error fetching forwarded counts:', error);
                        }
                    });
                }
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

                let table = '<table class="table table-bordered text-center calendar-table" style="color: black"><thead><tr>';
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
                            const total = info ? (info.shubh + info.asubh) : 0;

                            table += `<td class="p-1">
                                <a href="/complaints/date-wise?date=${fullDate}" target="_blank" class="text-decoration-none text-dark">
                                    <div><strong>${date}</strong></div>`;

                            if (total > 0) {
                                const tooltip = `
                                    शुभ सूचना: ${info.shubh}
                                    अशुभ सूचना: ${info.asubh}
                                `.trim().replace(/\n/g, ' ').replace(/\s+/g, ' ');

                                table += `
                                    <div class="calendar-dot mx-auto mt-1" data-bs-toggle="tooltip" title="${tooltip}">
                                        <span class="dot-text">${total}</span>
                                    </div>
                                `;
                            }

                            table += `</a></td>`;
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

            $(document).ajaxStart(function() {
                $("#loader-wrapper").show();
            });

            $(document).ajaxStop(function() {
                $("#loader-wrapper").hide();
            });
        </script>
    @endpush
@endsection
