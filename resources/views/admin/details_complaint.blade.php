@php
    $pageTitle = 'समस्याएँ देखे';
    $breadcrumbs = [
        'एडमिन' => '#',
        'समस्याएँ देखे' => '#',
    ];
@endphp

@extends('layouts.app')
@section('title', 'View Complaints')

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
                <span style="color: gray;">Status: {!! $complaint->statusText() !!}</span>
            </div>
            <div class="card-body row g-3">
                @php

                    $fields = [
                        'शिकायतकर्ता का नाम' => $complaint->name,
                        'शिकायतकर्ता का मोबाइल' => $complaint->mobile_number,
                        'पिता का नाम' => $complaint->father_name,
                        'रेफरेंस नाम' => $complaint->reference_name,
                        // 'पदाधिकारी का मोबाइल' => $complaint->mobile_number,
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
                        // 'मंडल का नाम' => $complaint->mandal->mandal_name ?? '',
                        // 'नगर केंद्र/ग्राम केंद्र' => $complaint->gram->nagar_name ?? '',
                        // 'मतदान केंद्र' =>
                        //     ($complaint->polling->polling_name ?? '') . '-' . $complaint->polling->polling_no,
                        // 'ग्राम चौपाल/वार्ड चौपाल' => $complaint->area->area_name ?? '',
                        'Gender' => $complaint->registration->gender ?? '',
                        'Religion' => $complaint->registration->religion ?? '',
                        'Caste' => $complaint->registration->caste ?? '',
                        'Jati' => $complaint->registration->jati ?? '',
                        'Education' => $complaint->registration->education ?? '',
                        'Business' => $complaint->registration->business ?? '',
                        'Position' => $complaint->registration->position ?? '',
                        'शिकायत का दिनांक' => $complaint->posted_date,
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

        {{-- Reply History --}}
        {{-- <div class="card container" style="color: #000; ">
            <h5 class="my-3">Reply History for {{ $complaint->complaint_number }}</h5>
            <div class="row">
                @foreach ($complaint->replies as $reply)
                    <div class="col-md-4 mb-4">
                        <div class="card h-100">
                            <div class="card-body">
                                <p><strong>Date:</strong>
                                    {{ $reply->reply_date ? \Carbon\Carbon::parse($reply->reply_date)->format('d-m-Y') : 'N/A' }}
                                </p>
                                @php
                                    $replyFromName =
                                        $reply->reply_from == 1 ? $reply->complaint->user->name ?? 'User' : 'BJS Team';
                                @endphp

                                <p><strong>Reply Given By:</strong> {{ $replyFromName }}</p>
                                <p><strong>समस्या/समाधान:</strong> {{ $reply->complaint_reply }}</p>

                                @if (!empty($reply->cb_photo))
                                    <label class="form-label mr-3">Before Images: </label>
                                    <a href="{{ asset($reply->cb_photo) }}" class="btn btn-primary mb-3"
                                        target="_blank">Open Attachment</a>
                                @endif

                                @if (!empty($reply->ca_photo))
                                    <label class="form-label mr-4">After Images: </label>
                                    <a href="{{ asset($reply->ca_photo) }}" class="btn btn-primary" target="_blank">Open
                                        Attachment</a>
                                @endif

                                @if (!empty($reply->c_video))
                                    <label class="form-label mr-4">Youtube Link: </label>
                                    <a href="{{ asset($reply->c_video) }}" class="btn btn-primary"
                                        target="_blank">{{ $reply->c_video }}</a>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div> --}}
        <div class="card container" style="color: #000;">
            <h5 class="my-3">Reply History for {{ $complaint->complaint_number }}</h5>

            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead class="thead-dark">
                        <tr>
                            <th>निर्धारित उत्तर</th>
                            <th>स्थिति</th>
                            <th>दिनांक</th>
                            <th>भेजा गया</th>
                            <th>विवरण देखें</th>
                            {{-- <th>समस्या/समाधान</th>
                            <th>पूर्व स्थिति की तस्वीर</th>
                            <th>बाद की तस्वीर</th>
                            <th>यूट्यूब लिंक</th> --}}
                        </tr>
                    </thead>
                    <tbody style="color: #000;">
                        @forelse ($complaint->replies as $reply)
                            {{-- @php
                                $replyFromName =
                                    $reply->reply_from == 1 ? $reply->complaint->user->name ?? 'User' : 'BJS Team';
                            @endphp --}}
                            <tr>
                              <td> {{ $reply->selected_reply === 0 ? 'अन्य' : ($reply->predefinedReply->reply ?? '-') }}</td>
                                <td>{!! $reply->statusTextPlain() !!}</td>
                                <td>{{ $reply->reply_date ? \Carbon\Carbon::parse($reply->reply_date)->format('d-m-Y h:i A') : 'N/A' }}
                                </td>
                                <td> {{ $reply->forwardedToManager?->admin_name ?? '' }}</td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-info view-details-btn" data-toggle="modal"
                                        data-target="#detailsModal" data-reply="{{ $reply->complaint_reply }}"
                                        data-reply-date="{{ \Carbon\Carbon::parse($reply->reply_date)->format('d-m-Y h:i A') }}"
                                       data-contact="{{ $reply->contact_status }}"
                                        data-admin="{{ $reply->forwardedToManager?->admin_name ?? '' }}"
                                        data-status="{{ strip_tags($reply->statusTextPlain()) }}"
                                        data-predefined="{{ $reply->selected_reply === 0 ? 'अन्य' : ($reply->predefinedReply->reply ?? '-') }}"
                                        data-cb-photo="{{ $reply->cb_photo ? asset($reply->cb_photo) : '' }}"
                                        data-ca-photo="{{ $reply->ca_photo ? asset($reply->ca_photo) : '' }}"
                                        data-video="{{ $reply->c_video ? asset($reply->c_video) : '' }}">
                                        विवरण </button>
                                </td>
                                {{-- <td>{{ $replyFromName }}</td> --}}
                                {{-- <td>
                                    @if (!empty($reply->cb_photo))
                                        <a href="{{ asset($reply->cb_photo) }}" class="btn btn-sm btn-primary"
                                            target="_blank">खोलें</a>
                                    @else
                                        —
                                    @endif
                                </td>
                                <td>
                                    @if (!empty($reply->ca_photo))
                                        <a href="{{ asset($reply->ca_photo) }}" class="btn btn-sm btn-primary"
                                            target="_blank">खोलें</a>
                                    @else
                                        —
                                    @endif
                                </td>
                                <td>
                                    @if (!empty($reply->c_video))
                                        <a href="{{ $reply->c_video }}" class="btn btn-sm btn-primary"
                                            target="_blank">लिंक</a>
                                    @else
                                        —
                                    @endif
                                </td> --}}
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

        <div class="modal fade" id="detailsModal" tabindex="-1" role="dialog" aria-labelledby="detailsModalLabel"
            aria-hidden="true">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">

                    <div class="modal-header">
                        <h5 class="modal-title">जवाब विवरण</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span>&times;</span>
                        </button>
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
                                            <th>स्थिति</th>
                                            <th>तारीख</th>
                                            <th>भेजा गया</th>
                                            <th>संपर्क स्थिति</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td id="modal-predefined">—</td>
                                            <td id="modal-status">—</td>
                                            <td id="modal-date">—</td>
                                            <td id="modal-admin">—</td>
                                            <td id="modal-contact">—</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <div class="border p-2 rounded mb-3 bg-light">
                                <h5 class="mb-3 text-center">अटैचमेंट्स</h5>
                                <div class="d-flex flex-wrap justify-content-center gap-2">
                                    <div class="card p-3 mr-2  border-0 shadow rounded" style="background-color: #ffffff;">
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

        {{-- Reply Form --}}
        {{-- <div class="card">
            <div class="card-header" style="color: #000">Reply to {{ $complaint->complaint_number }}</div>
            <div class="card-body">
                <form method="POST" action="{{ route('complaints.reply', $complaint->complaint_id) }}"
                    enctype="multipart/form-data">
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label">समस्या/समाधान में प्रगति</label>
                            <textarea name="cmp_reply" class="form-control" rows="6" required></textarea>
                        </div>

                        <div class="col-md-2">
                            <label class="form-label">Before Photo</label>
                            <input type="file" name="cb_photo[]" class="form-control" multiple accept="image/*">
                            <label class="form-label mt-3">After Photo</label>
                            <input type="file" name="ca_photo[]" class="form-control" multiple accept="image/*">
                        </div>

                        <div class="col-md-2">
                            <label class="form-label">YouTube Link</label>
                            <input type="url" name="c_video" class="form-control">
                            <label class="form-label mt-3">Complaint Status: <span class="tx-danger"
                                    style="font-size: 11px; color: red;">*</span></label>
                            <select name="cmp_status" class="form-control" required>
                                <option value="">Select</option>
                                <option value="1" {{ $complaint->status == 1 ? 'selected' : '' }}>Opened</option>
                                <option value="2" {{ $complaint->status == 2 ? 'selected' : '' }}>Processing</option>
                                <option value="3" {{ $complaint->status == 3 ? 'selected' : '' }}>On Hold</option>
                                <option value="4" {{ $complaint->status == 4 ? 'selected' : '' }}>Closed</option>
                            </select>
                        </div>

                        <div class="col-12 mt-3">
                            <button type="submit" class="btn btn-primary">Post Reply</button>
                        </div>
                    </div>
                </form>
            </div>
        </div> --}}

        <div class="card">
            <div class="card-header" style="color: #000">Reply to {{ $complaint->complaint_number }}</div>
            <div class="card-body">
                <form id="replyForm" method="POST" action="{{ route('complaints.reply', $complaint->complaint_id) }}"
                    enctype="multipart/form-data">
                    @csrf

                    <div class="row mb-3">
                        <div class="col-md-3">
                            <label class="form-label ">शिकायत की स्थिति: <span class="tx-danger"
                                    style="color: red;">*</span></label>
                            <select name="cmp_status" id="cmp_status" class="form-control" required>
                                <option value="">--चुने--</option>
                                <option value="1" {{ $complaint->complaint_status == 1 ? 'selected' : '' }}>शिकायत
                                    दर्ज
                                </option>
                                <option value="2" {{ $complaint->complaint_status == 2 ? 'selected' : '' }}>प्रक्रिया
                                    में
                                </option>
                                <option value="3" {{ $complaint->complaint_status == 3 ? 'selected' : '' }}>स्थगित
                                </option>
                                <option value="4" {{ $complaint->complaint_status == 4 ? 'selected' : '' }}>पूर्ण
                                </option>
                                <option value="5" {{ $complaint->complaint_status == 5 ? 'selected' : '' }}>रद्द
                                </option>
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">पूर्व निर्धारित उत्तर चुनें:</label>
                            <select name="selected_reply" id="selected_reply" class="form-control">
                                <option value="">--चयन करें--</option>
                                @foreach ($replyOptions as $option)
                                    <option value="{{ $option->reply_id }}">{{ $option->reply }}</option>
                                @endforeach
                                <option value="0">अन्य</option>
                            </select>
                        </div>

                        <div class="col-md-3" id="forwarded_to_field">
                            <label class="form-label">अधिकारी चुनें (आगे भेजे)<span class="tx-danger"
                                    style="color: red;">*</span></label>
                            <select name="forwarded_to" id="managers" class="form-control" required>
                                <option value="">--चयन करें--</option>
                                @foreach ($managers as $manager)
                                    <option value="{{ $manager->admin_id }}">{{ $manager->admin_name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">संपर्क स्थिति:</label>
                            <select name="contact_status" class="form-control">
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
                        </div>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-12">
                            <label class="form-label">समस्या/समाधान में प्रगति<span class="tx-danger"
                                    style="color: red;">*</span></label>
                            <textarea name="cmp_reply" placeholder="हिंदी में टाइप करने के लिए कृपया हिंदी कीबोर्ड चालू करें"
                                class="form-control" rows="6" required></textarea>
                        </div>
                    </div>

                    <div class="row mt-3">
                        <div class="col-md-3">
                            <label class="form-label">पूर्व स्थिति की तस्वीर</label>
                            <input type="file" name="cb_photo[]" class="form-control" multiple accept="image/*">
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">बाद की तस्वीर</label>
                            <input type="file" name="ca_photo[]" class="form-control" multiple accept="image/*">
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">यूट्यूब लिंक</label>
                            <input type="url" name="c_video" class="form-control">
                        </div>
                    </div>


                    {{-- <div class="col-md-2">
                        <label class="form-label">पूर्व स्थिति की तस्वीर</label>
                        <input type="file" name="cb_photo[]" class="form-control" multiple accept="image/*">
                        <label class="form-label mt-3">बाद की तस्वीर</label>
                        <input type="file" name="ca_photo[]" class="form-control" multiple accept="image/*">
                    </div>

                    <div class="col-md-2">
                        <label class="form-label">यूट्यूब लिंक</label>
                        <input type="url" name="c_video" class="form-control">
                        <label class="form-label mt-3">शिकायत की स्थिति: <span class="tx-danger"
                                style="font-size: 11px; color: red;">*</span></label>
                        <select name="cmp_status" class="form-control" required>
                            <option value="">--चुने--</option>
                            <option value="1" {{ $complaint->complaint_status == 1 ? 'selected' : '' }}>शिकायत
                                दर्ज
                            </option>
                            <option value="2" {{ $complaint->complaint_status == 2 ? 'selected' : '' }}>प्रक्रिया
                                में
                            </option>
                            <option value="3" {{ $complaint->complaint_status == 3 ? 'selected' : '' }}>स्थगित
                            </option>
                            <option value="4" {{ $complaint->complaint_status == 4 ? 'selected' : '' }}>पूर्ण
                            </option>
                            <option value="5" {{ $complaint->complaint_status == 5 ? 'selected' : '' }}>रद्द
                            </option>
                        </select>
                    </div> --}}

                    <div class="col-12 mt-3">
                        <button type="submit" class="btn btn-primary">शिकायत दर्ज करें</button>
                    </div>

                </form>
            </div>
        </div>
    </div>


    @push('scripts')
        <script>
            $(document).on('click', '.view-details-btn', function() {
                const reply = $(this).data('reply') || '—';
                const contact = $(this).data('contact') || '—';
                const replyDate = $(this).data('reply-date') || '—';
                const status = $(this).data('status') || '—';
                const admin = $(this).data('admin') || '—';
                 const predefinedRaw = $(this).data('predefined');
                const predefined = predefinedRaw === 0 ? 'अन्य' : (predefinedRaw || '—');

                const cbPhoto = $(this).data('cb-photo');
                const caPhoto = $(this).data('ca-photo');
                const video = $(this).data('video');

                $('#modal-reply').text(reply);
                $('#modal-status').text(status);
                $('#modal-date').text(replyDate);
                $('#modal-admin').text(admin);
                $('#modal-predefined').text(predefined);
                 $('#modal-contact').text(contact);

                cbPhoto ? $('#cb-photo-link').attr('href', cbPhoto).show() : $('#cb-photo-link').hide();
                caPhoto ? $('#ca-photo-link').attr('href', caPhoto).show() : $('#ca-photo-link').hide();
                video ? $('#video-link').attr('href', video).show() : $('#video-link').hide();
            });

            $(document).ready(function() {
                $('#replyForm').on('submit', function(e) {
                    e.preventDefault();

                    $("#loader-wrapper").show();
                    var form = $(this)[0];
                    var formData = new FormData(form);

                    $.ajax({
                        url: $(form).attr('action'),
                        method: 'POST',
                        data: formData,
                        processData: false,
                        contentType: false,
                        success: function(response) {
                            $("#loader-wrapper").hide();
                            $('#success-message').text(response.message);

                            $('#success-alert').removeClass('d-none');
                            window.scrollTo({
                                top: 0,
                                behavior: 'smooth'
                            });

                            setTimeout(function() {
                                location.reload(); 
                            }, 500);
                            
                            $('#replyForm')[0].reset();

                            setTimeout(function() {
                                $('#success-alert').addClass('d-none');
                            }, 5000);
                        },
                        error: function(xhr) {
                            $("#loader-wrapper").hide();
                            alert('अपडेट करते समय एक त्रुटि हुई।');
                        }
                    });
                });
            });


            document.addEventListener('DOMContentLoaded', function() {
                const statusSelect = document.getElementById('cmp_status');
                const forwardedSelect = document.getElementById('managers');

                function toggleForwardedField() {
                    const selectedValue = parseInt(statusSelect.value);

                    if (selectedValue === 4 || selectedValue === 5) {
                        forwardedSelect.disabled = true;
                        forwardedSelect.style.backgroundColor = '#e1e2e6'; 
                        forwardedSelect.removeAttribute('required');
                    } else {
                        forwardedSelect.disabled = false;
                        forwardedSelect.style.backgroundColor = ''; 
                        forwardedSelect.setAttribute('required', 'required');
                    }
                }

                toggleForwardedField();

                statusSelect.addEventListener('change', toggleForwardedField);
            });
        </script>
    @endpush
@endsection
