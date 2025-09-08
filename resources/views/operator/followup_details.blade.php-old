@php
    $pageTitle = 'फ़ॉलोअप विवरण';
    $breadcrumbs = [
        'कार्यालय' => '#',
        'फ़ॉलोअप विवरण' => '#',
    ];
@endphp

@extends('layouts.app')
@section('title', 'Followup Details')

@section('content')
    <div class="container">

        <div id="success-alert" class="alert alert-success alert-dismissible fade show d-none" role="alert">
            <span id="success-message"></span>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>

        <div class="card mb-3">
            <div class="card-header d-flex justify-content-between">
                <h5>{{ $complaint->complaint_number }}</h5>
                <span style="color: gray;"> स्थिति: {!! $complaint->statusText() !!}</span>
            </div>
            <div class="card-body row g-3">
                @php
                    $fields = [
                        'शिकायतकर्ता का नाम' => $complaint->name,
                        'शिकायतकर्ता का मोबाइल' => $complaint->mobile_number,
                        'पिता का नाम' => $complaint->father_name,
                        'रेफरेंस नाम' => $complaint->reference_name,
                        'मतदाता पहचान' => $complaint->voter_id,
                        'संभाग का नाम' => $complaint->division->division_name ?? '',
                        'जिले का नाम' => $complaint->district->district_name ?? '',
                        'विधानसभा का नाम' => $complaint->vidhansabha->vidhansabha ?? '',
                        'नगर/मंडल' =>
                            ($complaint->gram->nagar_name ?? '-') . ' - ' . ($complaint->mandal->mandal_name ?? '-'),
                        'मतदान केंद्र/ग्राम/वार्ड' =>
                            ($complaint->polling->polling_name ?? '-') .
                            ' (' .
                            ($complaint->polling->polling_no ?? '-') .
                            ') - ' .
                            ($complaint->area->area_name ?? '-'),
                        'लिंग' => $complaint->registration->gender ?? '',
                        'धर्म' => $complaint->registration->religion ?? '',
                        'वर्ग/श्रेणी' => $complaint->registration->caste ?? '',
                        'जाति' => $complaint->registration->jati ?? '',
                        'शिक्षा' => $complaint->registration->education ?? '',
                        'व्यवसाय' => $complaint->registration->business ?? '',
                        'पद' => $complaint->registration->position ?? '',
                        'शिकायत का दिनांक' => $complaint->posted_date ?? '',
                    ];
                @endphp

                @foreach ($fields as $label => $value)
                    <div class="col-md-4 d-flex align-items-center mb-2">
                        <label class="form-label me-2 mr-2 mb-0"
                            style="white-space: nowrap; min-width: 140px;">{{ $label }}:</label>
                        <input type="text" class="form-control" value="{{ $value }}" disabled>
                    </div>
                @endforeach




                <div class="col-md-4 d-flex align-items-start mt-3">
                    <label class="form-label mr-2 me-2" style="white-space: nowrap; min-width: 120px;">पूरा पता:</label>
                    <textarea class="form-control" rows="3" style="flex: 1;" disabled>{{ $complaint->address }}</textarea>
                </div>


                <div class="col-md-4 d-flex align-items-start mt-3" style="margin-top: 8px;">
                    <label class="form-label mr-2 me-2" style="white-space: nowrap; min-width: 120px;">समस्या का
                        विषय</label>
                    <textarea class="form-control" rows="3" disabled>{{ $complaint->issue_title }}</textarea>
                </div>

                <div class="col-md-4 d-flex align-items-start mt-3" style="margin-top: 8px;">
                    <label class="form-label mr-2 me-2" style="white-space: nowrap; min-width: 120px;">समस्या</label>
                    <textarea class="form-control" rows="3" disabled>{{ $complaint->issue_description }}</textarea>
                </div>

                <div class="col-md-4 mt-4" style="justify-content: center; align-items:center">
                    <label class="form-label">फ़ाइल अटैचमेंट</label>
                    @if (!empty($complaint->issue_attachment))
                        <a href="{{ asset('assets/upload/complaints/' . $complaint->issue_attachment) }}"
                            class="btn btn-primary" target="_blank">अटैचमेंट खोलें</a>
                    @else
                        <button class="btn btn-sm btn-secondary" disabled>कोई अटैचमेंट नहीं है</button>
                    @endif
                </div>
            </div>
        </div>



        <div class="card container" style="color: #000;">
            <h5 class="my-3">Reply History for {{ $complaint->complaint_number }}</h5>

            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead class="thead-dark">
                        <tr>
                            <th>निर्धारित उत्तर</th>
                            <th>स्थिति</th>
                            <th>दिनांक</th>
                            <th>द्वारा भेजा गया</th>
                            <th>को भेजा गया</th>
                            <th>फ़ॉलोअप विवरण</th>
                            <th>विवरण देखें</th>
                        </tr>
                    </thead>
                    <tbody style="color: #000;">
                        @forelse ($complaint->replies as $reply)
                            <tr>
                                <td> {{ $reply->selected_reply === 0 ? 'अन्य' : $reply->predefinedReply->reply ?? '-' }}
                                </td>
                                <td>{!! $reply->statusTextPlain() !!}</td>
                                <td>{{ $reply->reply_date ? \Carbon\Carbon::parse($reply->reply_date)->format('d-m-Y h:i A') : 'N/A' }}
                                </td>
                                <td>{{ $reply->replyfrom?->admin_name ?? '' }}</td>
                                <td> {{ $reply->forwardedToManager?->admin_name ?? '' }}</td>

                                <td>
                                    @php
                                        $replyData = [
                                            'complaint_reply' => $reply->complaint_reply,
                                            'reply_date' => $reply->reply_date,
                                            'replyfrom' => $reply->replyfrom,
                                            'forwardedToManager' => $reply->forwardedToManager,
                                            'status_html' => $reply->statusText(),
                                        ];

                                        $followupsData = $reply->followups->map(function ($f) {
                                            return [
                                                'followup_date' => $f->followup_date,
                                                'followup_contact_status' => $f->followup_contact_status,
                                                'followup_contact_description' => $f->followup_contact_description,
                                                'created_by_admin' => $f->createdByAdmin,
                                                'followup_status_text' => $f->followup_status_text(),
                                            ];
                                        });
                                    @endphp

                                    <button class="btn btn-sm btn-warning openFollowupHistoryBtn"
                                        data-reply='@json($replyData)'
                                        data-followups='@json($followupsData)'>
                                        फ़ॉलोअप विवरण
                                    </button>

                                </td>

                                <td>
                                    <button type="button" class="btn btn-sm btn-info view-details-btn" data-toggle="modal"
                                        data-target="#detailsModal" data-reply="{{ $reply->complaint_reply }}"
                                        {{-- data-contact="{{ $reply->contact_status }}" --}} data-review="{{ $reply->review_date }}"
                                        data-importance="{{ $reply->importance }}" {{-- data-critical="{{ $reply->criticality }}" --}}
                                        data-reply_from="{{ $reply->replyfrom?->admin_name ?? '' }}" {{-- data-details="{{ $reply->contact_update }}" --}}
                                        data-reply-date="{{ \Carbon\Carbon::parse($reply->reply_date)->format('d-m-Y h:i A') }}"
                                        data-admin="{{ $reply->forwardedToManager?->admin_name ?? '' }}"
                                        {{-- data-status="{{ strip_tags($reply->statusTextPlain()) }}" --}} data-status-html="{!! htmlspecialchars($reply->statusText(), ENT_QUOTES, 'UTF-8') !!}"
                                        data-predefined="{{ $reply->selected_reply === 0 ? 'अन्य' : $reply->predefinedReply->reply ?? '-' }}"
                                        data-cb-photo="{{ $reply->cb_photo ? asset($reply->cb_photo) : '' }}"
                                        data-ca-photo="{{ $reply->ca_photo ? asset($reply->ca_photo) : '' }}"
                                        data-video="{{ $reply->c_video ? asset($reply->c_video) : '' }}">
                                        विवरण
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center">कोई जवाब उपलब्ध नहीं है।</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>




        {{-- followup history modal --}}
        <div class="modal fade" id="followupHistoryModal" tabindex="-1" role="dialog"
            aria-labelledby="followupHistoryModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">फ़ॉलोअप विवरण</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <h5 class="mt-3 mb-2">जवाब विवरण</h5>
                        <div class="table-responsive">
                            <div class="table-responsive">
                                <table class="table table-bordered" style="color: black; border: 1px solid black">
                                    <thead class="thead-light border-header">
                                        <tr>
                                            <th>जवाब</th>
                                            <th>जवाब- द्वारा भेजा गया</th>
                                            <th>जवाब- को भेजा गया</th>
                                            <th>स्थिति</th>
                                            <th>जवाब तिथि</th>
                                        </tr>
                                    </thead>
                                    <tbody id="reply_detail" class="border-header">
                                        <tr>
                                            <td id="modal-reply-text">—</td>
                                            <td id="modal-reply-from">—</td>
                                            <td id="modal-admin">—</td>
                                            <td id="modal-reply-status">—</td>
                                            <td id="modal-reply-date">—</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>


                        <h5 class="mt-4 mb-2">फ़ॉलोअप विवरण</h5>
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered" style="color: black">
                                <thead class="thead-light border-header">
                                    <tr>
                                        <th>फ़ॉलोअप तिथि</th>
                                        <th>संपर्क स्थिति</th>
                                        <th>विवरण</th>
                                        <th>दिया गया द्वारा</th>
                                        <th>स्थिति</th>
                                    </tr>
                                </thead>
                                <tbody id="followupHistoryTable" class="border-header">
                                    <tr>
                                        <td colspan="5" class="text-muted">कोई डेटा उपलब्ध नहीं</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">बंद करें</button>
                    </div>
                </div>
            </div>
        </div>


        {{-- reply history modal --}}
        <div class="modal fade" id="detailsModal" tabindex="-1" role="dialog" aria-labelledby="detailsModalLabel"
            aria-hidden="true">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">

                    <div class="modal-header">
                        <h5 class="modal-title">जवाब विवरण</h5>
                        <div id="modal-status"></div>
                    </div>

                    <div class="modal-body">
                        <div class="border p-2 rounded mb-4 bg-light">
                            <h5>समस्या / समाधान</h5>
                            <p id="modal-reply">—</p>
                        </div>

                        <div class="border p-2 rounded mb-3">
                            <h5>उत्तर विवरण</h5>
                            <div class="table-responsive">
                                <table style="color: black" class="table table-bordered text-center align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th>पूर्वनिर्धारित उत्तर</th>
                                            <th>दिनांक</th>
                                            <th>द्वारा भेजा गया</th>
                                            <th>को भेजा गया</th>
                                            {{-- <th>संपर्क स्थिति</th> --}}
                                            {{-- <th>संपर्क विवरण</th> --}}
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td id="modal-predefined">—</td>
                                            <td id="modal-date">—</td>
                                            <td id="modal-reply-from">—</td>
                                            <td id="modal-admin">—</td>
                                            {{-- <td id="modal-contact">—</td> --}}
                                            {{-- <td id="modal-details">—</td> --}}
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <div class="table-responsive">
                                <table style="color: black" class="table table-bordered text-center align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th>रीव्यू दिनांक</th>
                                            <th>महत्त्व स्तर</th>
                                            {{-- <th>गंभीरता स्तर</th> --}}
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td id="modal-review">—</td>
                                            <td id="modal-importance">—</td>
                                            {{-- <td id="modal-critical">—</td> --}}
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <div class="border p-2 rounded mb-3 bg-light">
                                <h5 class="mb-3 text-center">अटैचमेंट्स</h5>
                                <div class="d-flex flex-wrap justify-content-center gap-2">
                                    <div class="card p-3 mr-2  border-0 shadow rounded"
                                        style="background-color: #ffffff;">
                                        <div class="text-center" style="width: 200px;">
                                            <div>पूर्व स्थिति की तस्वीर</div>
                                            <a href="#" id="cb-photo-link"
                                                class="btn btn-sm btn-outline-primary mt-1" target="_blank">खोलें</a>
                                        </div>
                                    </div>

                                    <div class="card p-3 mr-2  border-0 shadow rounded"
                                        style="background-color: #ffffff;">
                                        <div class="text-center" style="width: 200px;">
                                            <div>बाद की तस्वीर</div>
                                            <a href="#" id="ca-photo-link"
                                                class="btn btn-sm btn-outline-primary mt-1" target="_blank">खोलें</a>
                                        </div>
                                    </div>

                                    <div class="card p-3 mr-2  border-0 shadow rounded"
                                        style="background-color: #ffffff;">
                                        <div class="text-center" style="width: 200px;">
                                            <div>वीडियो लिंक</div>
                                            <a href="#" id="video-link" class="btn btn-sm btn-outline-primary mt-1"
                                                target="_blank">खोलें</a>
                                        </div>
                                    </div>

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
            $(document).on('click', '.view-details-btn', function() {
                const reply = $(this).data('reply') || '—';
                const reply_from = $(this).data('reply_from') || '—';
                // const contact = $(this).data('contact') || '—';
                // const details = $(this).data('details') || '—';
                const replyDate = $(this).data('reply-date') || '—';
                // const status = $(this).data('status') || '—';
                const statusHtml = $(this).data('status-html') || '—';
                const admin = $(this).data('admin') || '—';
                const predefinedRaw = $(this).data('predefined');
                const predefined = predefinedRaw === 0 ? 'अन्य' : (predefinedRaw || '—');

                const cbPhoto = $(this).data('cb-photo');
                const caPhoto = $(this).data('ca-photo');
                const video = $(this).data('video');
                const review = $(this).data('review');
                const importance = $(this).data('importance');
                // const critical = $(this).data('critical');

                $('#modal-reply').text(reply);
                // $('#modal-status').text(status);
                $('#modal-status').html(statusHtml);
                $('#modal-date').text(replyDate);
                $('#modal-admin').text(admin);
                $('#modal-predefined').text(predefined);
                // $('#modal-contact').text(contact);
                // $('#modal-details').text(details);
                $('#modal-review').text(review);
                $('#modal-importance').text(importance);
                $('#modal-reply-from').text(reply_from);
                // $('#modal-critical').text(critical);

                cbPhoto ? $('#cb-photo-link').attr('href', cbPhoto).show() : $('#cb-photo-link').hide();
                caPhoto ? $('#ca-photo-link').attr('href', caPhoto).show() : $('#ca-photo-link').hide();
                video ? $('#video-link').attr('href', video).show() : $('#video-link').hide();
            });


            document.addEventListener("DOMContentLoaded", function() {
                document.querySelectorAll(".openFollowupHistoryBtn").forEach(function(btn) {
                    btn.addEventListener("click", function() {
                        let reply = {};
                        let followups = [];

                        try {
                            reply = this.dataset.reply ? JSON.parse(this.dataset.reply) : {};
                            followups = this.dataset.followups ? JSON.parse(this.dataset.followups) :
                        [];
                        } catch (e) {
                            console.error('Invalid JSON in dataset:', e);
                        }


                        document.getElementById("modal-reply-text").textContent = reply
                            .complaint_reply || '—';
                        document.getElementById("modal-reply-status").innerHTML = reply.status_html ||
                            '—';

                        document.getElementById("modal-reply-date").textContent = reply.reply_date ?
                            new Date(reply.reply_date).toLocaleString('hi-IN') : '—';
                        document.getElementById("modal-reply-from").textContent = reply.replyfrom
                            ?.admin_name || '—';
                        document.getElementById("modal-admin").textContent = reply.forwardedToManager
                            ?.admin_name || '—';

                        console.log(reply.forwardedToManager);
                        // populate followup history
                        const tableBody = document.getElementById("followupHistoryTable");
                        tableBody.innerHTML = '';

                        if (followups.length > 0) {
                            followups.forEach(f => {
                                const row = document.createElement('tr');
                                row.innerHTML = `
                        <td>${new Date(f.followup_date).toLocaleString('hi-IN')}</td>
                        <td>${f.followup_contact_status ?? ''}</td>
                        <td>${f.followup_contact_description ?? ''}</td>
                        <td>${f.created_by_admin?.admin_name ?? 'N/A'}</td>
                        <td>${f.followup_status_text ?? ''}</td>
                    `;
                                tableBody.appendChild(row);
                            });
                        } else {
                            tableBody.innerHTML =
                                `<tr><td colspan="5" class="text-muted text-center">कोई फ़ॉलोअप उपलब्ध नहीं</td></tr>`;
                        }

                        $("#followupHistoryModal").modal("show");
                    });
                });
            });
        </script>
    @endpush
@endsection
