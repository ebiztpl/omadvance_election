@php
    $pageTitle = 'समस्याएँ देखे';
    $breadcrumbs = [
        'मैनेजर' => '#',
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
                <span style="color: gray;">स्थिति: {!! $complaint->statusText() !!}</span>
            </div>
            <div class="col-md-12 col-sm-12 col-xs-12">
                <form id="updateComplaintForm" method="POST" enctype="multipart/form-data"
                    action="{{ route('complaints.update', $complaint->complaint_id) }}">
                    @csrf

                    {{-- Type Selection --}}
                    <div class="form-group row justify-content-center">
                        <div class="col-md-12 d-flex justify-content-center" style="padding-top:15px;">
                            <div id="type_row" class="d-flex justify-content-center flex-wrap">
                                @php
                                    $types = [
                                        'शुभ सुचना' => ['text' => 'सूचनाकर्ता का नाम', 'heading' => 'शुभ सूचना'],
                                        'अशुभ सुचना' => ['text' => 'सूचनाकर्ता का नाम', 'heading' => 'अशुभ सूचना'],
                                        'समस्या' => ['text' => 'शिकायतकर्ता का नाम', 'heading' => 'समस्या'],
                                        'विकास' => ['text' => 'मांगकर्ता का नाम', 'heading' => 'विकास'],
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
                                <div class="col-md-4 mb-3">
                                    <label><span class="data-text">नाम</span> <span class="error">*</span></label>
                                    <input type="text" class="form-control" name="txtname"
                                        value="{{ old('txtname', $complaint->name) }}" required>
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label><span class="mobile-label">मोबाइल</span></label>
                                    <input type="text" class="form-control" name="mobile"
                                        value="{{ old('mobile', $complaint->mobile_number) }}">
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label>मतदाता पहचान<span class="error">*</span></label>
                                    <input type="text" class="form-control" name="voter"
                                        value="{{ old('voter', $complaint->voter_id) }}" required>
                                </div>

                                <div class="col-md-4 mb-3">
                                <label>नगर/ग्राम <span class="error">*</span></label>
                                <select name="txtgram" class="form-control" id="txtgram" required>
                                    <option value="{{ $complaint->gram_id }}">
                                        {{ $complaint->gram->nagar_name ?? 'Current' }}
                                    </option>
                                    <option value="">--चुने--</option>
                                    @foreach ($nagars as $nagar)
                                    <option value="{{ $nagar->nagar_id }}"
                                        {{ $nagar->nagar_id == $complaint->gram_id ? 'selected' : '' }}>
                                        {{ $nagar->nagar_name }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-4 mb-3">
                                <label>मंडल <span class="error">*</span></label>
                                <select name="txtmandal" class="form-control" id="txtmandal" required>
                                    <option value="{{ $complaint->mandal_id }}">
                                        {{ $complaint->mandal->mandal_name ?? 'Current' }}
                                    </option>
                                </select>
                            </div>

                            <div class="col-md-4 mb-3">
                                <label>विधानसभा <span class="error">*</span></label>
                                <select name="txtvidhansabha" class="form-control" id="txtvidhansabha" required>
                                    <option value="{{ $complaint->vidhansabha_id }}">
                                        {{ $complaint->vidhansabha->vidhansabha ?? 'Current' }}
                                    </option>
                                </select>
                            </div>


                            <div class="col-md-4 mb-3">
                                <label>जिले का नाम <span class="error">*</span></label>
                                <select class="form-control" name="txtdistrict_name" id="district_name" required>
                                    <option value="{{ $complaint->district_id }}">
                                        {{ $complaint->district->district_name ?? 'Current' }}
                                    </option>
                                </select>
                            </div>


                            <div class="col-md-4 mb-3">
                                <label>संभाग का नाम <span class="error">*</span></label>
                                <select class="form-control" name="division_id" id="division_id" required>
                                    <option value="{{ $complaint->division_id }}">
                                        {{ $complaint->division->division_name ?? 'Current' }}
                                    </option>
                                </select>
                            </div>

                            <div class="col-md-4 mb-3">
                                <label>मतदान केंद्र <span class="error">*</span></label>
                                <select name="txtpolling" class="form-control" id="txtpolling" required>
                                    <option value="{{ $complaint->polling_id }}">
                                        {{ $complaint->polling->polling_name ?? '' }} -
                                        {{ $complaint->polling->polling_no ?? '' }}
                                    </option>
                                </select>
                            </div>

                            <div class="col-md-4 mb-3">
                                <label>ग्राम/वार्ड चौपाल <span class="error">*</span></label>
                                <select name="txtarea" class="form-control" id="txtarea" required>
                                    <option value="{{ $complaint->area_id }}">
                                        {{ $complaint->area->area_name ?? 'Current' }}
                                    </option>
                                </select>
                            </div>

                                <div class="col-md-4 mb-3">
                                    <label>
                                        @if ($complaint->type == 1)
                                            कमांडर द्वारा भेजा गया वीडियो
                                        @else
                                            कार्यालय द्वारा भेजी गई फ़ाइल
                                        @endif
                                    </label><br>

                                    @if ($complaint->issue_attachment)
                                        <a href="{{ asset('assets/upload/complaints/' . $complaint->issue_attachment) }}"
                                            class="btn btn-sm btn-info mb-2" target="_blank">अटैचमेंट खोलें</a>
                                    @else
                                        <button class="btn btn-sm btn-secondary" disabled>कोई अटैचमेंट नहीं है</button>
                                    @endif
                                    {{-- <input type="file" class="form-control" name="file_attach"> --}}
                                </div>
                            </div>

                            {{-- Detail Section --}}
                            <fieldset class="scheduler-border fieldset-bordered">
                                <legend class="scheduler-border dynamic-legend">विवरण</legend>

                                {{-- Department --}}
                                <div class="form-group row department_row">
                                    <div class="col-md-6 mb-3">
                                        <label>विभाग <span class="error">*</span></label>
                                        <select name="department" class="form-control" id="department-select" required>
                                            <option value="">--चुने--</option>
                                            @foreach (['राजस्व विभाग', 'विद्युत विभाग', 'सहकारिता', 'पंचायत', 'पी.एच.ई.', 'नगरीय निकाय', 'पुलिस', 'सिंचाई', 'स्वास्थ्य विभाग', 'पी.डब्ल्यू.डी.', 'खाद्य', 'शिक्षा विभाग', 'कृषि विभाग', 'पशु चिकित्सा', 'एम.बी.वी', 'जनजातीय विभाग', 'वन विभाग'] as $dept)
                                                <option value="{{ $dept }}">
                                                    {{ $dept }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label>पद <span class="error">*</span></label>
                                        <select name="post" class="form-control" id="post-select" required>
                                            <option value="">--चुने--</option>
                                        </select>
                                    </div>
                                </div>

                                {{-- Dates --}}
                                <div class="form-group row date_row">
                                    <div class="col-md-4 mb-3">
                                        <label>सूचना दिनांक</label>
                                        <input type="date" class="form-control" name="from_date"
                                            value="{{ old('from_date', $complaint->news_date) }}">
                                    </div>

                                    <div class="col-md-4 mb-3">
                                        <label>कार्यक्रम दिनांक</label>
                                        <input type="date" class="form-control" name="program_date"
                                            value="{{ old('program_date', $complaint->program_date) }}">
                                    </div>

                                    <div class="col-md-4 mb-3">
                                        <label>कार्यक्रम समय</label>
                                        <input type="time" class="form-control" name="to_date"
                                            value="{{ old('to_date', $complaint->news_time) }}">
                                    </div>
                                </div>

                                {{-- Subject, Description, File --}}
                                <div class="form-group row">
                                    <div class="col-md-12 mb-3">
                                        <label>विषय</label>
                                        <input type="text" class="form-control" name="CharCounter"
                                            value="{{ old('CharCounter', $complaint->issue_title) }}" placeholder="हिंदी में टाइप करने के लिए कृपया हिंदी कीबोर्ड चालू करें" maxlength="100"
                                            required>
                                    </div>

                                    <div class="col-md-12 mb-3">
                                        <label>विवरण</label>
                                        <textarea class="form-control" name="NameText" rows="5" placeholder="हिंदी में टाइप करने के लिए कृपया हिंदी कीबोर्ड चालू करें" maxlength="2000" required>{{ old('NameText', $complaint->issue_description) }}</textarea>
                                    </div>

                                    {{-- <div class="col-md-6 mb-3">
                                        <label>फाइल संलग्न करें</label><br>
                                        @if ($complaint->issue_attachment)
                                            <a href="{{ asset('assets/upload/complaints/' . $complaint->issue_attachment) }}"
                                                class="btn btn-sm btn-info mb-2" target="_blank">View Current</a>
                                        @endif
                                        <input type="file" class="form-control" name="file_attach">
                                    </div> --}}
                                </div>
                            </fieldset>
                        </fieldset>

                        <input class="btn btn-primary" type="submit" value="अपडेट करें"
                            style="background-color:blue; color:#fff; width:100%; height:50px;">
                    </div>
                </form>
            </div>
        </div>

        {{-- Reply History --}}
        <div class="card container" style="color: #000; ">
            <h5 class="my-3">Reply History for {{ $complaint->complaint_number }}</h5>
            <div class="row">
                @foreach ($complaint->replies as $reply)
                    <div class="col-md-4 mb-4">
                        <div class="card h-100" style="border: 1px solid black">
                            <div class="card-body">

                                <p><strong>समस्या/समाधान:</strong> {{ $reply->complaint_reply }}</p>
                                <p><strong>दिनांक:</strong>
                                    {{ $reply->reply_date ? \Carbon\Carbon::parse($reply->reply_date)->format('d-m-Y') : 'N/A' }}
                                </p>
                                @php
                                    $replyFromName =
                                        $reply->reply_from == 1 ? $reply->complaint->user->name ?? 'User' : 'BJS Team';
                                @endphp

                                <p><strong>प्रतिक्रिया देने वाला:</strong> {{ $replyFromName }}</p>

                                @if (!empty($reply->cb_photo))
                                    <label class="form-label mr-3"><strong>पूर्व स्थिति की तस्वीर: </strong> </label>
                                    <a href="{{ asset($reply->cb_photo) }}" class="btn btn-primary mb-3"
                                        target="_blank">अटैचमेंट खोलें</a>
                                @endif

                                @if (!empty($reply->ca_photo))
                                    <label class="form-label mr-4"><strong>बाद की तस्वीर: </strong></label>
                                    <a href="{{ asset($reply->ca_photo) }}" class="btn btn-primary"
                                        target="_blank">अटैचमेंट खोलें</a>
                                @endif

                                @if (!empty($reply->c_video))
                                    <label class="form-label mr-4"><strong>यूट्यूब लिंक: </strong></label>
                                    <a href="{{ asset($reply->c_video) }}" class="btn btn-primary"
                                        target="_blank">{{ $reply->c_video }}</a>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>


        {{-- Reply Form --}}
        <div class="card">
            <div class="card-header" style="color: #000">Reply to {{ $complaint->complaint_number }}</div>
            <div class="card-body">
                <form id="replyForm" method="POST"
                    action="{{ route('complaint_reply.reply', $complaint->complaint_id) }}"
                    enctype="multipart/form-data">
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label">समस्या/समाधान में प्रगति</label>
                            <textarea name="cmp_reply" placeholder="हिंदी में टाइप करने के लिए कृपया हिंदी कीबोर्ड चालू करें" class="form-control" rows="6" required></textarea>
                        </div>

                        <div class="col-md-2">
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
                        </div>

                        <div class="col-12 mt-3">
                            <button type="submit" class="btn btn-primary">शिकायत दर्ज करें</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
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
                            alert('अपडेट करते समय एक त्रुटि हुई।');
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
                        $("select[name='department']").removeAttr("required");
                        $(".date_row").show();
                    } else {
                        $(".department_row").show();
                        $("select[name='department']").attr("required", "required");
                        $(".date_row").hide();
                    }
                });

                if ($(".check:checked").length > 0) {
                    $(".check:checked").trigger("change");
                }

                const postsByDepartment = {
                    'राजस्व विभाग': ['पटवारी', 'आर.आई', 'तहसीलदार', 'एस.डी.एम'],
                    'विद्युत विभाग': ['जेई', 'ऐई', 'डीजीएम', 'एस.ई'],
                    'सहकारिता': ['बिक्रीकर्ता', 'जेडी', 'सचिव'],
                    'पंचायत': ['जीआरएस', 'सचिव', 'सीओ', 'जेपी-सीओ'],
                    'पी.एच.ई.': ['जेई', 'ऐई', 'डीजीएम'],
                    'नगरीय निकाय': [],
                    'पुलिस': ['एस.ओ(टी.आई)', 'एस.डी.ओ.पी', 'ए.डी.एस.पी', 'एस.पी'],
                    'सिंचाई': ['एस.डी.ओ', 'ई.ई', 'एस.ई'],
                    'स्वास्थ्य विभाग': [],
                    'पी.डब्ल्यू.डी.': [],
                    'खाद्य': ['एफ.आई', 'एफ.सी'],
                    'शिक्षा विभाग': ['बीआरसी', 'बीईओ', 'डीईओ', 'जेडी'],
                    'कृषि विभाग': ['एस.ए.डी.ओ', 'एस.डी.ओ', 'डी.डी.ए', 'जे.डी.ए'],
                    'पशु चिकित्सा': ['डीडी', 'जेडी'],
                    'एम.बी.वी': ['सुपरवाइज़र', 'सी.डी.पी.ओ', 'डी.पी.ओ', 'जेडी'],
                    'जनजातीय विभाग': ['सहायक', 'जनजातीय आयुक्त'],
                    'वन विभाग': ['क्षेत्रपाल', 'एस.डी.ओ', 'डी.एफ.ओ']
                };

                const selectedDept = "{{ old('department', $complaint->complaint_department) }}";
                const selectedPost = "{{ old('post', $complaint->complaint_designation) }}";

                function populatePosts(department, selected = null) {
                    const $postSelect = $('#post-select');
                    $postSelect.empty().append('<option value="">--चुने--</option>');

                    if (department && postsByDepartment[department]) {
                        postsByDepartment[department].forEach(function(post) {
                            const isSelected = post === selected ? 'selected' : '';
                            $postSelect.append(`<option value="${post}" ${isSelected}>${post}</option>`);
                        });
                    }
                }

                $(document).ready(function() {
                    if (selectedDept) {
                        $('#department-select').val(selectedDept);
                        populatePosts(selectedDept, selectedPost);
                    }

                    $('#department-select').on('change', function() {
                        const dept = $(this).val();
                        populatePosts(dept);
                    });
                });






                $('#txtgram').change(function() {
                    let nagarID = $(this).val();
                    if (!nagarID) return;

                    $('#txtmandal, #txtvidhansabha, #district_name, #division_id, #txtpolling, #txtarea').html(
                        '<option value="">--चुने--</option>');

                    $.get('/manager/get-parent-mandal/' + nagarID, function(data) {
                        let mandalID = data.mandal_id;
                        if (!mandalID) return;

                        $.get('/manager/get-mandal-from-id/' + mandalID, function(mandalOptions) {
                            $('#txtmandal').html(mandalOptions);
                            $('#txtmandal').val(mandalID).trigger('change');
                        });

                        $.get('/manager/get-pollings/' + mandalID, function(pollings) {
                            let options = '<option value="">--चुने--</option>';
                            pollings.forEach(function(p) {
                                options +=
                                    `<option value="${p.gram_polling_id}">${p.polling_name} (${p.polling_no})</option>`;
                            });
                            $('#txtpolling').html(options);
                        });

                        $.get('/manager/get-parent-vidhansabha/' + mandalID, function(data2) {
                            let vidhansabhaID = data2.vidhansabha_id;
                            if (!vidhansabhaID) return;

                            $.get('/manager/get-vidhansabha-from-id/' + vidhansabhaID,
                                function(vidhansabhaOptions) {
                                    $('#txtvidhansabha').html(vidhansabhaOptions);
                                    $('#txtvidhansabha').val(vidhansabhaID).trigger(
                                        'change');
                                });

                            $.get('/manager/get-parent-district/' + vidhansabhaID, function(
                                data3) {
                                let districtID = data3.district_id;
                                if (!districtID) return;

                                $.get('/manager/get-district-from-id/' + districtID,
                                    function(districtOptions) {
                                        $('#district_name').html(districtOptions);
                                        $('#district_name').val(districtID).trigger(
                                            'change');
                                    });

                                $.get('/manager/get-parent-division/' + districtID,
                                    function(data4) {
                                        let divisionID = data4.division_id;
                                        if (!divisionID) return;

                                        $.get('/manager/get-division-from-id/' +
                                            divisionID,
                                            function(divisionOptions) {
                                                $('#division_id').html(
                                                    divisionOptions);
                                                $('#division_id').val(
                                                    divisionID);
                                            });
                                    });
                            });
                        });
                    });
                });

                $('#txtpolling').change(function() {
                    let pollingID = $(this).val();
                    if (!pollingID) return;

                    $('#txtarea').html('<option value="">लोड हो रहा है...</option>');
                    $.get('/manager/get-areas/' + pollingID, function(areas) {
                        let options = '<option value="">--चुने--</option>';
                        areas.forEach(function(a) {
                            options += `<option value="${a.area_id}">${a.area_name}</option>`;
                        });
                        $('#txtarea').html(options);
                    });
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
            });
        </script>
    @endpush


@endsection
