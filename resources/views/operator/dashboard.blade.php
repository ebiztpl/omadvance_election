@php
    $pageTitle = 'कार्यालय डैशबोर्ड';
    $breadcrumbs = [
        'कार्यालय' => '#',
        'कार्यालय डैशबोर्ड' => '#',
    ];
@endphp

@extends('layouts.app')
@section('title', 'Operator Dashboard')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-xl-4 col-lg-4 col-md-4">
                <div class="card" style="background-color: rgb(247, 247, 198)">
                    <div class="card-body">
                        <div class="card-header" style="border-bottom: 2px solid gray;">
                            <h4 class="card-title suchna mb-0">आज के फॉलोअप</h4>
                        </div>
                        <ul class="mt-3" id="followup-count-list"
                            style="list-style-type: none; padding: 0; font-weight: bold">
                        </ul>
                    </div>
                </div>
            </div>

            <div class="col-xl-4 col-lg-4 col-md-4">
                <div class="card" style="background-color: #f5e2dc">
                    <div class="card-body">
                        <div class="card-header" style="border-bottom: 2px solid gray;">
                            <h4 class="card-title suchna mb-0">समस्या और सूचना सारणी</h4>
                        </div>
                        <div class="row mt-3">
                            <div class="col-6">
                                <input type="date" id="fromDate" class="form-control" placeholder="From Date">
                            </div>
                            <div class="col-6 mb-3">
                                <input type="date" id="toDate" class="form-control" placeholder="To Date">
                            </div>
                            <div class="col-xl-12">
                                <div class="table-responsive">
                                    <table id="dynamicTable" class="table text-center custom-bordered-table"
                                        style="color: black;">
                                        <thead style="font-weight: bold">
                                            <tr>
                                                <th>समय</th>
                                                <th>समस्या</th>
                                                <th>सूचना</th>
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

            <div class="col-xl-4 col-lg-4 col-md-4">
                <div class="card" style="background-color: #ECF2FF">
                    <div class="card-body">
                        <div class="card-header" style="border-bottom: 2px solid gray;">
                            <h4 class="card-title suchna mb-0">फॉलोअप स्थिति</h4>
                        </div>

                        <div class="row mt-3">
                            <div class="col-6">
                                <input type="date" id="followupfromDate" class="form-control" placeholder="From Date">
                            </div>
                            <div class="col-6 mb-3">
                                <input type="date" id="followuptoDate" class="form-control" placeholder="To Date">
                            </div>
                            <div class="col-xl-12">
                                <div class="table-responsive">
                                    <table id="dynamicfollowupTable" class="table text-center custom-bordered-table"
                                        style="color: black;">
                                        <thead style="font-weight: bold">
                                            <tr>
                                                <th>समय</th>
                                                <th>पूर्ण</th>
                                                <th>अपूर्ण</th>
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
        </div>
    </div>


    @push('scripts')
        <script>
            $(document).ready(function() {
                function loadComplaintSummary(from = '', to = '') {
                    $.ajax({
                        url: "/operator/complaint-summary",
                        method: "GET",
                        dataType: "json",
                        data: {
                            from: from,
                            to: to
                        },
                        success: function(data) {
                            const $tbody = $("#dynamicTable tbody");
                            $tbody.empty();

                            data.forEach(row => {
                                const totalSamasya = Number(row.samasya);
                                const totalSuchna = Number(row.suchna);

                                const samasyaTooltip =
                                    `समस्या: ${row.samasya_details.samasya}, विकास: ${row.samasya_details.vikash}`;
                                const suchnaTooltip =
                                    `शुभ: ${row.suchna_details.subh}, अशुभ: ${row.suchna_details.asubh}`;

                                const tr = `
                                    <tr>
                                        <td><strong>${row.samay}</strong></td>
                                        <td>
                                            <span class="badge bg-danger text-white" 
                                                data-bs-toggle="tooltip" 
                                                title="${samasyaTooltip}">
                                                ${totalSamasya}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge bg-success text-white" 
                                                data-bs-toggle="tooltip" 
                                                title="${suchnaTooltip}">
                                                ${totalSuchna}
                                            </span>
                                        </td>
                                    </tr>
                                `;
                                $tbody.append(tr);
                            });

                            $('[data-bs-toggle="tooltip"]').tooltip();
                        },
                        error: function(xhr, status, error) {
                            console.error("Error loading data:", error);
                        }
                    });
                }

                loadComplaintSummary();

                $('#fromDate, #toDate').on('change', function() {
                    const from = $('#fromDate').val();
                    const to = $('#toDate').val();
                    if (from && to) {
                        loadComplaintSummary(from, to);
                    }
                });

                $.ajax({
                    url: "/operator/todays-followups",
                    method: "GET",
                    dataType: "json",
                    success: function(data) {
                        const $list = $("#followup-count-list");
                        $list.empty();

                        $list.append(
                            `<li class="d-flex justify-content-between border-bottom py-1">नये फॉलोअप
                                <span class="badge bg-info">${data.new_followups}</span></li>`
                        );
                        $list.append(
                            `<li class="d-flex justify-content-between border-bottom py-1">नये पूर्ण 
                                <span class="badge bg-success text-light">${data.new_completed}</span></li>`
                        );
                        $list.append(
                            `<li class="d-flex justify-content-between border-bottom py-1">नये पेंडिंग 
                                <span class="badge bg-danger text-light">${data.new_pending}</span></li>`
                        );

                        // Separator between new and old
                        $list.append(
                            `<li><hr class="my-2" style="border-top: 5px solid #ccc;"></li>`
                        );


                        $list.append(
                            `<li class="d-flex justify-content-between border-bottom py-1">पुराने फॉलोअप 
                                <span class="badge bg-primary text-light">${data.old_followups}</span></li>`
                        );
                        $list.append(
                            `<li class="d-flex justify-content-between border-bottom py-1">पुराने पूर्ण 
                                <span class="badge bg-success text-light">${data.old_completed}</span></li>`
                        );
                        $list.append(
                            `<li class="d-flex justify-content-between border-bottom py-1">पुराने पेंडिंग 
                                <span class="badge bg-warning text-light">${data.old_pending}</span></li>`
                        );
                    },
                    error: function(xhr, status, error) {
                        console.error("Error loading followup data:", error);
                    }
                });


                function loadFollowupSummary(from = '', to = '') {
                    $.ajax({
                        url: "/operator/followup-summary",
                        method: "GET",
                        dataType: "json",
                        data: {
                            from: from,
                            to: to
                        },
                        success: function(data) {
                            const $tbody = $("#dynamicfollowupTable tbody");
                            $tbody.empty();

                            data.forEach(row => {
                                const tr = `
                                    <tr>
                                        <td><strong>${row.samay}</strong></td>
                                        <td><span class="badge bg-info ">${row.completed}</span></td>
                                        <td><span class="badge bg-danger text-light">${row.pending}</span></td>
                                    </tr>
                                `;
                                $tbody.append(tr);
                            });
                        },
                        error: function(xhr, status, error) {
                            console.error("Error loading data:", error);
                        }
                    });
                }

                loadFollowupSummary();

                $('#followupfromDate, #followuptoDate').on('change', function() {
                    const from = $('#followupfromDate').val();
                    const to = $('#followuptoDate').val();
                    if (from && to) {
                        loadFollowupSummary(from, to);
                    }
                });
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
