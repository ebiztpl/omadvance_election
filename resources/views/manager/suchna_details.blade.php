@php
    $pageTitle = 'सूचनाएँ देखे';
    $breadcrumbs = [
        'मैनेजर' => '#',
        'सूचनाएँ देखे' => '#',
    ];
@endphp

@extends('layouts.app')
@section('title', 'View Suchnas')

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
                <span style="color: gray;">सूचना स्थिति: {!! $complaint->statusText() !!}</span>
            </div>
            <div class="col-md-12 col-sm-12 col-xs-12">
                <form id="updateComplaintForm" method="POST" enctype="multipart/form-data"
                    action="{{ route('suchna.update', $complaint->complaint_id) }}">
                    @csrf

                    {{-- Type Selection --}}
                    <div class="form-group row justify-content-center">
                        <div class="col-md-12 d-flex justify-content-center" style="padding-top:15px;">
                            <div id="type_row" class="d-flex justify-content-center flex-wrap">
                                @php
                                    $types = [
                                        'शुभ सुचना' => ['text' => 'सूचनाकर्ता का नाम', 'heading' => 'शुभ सूचना'],
                                        'अशुभ सुचना' => ['text' => 'सूचनाकर्ता का नाम', 'heading' => 'अशुभ सूचना'],
                                    ];
                                @endphp
                                @foreach ($types as $type => $data)
                                    <label class="btn btn-success text-white m-1">
                                        <input type="radio" name="type" value="{{ $type }}" class="check"
                                            data-text="{{ $data['text'] }}" data-legend="{{ $data['heading'] }}"
                                            {{ $complaint->complaint_type == $type ? 'checked' : '' }}
                                            style="width: 25px; height: 25px;" required>
                                        <br>{{ $type }}
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <div id="form_container" style="color: #000;">
                        <fieldset class="scheduler-border mb-3">
                            <div class="form-group row">
                                <div class="col-md-4 mb-3 d-flex align-items-center">
                                    <label class="me-2 mr-2 mb-0" style="white-space: nowrap;">
                                        <span class="data-text">नाम</span> <span class="error">*</span>
                                    </label>
                                    <div class="flex-grow-1">
                                        <input type="text" class="form-control" name="txtname" id="name"
                                            value="{{ old('txtname', $complaint->name) }}" required>
                                    </div>
                                </div>

                                <div class="col-md-4 mb-3 d-flex align-items-center">
                                    <label class="me-2 mr-2 mb-0" style="white-space: nowrap;">
                                        पिता का नाम <span class="error">*</span>
                                    </label>
                                    <div class="flex-grow-1">
                                        <input type="text" class="form-control" name="father_name" id="father_name"
                                            value="{{ old('father_name', $complaint->father_name) }}" required>
                                    </div>
                                </div>

                                <div class="col-md-4 mb-3 d-flex align-items-center">
                                    <label class="me-2 mr-2 mb-0" style="white-space: nowrap;">
                                        मोबाइल <span class="error">*</span>
                                    </label>
                                    <div class="flex-grow-1">
                                        <input type="text" class="form-control" name="mobile"
                                            value="{{ old('mobile', $complaint->mobile_number) }}">
                                    </div>
                                </div>

                                <div class="col-md-4 mb-3 d-flex align-items-center">
                                    <label class="me-2 mr-2 mb-0" style="white-space: nowrap;">संभाग का नाम <span
                                            class="error">*</span></label>
                                    <select class="form-control bg-light text-muted" name="division_id" id="division_id"
                                        disabled required>
                                        <option value="{{ $complaint->division_id }}">
                                            {{ $complaint->division->division_name ?? 'Current' }}
                                        </option>
                                    </select>
                                    <input type="hidden" name="division_id" value="2">
                                </div>

                                <div class="col-md-4 mb-3 d-flex align-items-center">
                                    <label class="me-2 mr-2 mb-0" style="white-space: nowrap;">जिले का नाम <span
                                            class="error">*</span></label>
                                    <select class="form-control bg-light text-muted" name="txtdistrict_name"
                                        id="district_name" disabled required>
                                        <option value="{{ $complaint->district_id }}">
                                            {{ $complaint->district->district_name ?? 'Current' }}
                                        </option>
                                    </select>
                                    <input type="hidden" name="txtdistrict_name" value="11">
                                </div>

                                <div class="col-md-4 mb-3 d-flex align-items-center">
                                    <label class="me-2 mr-2 mb-0" style="white-space: nowrap;">विधानसभा <span
                                            class="error">*</span></label>
                                    <select name="txtvidhansabha" class="form-control bg-light text-muted"
                                        id="txtvidhansabha" disabled required>
                                        <option value="{{ $complaint->vidhansabha_id }}">
                                            {{ $complaint->vidhansabha->vidhansabha ?? 'Current' }}
                                        </option>
                                    </select>
                                    <input type="hidden" name="txtvidhansabha" value="49">
                                </div>


                                <div class="col-md-4 mb-3 d-flex align-items-center">
                                    <label class="me-2 mr-2 mb-0" style="white-space: nowrap;">नगर/मंडल <span
                                            class="error">*</span></label>
                                    <select name="txtgram" class="form-control" id="txtgram" required>
                                        <option value="">--चुने--</option>
                                        @foreach ($nagars as $nagar)
                                            <option value="{{ $nagar->nagar_id }}"
                                                {{ $nagar->nagar_id == $complaint->gram_id ? 'selected' : '' }}>
                                                {{ $nagar->nagar_name }} - {{ $nagar->mandal->mandal_name ?? '' }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>


                                <div class="col-md-4 mb-3 d-flex align-items-center">
                                    <label class="me-2 mr-2 mb-0" style="white-space: nowrap;">मतदान
                                        केंद्र/ग्राम/वार्ड <span class="error">*</span></label>
                                    <select name="txtpolling" class="form-control" id="txtpolling" required>
                                        <option value="{{ $complaint->polling_id }}">
                                            {{ $complaint->polling->polling_name ?? '' }}
                                            ({{ $complaint->polling->polling_no ?? '' }}) -
                                            {{ $complaint->polling->area->area_name ?? '' }}
                                        </option>
                                    </select>
                                    <input type="hidden" id="area_id" name="area_id"
                                        value="{{ $complaint->polling->area->area_id ?? '' }}">
                                </div>


                                <div class="col-md-4 mb-3 d-flex align-items-center">
                                    <label class="me-2 mr-2 mb-0" style="white-space: nowrap;">
                                        रेफरेंस नाम
                                    </label>
                                    <div class="flex-grow-1">
                                        <input type="text" class="form-control" name="reference"
                                            value="{{ old('reference', $complaint->reference_name) }}">
                                    </div>
                                </div>

                                <div class="col-md-4 mb-3 d-flex align-items-center">
                                    <label class="me-2 mr-2 mb-0" style="white-space: nowrap;">
                                        मतदाता पहचान <span class="error">*</span>
                                    </label>

                                    <div class="d-flex flex-column w-100">
                                        <input type="text" class="form-control" name="voter" id="voter_id_input"
                                            value="{{ old('voter', $complaint->voter_id) }}" required>
                                        <small id="voter-error" class="text-danger mt-1" style="display:none;"></small>
                                    </div>
                                </div>

                                <div class="col-md-4 mb-3 d-flex align-items-center">
                                    <label class="me-2 mr-2 mb-0" style="white-space: nowrap;">
                                        @if ($complaint->type == 1)
                                            कमांडर द्वारा भेजा गया वीडियो
                                        @else
                                            कार्यालय द्वारा भेजी गई फ़ाइल
                                        @endif
                                    </label>

                                    @if ($complaint->issue_attachment)
                                        <a href="{{ asset('assets/upload/complaints/' . $complaint->issue_attachment) }}"
                                            class="btn btn-sm btn-info" target="_blank">अटैचमेंट खोलें</a>
                                    @else
                                        <button class="btn btn-sm btn-secondary" disabled>कोई अटैचमेंट नहीं है</button>
                                    @endif
                                </div>
                            </div>

                            {{-- Detail Section --}}
                            <fieldset class="scheduler-border fieldset-bordered">
                                <legend class="scheduler-border dynamic-legend">विवरण</legend>

                                <div class="form-group row date_row">
                                    <div class="col-md-3 d-flex align-items-center">
                                        <label for="from_date" class="me-2 mr-2 mb-0" style="white-space: nowrap;">सूचना
                                            दिनांक <span class="error">*</span></label>
                                        <input type="date" class="form-control" name="from_date" required
                                            value="{{ old('from_date', $complaint->news_date) }}">
                                    </div>

                                    <div class="col-md-3 d-flex align-items-center">
                                        <label for="program_date" class="me-2 mr-2 mb-0"
                                            style="white-space: nowrap;">कार्यक्रम दिनांक <span
                                                class="error">*</span></label>
                                        <input type="date" class="form-control" name="program_date" required
                                            value="{{ old('program_date', $complaint->program_date) }}">
                                    </div>

                                    <div class="col-md-3 d-flex align-items-center">
                                        <label for="to_date" class="me-2 mr-2 mb-0"
                                            style="white-space: nowrap;">कार्यक्रम समय</label>
                                        <input type="time" class="form-control" name="to_date"
                                            value="{{ old('to_date', $complaint->news_time) }}">
                                    </div>

                                    <div class="col-md-3 d-flex align-items-center">
                                        <label for="subject-select" class="me-2 mr-2 mb-0"
                                            style="white-space: nowrap;">विषय
                                            <span class="error">*</span></label>
                                        <select name="CharCounter" id="issue_title" class="form-control"
                                            data-selected="{{ $complaint->issue_title }}" required>
                                            <option value="">--चुने--</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="form-group row">
                                    <div class="col-md-12 mb-3">
                                        <label>विवरण</label>
                                        <textarea class="form-control" name="NameText" rows="5"
                                            placeholder="हिंदी में टाइप करने के लिए कृपया हिंदी कीबोर्ड चालू करें" maxlength="2000" required>{{ old('NameText', $complaint->issue_description) }}</textarea>
                                    </div>
                                </div>
                            </fieldset>
                        </fieldset>

                        <input class="btn btn-primary" type="submit" value="अपडेट करें"
                            style="background-color:blue; color:#fff; width:100%; height:50px;">
                    </div>
                </form>
            </div>
        </div>

        <div class="card container" style="color: #000;">
            <h5 class="my-3">Reply History for {{ $complaint->complaint_number }}</h5>

            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead class="thead-dark">
                        <tr>
                            <th>स्थिति</th>
                            <th>दिनांक</th>
                            <th>द्वारा भेजा गया</th>
                            <th>को भेजा गया</th>
                            <th>विवरण देखें</th>
                        </tr>
                    </thead>
                    <tbody style="color: #000;">
                        @forelse ($complaint->replies as $reply)
                            <tr>
                                <td>{!! $reply->statusTextPlain() !!}</td>
                                <td>{{ $reply->reply_date ? \Carbon\Carbon::parse($reply->reply_date)->format('d-m-Y h:i A') : 'N/A' }}
                                </td>
                                <td>{{ $reply->replyfrom?->admin_name ?? '' }}</td>
                                <td> {{ $reply->forwardedToManager?->admin_name ?? '' }}</td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-info view-details-btn"
                                        data-toggle="modal" data-target="#detailsModal"
                                        data-reply="{{ $reply->complaint_reply }}"
                                        data-contact="{{ $reply->contact_status }}"
                                        data-details="{{ $reply->contact_update }}"
                                        data-review="{{ $reply->review_date }}"
                                        data-importance="{{ $reply->importance }}"
                                        data-critical="{{ $reply->criticality }}"
                                        data-reply_from="{{ $reply->replyfrom?->admin_name ?? '' }}"
                                        data-reply-date="{{ \Carbon\Carbon::parse($reply->reply_date)->format('d-m-Y h:i A') }}"
                                        data-admin="{{ $reply->forwardedToManager?->admin_name ?? '' }}"
                                        data-status-html="{!! htmlspecialchars($reply->statusText(), ENT_QUOTES, 'UTF-8') !!}"
                                        data-predefined="{{ $reply->predefinedReply->reply ?? '-' }}"
                                        data-cb-photo="{{ $reply->cb_photo ? asset($reply->cb_photo) : '' }}"
                                        data-ca-photo="{{ $reply->ca_photo ? asset($reply->ca_photo) : '' }}"
                                        data-video="{{ $reply->c_video ? asset($reply->c_video) : '' }}">
                                        विवरण
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center">कोई जवाब उपलब्ध नहीं है।</td>
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
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header" style="color: #000">Reply to {{ $complaint->complaint_number }}</div>
            <div class="card-body">

                @if ($disableReply)
                    <div class="alert alert-warning">
                        इस सूचना का अंतिम उत्तर प्राप्त हो चुका है। आप अब कोई नया उत्तर नहीं दे सकते।
                    </div>
                @endif

                <form id="replyForm" method="POST"
                    action="{{ route('complaint_reply.reply', $complaint->complaint_id) }}" enctype="multipart/form-data"
                    @if ($disableReply) style="pointer-events: none; opacity: 0.6;" @endif>
                    @csrf

                    <div class="row mb-3">
                        <div class="col-md-2">
                            <label class="form-label ">सूचना की स्थिति: <span class="tx-danger"
                                    style="color: red;">*</span></label>
                            <select name="cmp_status" id="cmp_status" class="form-control" required
                                @if ($disableReply) disabled @endif>
                                <option value="">--चुने--</option>
                                <option value="11" {{ $complaint->complaint_status == 11 ? 'selected' : '' }}>
                                    सूचना प्राप्त
                                </option>
                                <option value="12" {{ $complaint->complaint_status == 12 ? 'selected' : '' }}>
                                    फॉरवर्ड किया
                                </option>
                                <option value="13" {{ $complaint->complaint_status == 13 ? 'selected' : '' }}>
                                    सम्मिलित हुए
                                </option>
                                <option value="14" {{ $complaint->complaint_status == 14 ? 'selected' : '' }}>सम्मिलित
                                    नहीं हुए
                                </option>
                                <option value="15" {{ $complaint->complaint_status == 15 ? 'selected' : '' }}>फोन पर
                                    संपर्क किया
                                </option>
                                <option value="16" {{ $complaint->complaint_status == 16 ? 'selected' : '' }}>ईमेल पर
                                    संपर्क किया
                                </option>
                                <option value="17" {{ $complaint->complaint_status == 17 ? 'selected' : '' }}>
                                    व्हाट्सएप पर संपर्क किया
                                </option>
                                <option value="18" {{ $complaint->complaint_status == 18 ? 'selected' : '' }}>रद्द
                                </option>
                            </select>
                        </div>

                        <div class="col-md-2" id="forwarded_to_field">
                            <label class="form-label">अधिकारी चुनें (आगे भेजे)</label>
                            <select name="forwarded_to" id="forwarded_to" class="form-control"
                                @if ($disableReply) disabled @endif>
                                <!-- Preselect the logged-in manager -->
                                <option value="{{ $loggedInManagerId }}" selected>
                                    {{ \App\Models\User::find($loggedInManagerId)->admin_name }} (You)
                                </option>

                                @foreach ($managers as $manager)
                                    <option value="{{ $manager->admin_id }}">{{ $manager->admin_name }}</option>
                                @endforeach
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

                    <div class="col-12 mt-3">
                        <button type="submit" class="btn btn-primary"
                            @if ($disableReply) disabled @endif>फीडबैक दर्ज करें</button>
                    </div>

                </form>
            </div>
        </div>
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
                // const predefined = predefinedRaw === 0 ? 'अन्य' : (predefinedRaw || '—');

                const cbPhoto = $(this).data('cb-photo');
                const caPhoto = $(this).data('ca-photo');
                const video = $(this).data('video');
                const review = $(this).data('review');
                const importance = $(this).data('importance');
                const critical = $(this).data('critical');
                const details = $(this).data('details') || '—';

                $('#modal-reply').text(reply);
                $('#modal-status').html(statusHtml);
                $('#modal-date').text(replyDate);
                $('#modal-admin').text(admin);
                $('#modal-predefined').text(predefined);
                $('#modal-contact').text(contact);
                $('#modal-review').text(review);
                $('#modal-importance').text(importance);
                $('#modal-critical').text(critical);
                $('#modal-details').text(details);
                $('#modal-reply-from').text(reply_from);

                cbPhoto ? $('#cb-photo-link').attr('href', cbPhoto).show() : $('#cb-photo-link').hide();
                caPhoto ? $('#ca-photo-link').attr('href', caPhoto).show() : $('#ca-photo-link').hide();
                video ? $('#video-link').attr('href', video).show() : $('#video-link').hide();
            });

            $(document).ready(function() {
                $('#updateComplaintForm').on('submit', function(e) {
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
                                $('#success-alert').addClass('d-none');
                            }, 5000);
                        },
                        error: function(xhr) {
                            $("#loader-wrapper").hide();

                            if (xhr.status === 422) { // Validation error
                                const errors = xhr.responseJSON.errors;

                                // Remove old error messages
                                $('.error-text').remove();

                                // Show new error messages under respective fields
                                for (let field in errors) {
                                    const input = $(`[name="${field}"]`);
                                    if (input.length) {
                                        input.after(
                                            `<span class="text-danger error-text">${errors[field][0]}</span>`
                                        );
                                    }
                                }

                                // Scroll to the first error
                                $('html, body').animate({
                                    scrollTop: $(".error-text:first").offset().top - 100
                                }, 500);

                            } else {
                                alert('त्रुटि: अपडेट करते समय एक त्रुटि हुई।');
                                console.log(xhr.responseText);
                            }
                        }
                    });
                });


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



                $(".check").change(function() {

                    $("#form_container").slideDown();

                    var text = $(this).data('text');
                    $(".data-text").html(text);

                    var heading = $(this).data('heading');
                    $(".data-heading").html(heading);

                    var legend = $(this).data('legend');
                    $(".data-legend").html(legend);
                    $(".dynamic-legend").html("विवरण - " +
                        legend);


                    var type = $(this).val();

                    if (type === "समस्या") {
                        $(".mobile-label").html("शिकायतकर्ता का मोबाइल");
                    } else if (type === "विकास") {
                        $(".mobile-label").html("मांगकर्ता का मोबाइल");
                    } else {
                        $(".mobile-label").html("सूचनाकर्ता का मोबाइल");
                    }

                    // Toggle department/date rows
                    if (type === "शुभ सुचना" || type === "अशुभ सुचना") {
                        $(".department_row").hide();
                        $("select[name='department'], select[name='post'], select[name='CharCounter']")
                            .removeAttr("required");

                        $(".date_row").show();
                        $("input[name='from_date'], input[name='program_date']").attr("required", "required");
                    } else {
                        $(".department_row").show();
                        $("select[name='department'], select[name='post'], select[name='CharCounter']")
                            .attr("required", "required");

                        $(".date_row").hide();
                        $("input[name='from_date'], input[name='program_date']").removeAttr("required");
                    }
                });

                if ($(".check:checked").length > 0) {
                    $(".check:checked").trigger("change");
                }

                $('#department-select').on('change', function() {
                    const departmentName = $(this).val();
                    const $postSelect = $('#post-select');

                    $postSelect.html('<option value="">लोड हो रहा है...</option>');

                    if (departmentName) {
                        $.ajax({
                            url: '/get-designations/' + encodeURIComponent(departmentName),
                            method: 'GET',
                            success: function(data) {
                                let options = '<option value="">--चुने--</option>';
                                data.forEach(function(designation) {
                                    options +=
                                        `<option value="${designation.designation_name}">${designation.designation_name}</option>`;
                                });
                                $postSelect.html(options);
                            },
                            error: function() {
                                $postSelect.html('<option value="">लोड करने में त्रुटि</option>');
                            }
                        });
                    } else {
                        $postSelect.html('<option value="">--चुने--</option>');
                    }
                });


                $('#department-select').on('change', function() {
                    const departmentName = $(this).val();
                    const $subjectSelect = $('#subject-select');
                    $subjectSelect.html('<option value="">लोड हो रहा है...</option>');

                    if (departmentName) {
                        $.ajax({
                            url: '/manager/get-subjects-department/' + encodeURIComponent(
                                departmentName),
                            method: 'GET',
                            success: function(data) {
                                let options = '<option value="">--चुने--</option>';
                                data.forEach(function(subject) {
                                    options +=
                                        `<option value="${subject.subject}">${subject.subject}</option>`;
                                });
                                $subjectSelect.html(options);
                            },
                            error: function() {
                                $subjectSelect.html(
                                    '<option value="">लोड करने में त्रुटि</option>');
                            }
                        });
                    } else {
                        $subjectSelect.html('<option value="">--चुने--</option>');
                    }
                });

                $('#txtgram').change(function() {
                    const nagarId = $(this).val();
                    if (!nagarId) return;

                    $('#txtpolling').html('<option value="">लोड हो रहा है...</option>');

                    $.get('/manager/get-pollings-gram/' + nagarId, function(data) {
                        let options = '<option value="">--चुने--</option>';
                        data.forEach(function(item) {
                            options +=
                                `<option value="${item.id}" data-area-id="${item.area_id}">${item.label}</option>`;
                        });
                        $('#txtpolling').html(options);
                    });
                });

                $('#txtpolling').change(function() {
                    let areaId = $(this).find(':selected').data('area-id') || '';
                    $('#area_id').val(areaId);

                    fetchVoterId();
                });



                const complaint = {
                    division: "{{ $complaint->division_id ?? '' }}",
                    district: "{{ $complaint->district_id ?? '' }}",
                    vidhansabha: "{{ $complaint->vidhansabha_id ?? '' }}",
                    mandal: "{{ $complaint->mandal_id ?? '' }}",
                    nagar: "{{ $complaint->gram_id ?? '' }}",
                    polling: "{{ $complaint->polling_id ?? '' }}",
                    area: "{{ $complaint->area_id ?? '' }}"
                };

                function prefillFields() {
                    $('#division_id').val(complaint.division).trigger('change');

                    $.get(`/manager/get-districts/${complaint.division}`, function(data) {
                        $('#district_name').html(data).val(complaint.district).trigger('change');

                        $.get(`/manager/get-vidhansabha/${complaint.district}`, function(data) {
                            $('#txtvidhansabha').html(data).val(complaint.vidhansabha).trigger(
                                'change');

                            $.get(`/manager/get-mandal/${complaint.vidhansabha}`, function(data) {
                                $('#txtmandal').html(data).val(complaint.mandal).trigger(
                                    'change');

                                $.get(`/manager/get-nagar/${complaint.mandal}`, function(data) {
                                    $('#txtgram').append(data).val(complaint.nagar);

                                    $.get(`/manager/get-polling/${complaint.mandal}`,
                                        function(
                                            data) {
                                            $('#txtpolling').html(data).val(
                                                complaint.polling);

                                            $.get(`/manager/get-area/${complaint.polling}`,
                                                function(data) {
                                                    $('#txtarea').html(data)
                                                        .val(complaint
                                                            .area);
                                                });
                                        });
                                });
                            });
                        });
                    });
                }


                function fetchVoterId() {
                    let name = $('#name').val().trim();
                    let father = $('#father_name').val().trim();
                    let areaId = $('#area_id').val();

                    if (!name || !father || !areaId) {
                        $('#voter_id_input').val('');
                        $('#voter-error').hide().text('');
                        return;
                    }

                    $('#voter_id_input').val('Loading...');
                    $('#voter-error').hide().text('');

                    $.ajax({
                        url: '/manager/get-voter',
                        type: 'GET',
                        data: {
                            name: name,
                            father_name: father,
                            area_id: areaId
                        },
                        success: function(res) {
                            $('#voter_id_input').val('');

                            if (res.status === 'success' && res.data && res.data.voter_id) {
                                $('#voter_id_input').val(res.data.voter_id);
                                $('#voter-error').hide().text('');
                            } else {
                                $('#voter_id_input').val('');
                                $('#voter-error')
                                    .text('मतदाता पहचान नहीं मिली (Voter ID not found for given details)')
                                    .show();
                            }
                        },
                        error: function(xhr) {
                            $('#voter_id_input').val('');
                            $('#voter-error')
                                .text('मतदाता पहचान नहीं मिली (Voter ID not found for given details)')
                                .show();
                            console.error("Error fetching voter: " + xhr.responseText);
                        }
                    });
                }

                $('#name, #father_name').on('blur', fetchVoterId);
            });

            document.addEventListener('DOMContentLoaded', function() {
                const statusSelect = document.getElementById('cmp_status');
                const forwardedSelect = document.getElementById('forwarded_to');
                const replyForm = document.getElementById('replyForm');

                function toggleForwardedField() {
                    const selectedValue = parseInt(statusSelect.value);

                    if (selectedValue === 4 || selectedValue === 5 || selectedValue === 13 || selectedValue === 14 ||
                        selectedValue === 15 || selectedValue === 16 || selectedValue === 17 || selectedValue === 18) {
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

                @if ($disableReply)
                    Array.from(replyForm.elements).forEach(el => el.disabled = true);
                @endif
            });


            const subjects = {
                "शुभ सुचना": [{
                        title: "जन्मदिन"
                    },
                    {
                        title: "विवाह/सगाई"
                    },
                    {
                        title: "उपलब्धि/सम्मान/पदोन्नति"
                    },
                    {
                        title: "धार्मिक/सामाजिक आयोजन/भंडारा"
                    },
                    {
                        title: "नौकरी"
                    },
                    {
                        title: "पदवी/परीक्षा उत्तीर्ण"
                    },
                    {
                        title: "अच्छी उपज / नया साधन"
                    },
                    {
                        title: "नये घर का निर्माण/गृह प्रवेश"
                    },
                    {
                        title: "अन्य"
                    },
                ],
                "अशुभ सुचना": [{
                        title: "बीमारी/दुर्घटना"
                    },
                    {
                        title: "मृत्यु/शोक समाचार"
                    },
                    {
                        title: "प्राकृतिक आपदा"
                    },
                    {
                        title: "फसल खराब/नुकसान"
                    },
                    {
                        title: "पशु हानि"
                    },
                    {
                        title: "चोरी/लूट/घटना"
                    },
                    {
                        title: "अन्य"
                    },
                ]
            };

            function populateSubjects(type, preselected = "") {
                const issueSelect = document.getElementById('issue_title');
                issueSelect.innerHTML = '<option value="">--चुने--</option>'; // reset

                if (subjects[type]) {
                    subjects[type].forEach(sub => {
                        const opt = document.createElement('option');
                        opt.value = sub.title;
                        opt.textContent = sub.title;
                        if (sub.title === preselected) {
                            opt.selected = true; // mark saved subject
                        }
                        issueSelect.appendChild(opt);
                    });
                }
            }

            const issueSelect = document.getElementById('issue_title');
            const preselectedSubject = issueSelect.dataset.selected || "";

            // Handle radio change
            document.querySelectorAll('input[name="type"]').forEach(radio => {
                radio.addEventListener('change', function() {
                    populateSubjects(this.value);
                });
            });

            // On page load, check if a radio is already selected
            const checkedRadio = document.querySelector('input[name="type"]:checked');
            if (checkedRadio) {
                populateSubjects(checkedRadio.value, preselectedSubject);
            }
        </script>
    @endpush


@endsection
