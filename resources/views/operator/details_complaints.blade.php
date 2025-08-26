@php
    if (in_array($complaint->complaint_type, ['शुभ सुचना', 'अशुभ सुचना'])) {
        $pageTitle = 'सूचनाएँ देखे';
        $breadcrumbs = [
            'कार्यालय' => '#',
            'सूचनाएँ देखे' => '#',
        ];
    } else {
        $pageTitle = 'समस्याएँ देखें';
        $breadcrumbs = [
            'कार्यालय' => '#',
            'समस्याएँ देखें' => '#',
        ];
    }
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
                <span style="color: gray;"> स्थिति: {!! $complaint->statusText() !!}</span>
            </div>
            <div class="card-body row g-3">
                @php
                    if (in_array($complaint->complaint_type, ['शुभ सुचना', 'अशुभ सुचना'])) {
                        $nameLabel = 'सूचनाकर्ता का नाम';
                        $mobileLabel = 'सूचनाकर्ता का मोबाइल';
                    } elseif ($complaint->complaint_type === 'विकास') {
                        $nameLabel = 'मांगकर्ता का नाम';
                        $mobileLabel = 'मांगकर्ता का मोबाइल';
                    } else {
                        $nameLabel = 'शिकायतकर्ता का नाम';
                        $mobileLabel = 'शिकायतकर्ता का मोबाइल';
                    }

                    $fields = [
                        $nameLabel => $complaint->name,
                        $mobileLabel => $complaint->mobile_number,
                        'पिता का नाम' => $complaint->father_name,
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
                        'रेफरेंस नाम' => $complaint->reference_name,
                        'लिंग' => $complaint->registration->gender ?? '',
                        'धर्म' => $complaint->registration->religion ?? '',
                        'वर्ग/श्रेणी' => $complaint->registration->caste ?? '',
                        'जाति' => $complaint->registration->jati ?? '',
                        'शिक्षा' => $complaint->registration->education ?? '',
                        'व्यवसाय' => $complaint->registration->business ?? '',
                        'पद' => $complaint->registration->position ?? '',
                        'दिनांक' => $complaint->posted_date ?? '',
                    ];
                @endphp

                @foreach ($fields as $label => $value)
                    <div class="col-md-3 d-flex align-items-center mb-2">
                        <label class="form-label me-2 mr-2 mb-0" style="white-space: nowrap;">{{ $label }}:</label>
                        <input type="text" class="form-control" value="{{ $value }}" disabled>
                    </div>
                @endforeach

                <input type="hidden" id="complaint_type" value="{{ $complaint->complaint_type }}">


                <div class="col-md-3 d-flex align-items-start mt-3">
                    <label class="form-label mr-2 me-2" style="white-space: nowrap;">पूरा पता:</label>
                    <textarea class="form-control" rows="3" style="flex: 1;" disabled>{{ $complaint->address }}</textarea>
                </div>


                <div class="col-md-3 d-flex align-items-start mt-3" style="margin-top: 8px;">
                    <label class="form-label mr-2 me-2" style="white-space: nowrap;">विषय</label>
                    <textarea class="form-control" rows="3" disabled>{{ $complaint->issue_title }}</textarea>
                </div>

                <div class="col-md-6 d-flex align-items-start mt-3" style="margin-top: 8px;">
                    <label class="form-label mr-2 me-2" style="white-space: nowrap;">विवरण</label>
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
                            @if (!in_array($complaint->complaint_type, ['शुभ सुचना', 'अशुभ सुचना']))
                                <th>निर्धारित उत्तर</th>
                            @endif
                            <th>स्थिति</th>
                            <th>दिनांक</th>
                            <th>द्वारा भेजा गया</th>
                            <th>को भेजा गया</th>
                            @if (!in_array($complaint->complaint_type, ['शुभ सुचना', 'अशुभ सुचना']))
                                <th>रीव्यू दिनांक</th>
                                <th>महत्त्व स्तर</th>
                            @endif
                            <th>विवरण देखें</th>
                        </tr>
                    </thead>
                    <tbody style="color: #000;">
                        @forelse ($complaint->replies as $reply)
                            <tr>
                                @if (!in_array($complaint->complaint_type, ['शुभ सुचना', 'अशुभ सुचना']))
                                    <td>{{ $reply->selected_reply === 0 ? 'अन्य' : $reply->predefinedReply->reply ?? '-' }}
                                    </td>
                                @endif
                                <td>{!! $reply->statusTextPlain() !!}</td>
                                <td>{{ $reply->reply_date ? \Carbon\Carbon::parse($reply->reply_date)->format('d-m-Y h:i A') : 'N/A' }}
                                </td>
                                <td>{{ $reply->replyfrom?->admin_name ?? '' }}</td>
                                <td>{{ $reply->forwardedToManager?->admin_name ?? '' }}</td>
                                @if (!in_array($complaint->complaint_type, ['शुभ सुचना', 'अशुभ सुचना']))
                                    <td>{{ $reply->review_date ?? '' }}</td>
                                    <td>{{ $reply->importance ?? '' }}</td>
                                @endif

                                <td>
                                    <button type="button" class="btn btn-sm btn-info view-details-btn" data-toggle="modal"
                                        data-target="#detailsModal" data-reply="{{ $reply->complaint_reply }}"
                                        data-contact="{{ $reply->contact_status }}"
                                        data-details="{{ $reply->contact_update }}"
                                        data-review="{{ $reply->review_date }}" data-importance="{{ $reply->importance }}"
                                        data-critical="{{ $reply->criticality }}"
                                        data-reply_from="{{ $reply->replyfrom?->admin_name ?? '' }}"
                                        data-reply-date="{{ \Carbon\Carbon::parse($reply->reply_date)->format('d-m-Y h:i A') }}"
                                        data-admin="{{ $reply->forwardedToManager?->admin_name ?? '' }}"
                                        data-status-html="{!! htmlspecialchars($reply->statusText(), ENT_QUOTES, 'UTF-8') !!}"
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
                                <td colspan="{{ in_array($complaint->complaint_type, ['शुभ सुचना', 'अशुभ सुचना']) ? 5 : 8 }}"
                                    class="text-center">
                                    कोई जवाब उपलब्ध नहीं है।
                                </td>
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
                        <div id="modal-status"></div>
                    </div>

                    <div class="modal-body">
                        <div class="border p-2 rounded mb-4 bg-light">
                            <h5>विवरण</h5>
                            <p id="modal-reply">—</p>
                        </div>

                        <div class="border p-2 rounded mb-3">
                            <h5>उत्तर विवरण</h5>
                            <div class="table-responsive">
                                <table style="color: black" class="table table-bordered text-center align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th>तारीख</th>
                                            <th>द्वारा भेजा गया</th>
                                            <th>को भेजा गया</th>
                                            <th>संपर्क स्थिति</th>
                                            <th>संपर्क विवरण</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td id="modal-date">—</td>
                                            <td id="modal-reply-from">—</td>
                                            <td id="modal-admin">—</td>
                                            <td id="modal-contact">—</td>
                                            <td id="modal-details">—</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            @if (!in_array($complaint->complaint_type, ['शुभ सुचना', 'अशुभ सुचना']))
                                <div class="table-responsive">
                                    <table style="color: black" class="table table-bordered text-center align-middle">
                                        <thead class="table-light">
                                            <tr>
                                                <th>पूर्वनिर्धारित उत्तर</th>
                                                <th>रीव्यू दिनांक</th>
                                                <th>महत्त्व स्तर</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td id="modal-predefined">—</td>
                                                <td id="modal-review">—</td>
                                                <td id="modal-importance">—</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>

                                <div class="border p-2 rounded mb-3 bg-light">
                                    <h5 class="mb-3 text-center">अटैचमेंट्स</h5>
                                    <div class="d-flex flex-wrap justify-content-center gap-2">
                                        <div class="card p-3 mr-2 border-0 shadow rounded"
                                            style="background-color: #ffffff;">
                                            <div class="text-center" style="width: 200px;">
                                                <div>पूर्व स्थिति की तस्वीर</div>
                                                <a href="#" id="cb-photo-link"
                                                    class="btn btn-sm btn-outline-primary mt-1" target="_blank">खोलें</a>
                                            </div>
                                        </div>

                                        <div class="card p-3 mr-2 border-0 shadow rounded"
                                            style="background-color: #ffffff;">
                                            <div class="text-center" style="width: 200px;">
                                                <div>बाद की तस्वीर</div>
                                                <a href="#" id="ca-photo-link"
                                                    class="btn btn-sm btn-outline-primary mt-1" target="_blank">खोलें</a>
                                            </div>
                                        </div>

                                        <div class="card p-3 mr-2 border-0 shadow rounded"
                                            style="background-color: #ffffff;">
                                            <div class="text-center" style="width: 200px;">
                                                <div>वीडियो लिंक</div>
                                                <a href="#" id="video-link"
                                                    class="btn btn-sm btn-outline-primary mt-1" target="_blank">खोलें</a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- <div class="card">
            <div class="card-header" style="color: #000">Reply to {{ $complaint->complaint_number }}</div>
            <div class="card-body">

                @if ($disableReply)
                @if (in_array($complaint->complaint_type, ['शुभ सुचना', 'अशुभ सुचना']))
                    <div class="alert alert-warning">
                        इस सुचना का अंतिम उत्तर प्राप्त हो चुका है। आप अब कोई नया उत्तर नहीं दे सकते।
                    </div>

                     @else

                     <div class="alert alert-warning">
                        इस शिकायत का अंतिम उत्तर प्राप्त हो चुका है। आप अब कोई नया उत्तर नहीं दे सकते।
                    </div>
                     @endif
                @endif

                <form id="replyForm" method="POST"
                    action="{{ route('operator_complaint.reply', $complaint->complaint_id) }}"
                    enctype="multipart/form-data" @if ($disableReply) style="pointer-events: none; opacity: 0.6;" @endif>
                    @csrf

                    <div class="row mb-3">
                        <div class="col-md-2">
                            <label class="form-label ">
                                @if (in_array($complaint->complaint_type, ['शुभ सुचना', 'अशुभ सुचना']))
                                    सूचना की स्थिति:
                                @else
                                    शिकायत की स्थिति:
                                @endif
                                <span class="tx-danger" style="color: red;">*</span>
                            </label>
                            <select name="cmp_status" id="cmp_status" class="form-control" required @if ($disableReply) disabled @endif>
                                <option value="">--चुने--</option>
                            </select>
                        </div>

                        <div class="col-md-2 toggle-field">
                            <label class="form-label">पूर्व निर्धारित उत्तर चुनें:</label>
                            <select name="selected_reply" id="selected_reply" class="form-control" @if ($disableReply) disabled @endif>
                                <option value="">--चयन करें--</option>
                                @foreach ($replyOptions as $option)
                                    <option value="{{ $option->reply_id }}">{{ $option->reply }}</option>
                                @endforeach
                                <option value="0">अन्य</option>
                            </select>
                        </div>

                        <div class="col-md-2" id="forwarded_to_field">
                            <label class="form-label">अधिकारी चुनें (आगे भेजे)<span class="tx-danger"
                                    style="color: red;">*</span></label>
                            <select name="forwarded_to" id="forwarded_to" class="form-control" required @if ($disableReply) disabled @endif>
                                <option value="">--चयन करें--</option>
                                @foreach ($managers as $manager)
                                    <option value="{{ $manager->admin_id }}">{{ $manager->admin_name }}</option>
                                @endforeach
                            </select>
                        </div>


                        <div class="col-md-2 toggle-field">
                            <label for="review_date">रीव्यू दिनांक</label>
                            <input type="date" class="form-control" name="review_date" @if ($disableReply) disabled @endif>
                        </div>

                        <div class="col-md-2 toggle-field">
                            <label for="importance form-label">महत्त्व स्तर:</label>
                            <select name="importance" class="form-control" @if ($disableReply) disabled @endif>
                                <option value="">--चयन करें--</option>
                                <option value="उच्च">उच्च</option>
                                <option value="मध्यम">मध्यम</option>
                                <option value="कम">कम</option>
                            </select>
                        </div>

                        


                    </div>

                    <div class="row g-3">
                        <div class="col-md-12">
                            <label class="form-label">विवरण<span class="tx-danger" style="color: red;">*</span></label>
                            <textarea name="cmp_reply" placeholder="हिंदी में टाइप करने के लिए कृपया हिंदी कीबोर्ड चालू करें"
                                class="form-control" rows="6" required @if ($disableReply) disabled @endif></textarea>
                        </div>
                    </div>

                    <div class="row mt-3">
                        <div class="col-md-3 toggle-field">
                            <label class="form-label">पूर्व स्थिति की तस्वीर</label>
                            <input type="file" name="cb_photo[]" class="form-control" multiple accept="image/*" @if ($disableReply) disabled @endif>
                        </div>

                        <div class="col-md-3 toggle-field">
                            <label class="form-label">बाद की तस्वीर</label>
                            <input type="file" name="ca_photo[]" class="form-control" multiple accept="image/*" @if ($disableReply) disabled @endif>
                        </div>

                        <div class="col-md-3 toggle-field">
                            <label class="form-label">यूट्यूब लिंक</label>
                            <input type="url" name="c_video" class="form-control" @if ($disableReply) disabled @endif>
                        </div>
                    </div>

                    <div class="col-12 mt-3">
                        <button type="submit" class="btn btn-primary" @if ($disableReply) disabled @endif>फीडबैक दर्ज करें</button>
                    </div>

                </form>
            </div>
        </div> --}}
    </div>



    @push('scripts')
        <script>
            $(document).on('click', '.view-details-btn', function() {
                const reply = $(this).data('reply') || '—';
                const reply_from = $(this).data('reply_from') || '—';
                const contact = $(this).data('contact') || '—';
                const replyDate = $(this).data('reply-date') || '—';
                const statusHtml = $(this).data('status-html') || '—';
                const admin = $(this).data('admin') || '—';
                const predefinedRaw = $(this).data('predefined');
                const predefined = predefinedRaw === 0 ? 'अन्य' : (predefinedRaw || '—');

                const cbPhoto = $(this).data('cb-photo');
                const caPhoto = $(this).data('ca-photo');
                const video = $(this).data('video');
                const review = $(this).data('review');
                const importance = $(this).data('importance');
                const details = $(this).data('details') || '—';

                $('#modal-reply').text(reply);
                $('#modal-status').html(statusHtml);
                $('#modal-date').text(replyDate);
                $('#modal-admin').text(admin);
                $('#modal-predefined').text(predefined);
                $('#modal-contact').text(contact);
                $('#modal-review').text(review);
                $('#modal-importance').text(importance);
                $('#modal-details').text(details);
                $('#modal-reply-from').text(reply_from);

                cbPhoto ? $('#cb-photo-link').attr('href', cbPhoto).show() : $('#cb-photo-link').hide();
                caPhoto ? $('#ca-photo-link').attr('href', caPhoto).show() : $('#ca-photo-link').hide();
                video ? $('#video-link').attr('href', video).show() : $('#video-link').hide();
            });

            // $(document).ready(function() {
            //     $('#replyForm').on('submit', function(e) {
            //         e.preventDefault();

            //         $("#loader-wrapper").show();
            //         var form = $(this)[0];
            //         var formData = new FormData(form);

            //         $.ajax({
            //             url: $(form).attr('action'),
            //             method: 'POST',
            //             data: formData,
            //             processData: false,
            //             contentType: false,
            //             success: function(response) {
            //                 $("#loader-wrapper").hide();
            //                 $('#success-message').text(response.message);

            //                 $('#success-alert').removeClass('d-none');
            //                 window.scrollTo({
            //                     top: 0,
            //                     behavior: 'smooth'
            //                 });

            //                 setTimeout(function() {
            //                     location.reload();
            //                 }, 500);
            //                 $('#replyForm')[0].reset();

            //                 setTimeout(function() {
            //                     $('#success-alert').addClass('d-none');
            //                 }, 5000);
            //             },
            //             error: function(xhr) {
            //                 $("#loader-wrapper").hide();
            //                 alert('अपडेट करते समय एक त्रुटि हुई।');
            //             }
            //         });
            //     });
            // });


            // document.addEventListener('DOMContentLoaded', function() {
            //     const statusSelect = document.getElementById('cmp_status');
            //     const forwardedSelect = document.getElementById('forwarded_to');
            // const replyForm = document.getElementById('replyForm');

            //     function toggleForwardedField() {
            //         const selectedValue = parseInt(statusSelect.value);

            //         if (selectedValue === 4 || selectedValue === 5 || selectedValue === 13 || selectedValue === 14 || selectedValue === 15 || selectedValue === 16 || selectedValue === 17 || selectedValue === 18) {
            //             forwardedSelect.disabled = true;
            //             forwardedSelect.style.backgroundColor = '#e1e2e6';
            //             forwardedSelect.removeAttribute('required');
            //         } else {
            //             forwardedSelect.disabled = false;
            //             forwardedSelect.style.backgroundColor = '';
            //             forwardedSelect.setAttribute('required', 'required');
            //         }
            //     }

            //     toggleForwardedField();

            //     statusSelect.addEventListener('change', toggleForwardedField);

            // var disableReply = "{{ $disableReply ? 'true' : 'false' }}" === 'true';

            // if (disableReply && replyForm) {
            //     Array.from(replyForm.elements).forEach(el => el.disabled = true);
            // }
            // });

            // document.addEventListener("DOMContentLoaded", function() {
            //     const complaintType = document.getElementById("complaint_type").value;
            //     const cmpStatus = document.getElementById("cmp_status");

            //     const lastStatus = "{{ $complaint->complaint_status ?? '' }}";

            //     const defaultOptions = [{
            //             value: "1",
            //             text: "शिकायत दर्ज"
            //         },
            //         {
            //             value: "2",
            //             text: "प्रक्रिया में"
            //         },
            //         {
            //             value: "3",
            //             text: "स्थगित"
            //         },
            //         {
            //             value: "4",
            //             text: "पूर्ण"
            //         },
            //         {
            //             value: "5",
            //             text: "रद्द"
            //         }
            //     ];

            //     const suchnaOptions = [{
            //             value: "11",
            //             text: "सूचना प्राप्त"
            //         },
            //         {
            //             value: "12",
            //             text: "फॉरवर्ड किया"
            //         },
            //         {
            //             value: "13",
            //             text: "सम्मिलित हुए"
            //         },
            //         {
            //             value: "14",
            //             text: "सम्मिलित नहीं हुए"
            //         },
            //         {
            //             value: "15",
            //             text: "फोन पर संपर्क किया"
            //         },
            //         {
            //             value: "16",
            //             text: "ईमेल पर संपर्क किया"
            //         },
            //         {
            //             value: "17",
            //             text: "व्हाट्सएप पर संपर्क किया"
            //         },
            //         {
            //             value: "18",
            //             text: "रद्द"
            //         }
            //     ];

            //     cmpStatus.innerHTML = '<option value="">--चुने--</option>';

            //     const optionsToLoad = (complaintType === "शुभ सुचना" || complaintType === "अशुभ सुचना") ?
            //         suchnaOptions :
            //         defaultOptions;

            //     optionsToLoad.forEach(opt => {
            //         const option = document.createElement("option");
            //         option.value = opt.value;
            //         option.textContent = opt.text;

            //         if (opt.value == lastStatus) {
            //             option.selected = true;
            //         }

            //         cmpStatus.appendChild(option);
            //     });

            //     const toggleFields = document.querySelectorAll(".toggle-field");
            //     if (complaintType === "शुभ सुचना" || complaintType === "अशुभ सुचना") {
            //         toggleFields.forEach(el => el.style.display = "none");
            //     } else {
            //         toggleFields.forEach(el => el.style.display = "block");
            //     }
            // });
        </script>
    @endpush
@endsection
