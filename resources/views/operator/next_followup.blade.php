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
                        <ul class="nav nav-tabs mb-3" id="followupTabs" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link active" data-status="not_done" href="#">
                                    Not Done (<span id="count-not_done">0</span>)
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-status="update_followup" href="#">
                                    Update Follow-up (<span id="count-update_followup">0</span>)
                                </a>
                            </li>
                            <li class="nav-item" style="display: none">
                                <a class="nav-link" data-status="done_not_completed" href="#">
                                    Done Today (<span id="count-done_not_completed">0</span>)
                                </a>
                            </li>
                            <li class="nav-item" style="display: none">
                                <a class="nav-link" data-status="completed" href="#">
                                    Completed (<span id="count-completed">0</span>)
                                </a>
                            </li>
                            {{-- <li class="nav-item">
                                <a class="nav-link" data-status="all" href="#">
                                    All ({{ $complaints->count() }})
                                </a>
                            </li> --}}
                        </ul>

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
                                        <th>शिकायतकर्ता</th>
                                        <th>आवेदक</th>
                                        <th>विभाग</th>
                                        <th>शिकायत की तिथि</th>
                                        <th style="width: 100px">जवाब की स्थिति</th>
                                        <th>जवाब</th>
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
                                                <strong>नाम: </strong>{{ $complaint->name ?? 'N/A' }} <br>
                                                <strong>मोबाइल: </strong>{{ $complaint->mobile_number ?? '' }} <br>
                                                <strong>पुत्र श्री: </strong>{{ $complaint->father_name ?? '' }} <br>
                                            </td>

                                            <td>
                                                @if ($complaint->type == 2)
                                                    {{ $complaint->admin->admin_name ?? '-' }}
                                                @else
                                                    {{ $complaint->registrationDetails->name ?? '-' }}
                                                @endif
                                            </td>

                                            <td>{{ $complaint->complaint_department ?? 'N/A' }}</td>

                                            <td>{{ \Carbon\Carbon::parse($complaint->posted_date)->format('d-m-Y h:i A') }}
                                            </td>

                                            <td>
                                                <strong>फॉरवर्ड:
                                                </strong>{{ $complaint->latestNonDefaultReply->forwardedToManager->admin_name ?? 'N/A' }}
                                                <br>
                                                <strong>तिथि:
                                                </strong>{{ $complaint->latestNonDefaultReply->reply_date ?? '' }} <br>
                                                <strong>स्थिति: </strong>{!! $complaint->statusTextPlain() !!}
                                            </td>

                                            <td>{{ $complaint->latestNonDefaultReply->complaint_reply ?? '' }}</td>

                                            @if ($complaint->latestNonDefaultReply)
                                                <!-- Button -->
                                                <td>
                                                    <button type="button" class="btn btn-sm btn-warning"
                                                        data-toggle="modal"
                                                        data-target="#contactStatusModal{{ $complaint->latestNonDefaultReply->complaint_reply_id }}">
                                                        फ़ॉलोअप
                                                    </button>
                                                </td>

                                                <!-- Modal -->
                                                <div class="modal fade"
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
                                                                <button type="button" class="close" data-dismiss="modal"
                                                                    aria-label="Close">
                                                                    <span aria-hidden="true">&times;</span>
                                                                </button>
                                                            </div>

                                                            <form
                                                                action="{{ route('update.contact.status', $complaint->latestNonDefaultReply->complaint_reply_id) }}"
                                                                method="POST">
                                                                @csrf
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
                                                </div>
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
    </div>


    @push('scripts')
        <script>
            // Set initial tab counts
            document.getElementById("count-not_done").innerText = "{{ $counts['not_done'] }}";
            document.getElementById("count-update_followup").innerText = "{{ $counts['update_followup'] }}";
            document.getElementById("count-done_not_completed").innerText = "{{ $counts['done_not_completed'] }}";
            document.getElementById("count-completed").innerText = "{{ $counts['completed'] }}";

            function updateCount(status) {
                let visibleCount = 0;
                document.querySelectorAll("#complaintsTableBody tr").forEach(row => {
                    if (status === "all" || row.dataset.followupStatus === status) {
                        visibleCount++;
                    }
                });
                document.getElementById("complaint-count").innerText = visibleCount;
            }

            document.querySelectorAll('#followupTabs .nav-link').forEach(tab => {
                tab.addEventListener('click', function(e) {
                    e.preventDefault();
                    const status = this.dataset.status;

                    document.querySelectorAll('#complaintsTableBody tr').forEach(row => {
                        row.style.display = (status === 'all' || row.dataset.followupStatus ===
                            status) ? '' : 'none';
                    });

                    document.querySelectorAll('#followupTabs .nav-link').forEach(t => t.classList.remove(
                        'active'));
                    this.classList.add('active');

                    updateCount(status);
                });
            });

            // Default -> Not Done
            updateCount('not_done');
        </script>
    @endpush
@endsection
