@php
    $pageTitle = 'अगली फ़ॉलोअप';
    $breadcrumbs = [
        'कार्यालय' => '#',
        'अगली फ़ॉलोअप' => '#',
    ];

    $counts = [
        'not_done' => 0,
        'update_followup' => 0,
        'done_not_completed' => 0,
        'completed' => 0,
    ];
@endphp

@extends('layouts.app')
@section('title', 'Next Followup')

@section('content')
    <div class="container">
        <div class="row page-titles mx-0">
            <div class="col-12">
                <form method="GET" id="followupfilterform">
                    <div class="row mt-3">
                        <div class="col-md-2">
                            <label>तिथि से</label>
                            <input type="date" name="from_date" id="from_date" class="form-control">
                        </div>

                        <div class="col-md-2">
                            <label>तिथि तक</label>
                            <input type="date" name="to_date" id="to_date" class="form-control">
                        </div>

                        @php
                            $userName = session('logged_in_user') ?? 'आप';
                        @endphp

                        <div class="col-md-2">
                            <label>आपके फ़ॉलोअप स्थिति:</label>
                            <select name="operator_followup_status" id="operator_followup_status" class="form-control">
                                <option value="">-- सभी --</option>
                                <option value="completed_by_me">पूर्ण ({{ $userName }})</option>
                                <option value="pending_by_me">अपूर्ण ({{ $userName }})</option>
                                <option value="upcoming_by_me">प्रक्रिया फ़ॉलोअप ({{ $userName }})</option>
                                {{-- <option value="not_done_by_me">फ़ॉलोअप नहीं हुआ ({{ $userName }})</option> --}}
                            </select>
                        </div>
                    </div>

                    <div class="row mt-2">

                        <div class="col-md-8">
                            <label>फ़ॉलोअप स्थिति:</label><br>
                            <div class="d-flex flex-wrap">
                                {{-- <div class="form-check custom-radio-box mr-2 mb-2">
                                    <input class="form-check-input" type="radio" name="followup_status_filter"
                                        id="no_followup_default" value="no_followup_latest">
                                    <label class="form-check-label" for="no_followup_latest">फ़ॉलोअप नहीं किया</label>
                                </div> --}}

                                <div class="form-check custom-radio-box mr-2 mb-2">
                                    <input class="form-check-input" type="radio" name="followup_status_filter"
                                        id="upcoming" value="upcoming">
                                    <label class="form-check-label" for="upcoming">प्रक्रिया फ़ॉलोअप</label>
                                </div>

                                <div class="form-check custom-radio-box mr-2 mb-2">
                                    <input class="form-check-input" type="radio" name="followup_status_filter"
                                        id="completed" value="completed">
                                    <label class="form-check-label" for="completed">पूर्ण फ़ॉलोअप</label>
                                </div>

                                <div class="form-check custom-radio-box mr-2 mb-2">
                                    <input class="form-check-input" type="radio" name="followup_status_filter"
                                        id="not_done" value="not_done">
                                    <label class="form-check-label" for="not_done">फॉलोअप किया गया है, लेकिन कार्य अपूर्ण
                                        है</label>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-2 mt-2">
                            <br>
                            <button type="button" class="btn btn-secondary" id="resetFollowup">फ़ॉलोअप नहीं किया</button>
                        </div>

                        <div class="col-md-2 mt-2">
                            <br>
                            <button type="submit" class="btn btn-primary" id="applyFilters">फ़िल्टर लागू करें</button>
                        </div>
                    </div>

                </form>
            </div>
        </div>

        <div id="success-alert" class="alert alert-success alert-dismissible fade show d-none" role="alert">
            <span id="success-message"></span>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        @if (session('success'))
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                {{ session('success') }}
                                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                        @endif

                        <span
                            style="margin-bottom: 8px; font-size: 18px; color: green; text-align: right; margin-left: 50px; float: right">कुल
                            शिकायत - <span id="complaint-count">{{ $complaints->count() }}</span></span>

                        <div class="table-responsive">
                            <table id="example" style="min-width: 845px" class="display table-bordered">
                                <thead>
                                    <tr>
                                        <th>क्र.</th>
                                        <th>शिकायत विवरण</th>
                                        <th style="width: 150px">नवीनतम जवाब विवरण</th>
                                        <th>फ़ॉलोअप</th>
                                        <th>फ़ॉलोअप</th>
                                        <th>विस्तार से</th>
                                    </tr>
                                </thead>
                                <tbody id="complaintsTableBody">
                                    @foreach ($complaints as $index => $complaint)
                                        @php
                                            $latestFollowup = optional($complaint->latestNonDefaultReply)
                                                ->latestFollowup; // includes completed
                                            $latestFollowupNonCompleted = optional($complaint->latestNonDefaultReply)
                                                ->latestFollowupNotCompleted;

                                            $today = now()->toDateString();

                                            if (optional($latestFollowup)->followup_status == 2) {
                                                $rowStatus = 'completed';
                                            } elseif (
                                                optional($latestFollowupNonCompleted)->followup_status == 1 &&
                                                optional($latestFollowupNonCompleted)->followup_date != $today
                                            ) {
                                                $rowStatus = 'update_followup';
                                            } elseif (
                                                optional($latestFollowupNonCompleted)->followup_status == 1 &&
                                                optional($latestFollowupNonCompleted)->followup_date == $today
                                            ) {
                                                $rowStatus = 'done_not_completed';
                                            } else {
                                                $rowStatus = 'not_done';
                                            }

                                            $counts[$rowStatus]++; // count each category
                                        @endphp

                                        <tr data-followup-status="{{ $rowStatus }}">
                                            <td>{{ $index + 1 }}</td>
                                            <td> <strong>शिकायत क्र.: </strong>{{ $complaint->complaint_number ?? 'N/A' }}
                                                <br>
                                                <strong>शिकायत प्रकार: </strong>{{ $complaint->complaint_type ?? '' }}
                                                <br>
                                                <strong>नाम: </strong>{{ $complaint->name ?? 'N/A' }} <br>
                                                <strong>मोबाइल: </strong>{{ $complaint->mobile_number ?? '' }} <br>
                                                <strong>पुत्र श्री: </strong>{{ $complaint->father_name ?? '' }} <br>
                                                <strong>आवेदक: </strong>
                                                @if ($complaint->type == 2)
                                                    {{ $complaint->admin->admin_name ?? '-' }}
                                                @else
                                                    {{ $complaint->registrationDetails->name ?? '-' }}
                                                @endif
                                                <br>
                                                <strong>विभाग: </strong> {{ $complaint->complaint_department ?? 'N/A' }}
                                                <br><br>
                                                <strong>शिकायत तिथि: </strong>
                                                {{ \Carbon\Carbon::parse($complaint->posted_date)->format('d-m-Y h:i A') }}
                                                <br><br>
                                                <strong>स्थिति: </strong>{!! $complaint->statusTextPlain() !!} <br>
                                            </td>


                                            <td>
                                                <strong>भेजने वाला:
                                                </strong>{{ $complaint->latestReplyWithoutFollowup->replyfrom->admin_name ?? 'N/A' }}
                                                <br>
                                                <strong>फॉरवर्ड:
                                                </strong>{{ $complaint->latestReplyWithoutFollowup->forwardedToManager->admin_name ?? 'N/A' }}<br>
                                                <strong>जवाब:
                                                </strong>{{ $complaint->latestReplyWithoutFollowup->complaint_reply ?? '' }}<br><br>
                                                <strong>तिथि:
                                                </strong>{{ $complaint->latestReplyWithoutFollowup->reply_date ?? '' }}
                                                <br>
                                            </td>

                                            <td>
                                                @php
                                                    $latestFollowup = optional($complaint->latestNonDefaultReply)
                                                        ->latestFollowup;
                                                @endphp

                                                @if ($latestFollowup)
                                                    <strong>फ़ॉलोअप तिथि:
                                                    </strong>{{ \Carbon\Carbon::parse($latestFollowup->followup_date)->format('d-m-Y h:i A') }}
                                                    <br>
                                                    <strong>फ़ॉलोअप दिया:
                                                    </strong>{{ $latestFollowup->createdByAdmin->admin_name ?? 'N/A' }}
                                                    <br>
                                                    <strong>संपर्क स्थिति:
                                                    </strong>{{ $latestFollowup->followup_contact_status ?? 'N/A' }} <br>
                                                    <strong>संपर्क विवरण:
                                                    </strong>{{ $latestFollowup->followup_contact_description ?? 'N/A' }}
                                                    <br><br>
                                                    <strong>स्थिति: </strong>{{ $latestFollowup->followup_status_text() }}
                                                    <br>
                                                @else
                                                    <span class="text-muted">कोई फ़ॉलोअप उपलब्ध नहीं</span>
                                                @endif
                                            </td>

                                            @if ($complaint->latestNonDefaultReply)
                                                <!-- Button -->
                                                <td>
                                                    <button type="button" class="btn btn-sm btn-warning openModalBtn"
                                                        data-complaint-id="{{ $complaint->complaint_id }}"
                                                        data-complaint-reply-id="{{ $complaint->latestNonDefaultReply->complaint_reply_id }}">
                                                        फ़ॉलोअप
                                                    </button>
                                                </td>

                                                <!-- Modal -->
                                                {{-- <div class="modal fade"
                                                    id="contactStatusModal{{ $complaint->latestNonDefaultReply->complaint_reply_id }}"
                                                    tabindex="-1" role="dialog"
                                                    aria-labelledby="contactStatusLabel{{ $complaint->latestNonDefaultReply->complaint_reply_id }}"
                                                    aria-hidden="true">

                                                    <div class="modal-dialog modal-sm" role="document">
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title"
                                                                    id="contactStatusLabel{{ $complaint->latestNonDefaultReply->complaint_reply_id }}">
                                                                    संपर्क स्थिति अपडेट करें
                                                                </h5>
                                                                <button type="button" class="close"
                                                                    data-dismiss="modal" aria-label="Close">
                                                                    <span aria-hidden="true">&times;</span>
                                                                </button>
                                                            </div>

                                                            <form
                                                                action="{{ route('update.contact.status', $complaint->latestNonDefaultReply->complaint_reply_id) }}"
                                                                method="POST">
                                                                @csrf

                                                                <input type="hidden" name="complaint_id"
                                                                    value="{{ $complaint->complaint_id }}">


                                                                <div class="modal-body">

                                                                    <label class="form-label">संपर्क स्थिति:</label>
                                                                    <select class="form-control" name="contact_status">
                                                                        <option value="">--चयन करें--</option>
                                                                        <option value="फोन बंद था">फोन बंद था</option>
                                                                        <option value="सूचना दे दी गई है">सूचना दे दी गई
                                                                            है
                                                                        </option>
                                                                        <option value="फोन व्यस्त था">फोन व्यस्त था
                                                                        </option>
                                                                        <option value="कोई उत्तर नहीं मिला">कोई उत्तर
                                                                            नहीं
                                                                            मिला</option>
                                                                        <option value="बाद में संपर्क करने को कहा">बाद
                                                                            में
                                                                            संपर्क करने को कहा</option>
                                                                        <option value="कॉल काट दी गई">कॉल काट दी गई
                                                                        </option>
                                                                        <option value="संख्या आउट ऑफ कवरेज थी">संख्या
                                                                            आउट ऑफ
                                                                            कवरेज थी</option>
                                                                        <option value="SMS भेजा गया">SMS/Whatsapp भेजा
                                                                            गया
                                                                        </option>
                                                                        <option value="फोन नंबर उपलब्ध नहीं है">फोन नंबर
                                                                            उपलब्ध नहीं है</option>
                                                                    </select>


                                                                    <label class="form-label mt-4">संपर्क विवरण:</label>
                                                                    <textarea name="contact_update" class="form-control" rows="6"></textarea>

                                                                </div>
                                                                <div class="modal-footer">
                                                                    <button type="submit"
                                                                        class="btn btn-success btn-sm">अपडेट</button>
                                                                </div>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div> --}}
                                            @else
                                                <td><span class="text-muted">N/A</span></td>
                                            @endif


                                            <td>
                                                <a href="{{ route('follow_up.show', $complaint->complaint_id) }}"
                                                    class="btn btn-sm btn-primary" style="white-space: nowrap;">
                                                    क्लिक करें
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>


        <div class="modal fade" id="contactStatusModal" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog modal-sm" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">संपर्क स्थिति अपडेट करें</h5>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>
                    <form id="contactStatusForm" method="POST">
                        @csrf
                        <input type="hidden" name="complaint_reply_id" id="modal_complaint_reply_id">
                        <input type="hidden" name="complaint_id" id="modal_complaint_id">
                        <div class="modal-body">
                            <label>संपर्क स्थिति:</label>
                            <select class="form-control" name="contact_status">
                                <option value="">--चयन करें--</option>
                                <option value="फोन बंद था">फोन बंद था</option>
                                <option value="सूचना दे दी गई है">सूचना दे दी गई है</option>
                                <option value="फोन व्यस्त था">फोन व्यस्त था</option>
                                <option value="कोई उत्तर नहीं मिला">कोई उत्तर नहीं मिला</option>
                                <option value="बाद में संपर्क करने को कहा">बाद में संपर्क करने को कहा</option>
                                <option value="कॉल काट दी गई">कॉल काट दी गई</option>
                                <option value="संख्या आउट ऑफ कवरेज थी">संख्या आउट ऑफ कवरेज थी</option>
                                <option value="SMS भेजा गया">SMS/Whatsapp भेजा गया</option>
                                <option value="फोन नंबर उपलब्ध नहीं है">फोन नंबर उपलब्ध नहीं है</option>
                            </select>
                            <label class="form-label mt-2">संपर्क विवरण:</label>
                            <textarea name="contact_update" class="form-control" rows="6"></textarea>
                        </div>
                        <div class="modal-footer">
                            <button type="submit" class="btn btn-success btn-sm">अपडेट</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>


    @push('scripts')
        <script>
            $(document).ready(function() {
                // Initialize DataTable with destroy true
                let table = $('#example').DataTable({
                    pageLength: 10,
                    responsive: true,
                    destroy: true,
                    dom: '<"row mb-2"<"col-sm-3"l><"col-sm-6"B><"col-sm-3"f>>' +
                        '<"row"<"col-sm-12"tr>>' +
                        '<"row mt-2"<"col-sm-5"i><"col-sm-7"p>>',
                    buttons: [{
                            extend: "csv",
                            exportOptions: {
                                modifier: {
                                    page: "all"
                                }
                            },
                        },
                        {
                            extend: "excel",
                            exportOptions: {
                                modifier: {
                                    page: "all"
                                }
                            },
                        },
                    ],
                    lengthMenu: [
                        [10, 25, 50, 100, 500, -1],
                        [10, 25, 50, 100, 500, "All"],
                    ],
                });

                $('#applyFilters').on('click', function(e) {
                    e.preventDefault();
                    fetchComplaints();
                });

                function fetchComplaints() {
                    let data = $('#followupfilterform').serialize();

                    $("#loader-wrapper").show();

                    $.ajax({
                        url: "{{ route('next_followup_filter.index') }}",
                        type: "GET",
                        data: data,
                        success: function(res) {
                            // Destroy existing table instance
                            table.destroy();

                            // Replace tbody content
                            $('#complaintsTableBody').html(res.html);

                            // Re-initialize DataTable
                            table = $('#example').DataTable({
                                pageLength: 10,
                                responsive: true,
                                destroy: true
                            });

                            $('#complaint-count').text(res.count);
                        },
                        error: function(err) {
                            console.error(err);
                        },
                        complete: function() {
                            $("#loader-wrapper").hide();
                        }
                    });
                }

                $('#resetFollowup').on('click', function() {
                    location.reload();
                });


                $(document).on('click', '.openModalBtn', function() {
                    let complaintId = $(this).data('complaint-id');
                    let complaintReplyId = $(this).data('complaint-reply-id');

                    $('#modal_complaint_id').val(complaintId);
                    $('#modal_complaint_reply_id').val(complaintReplyId);

                    $('#contactStatusForm').attr('action', '/update-contact-status/' + complaintReplyId);

                    $('#contactStatusModal').modal('show');
                });
            });
        </script>
    @endpush
@endsection
