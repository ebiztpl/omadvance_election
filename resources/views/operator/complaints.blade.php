@php
    $pageTitle = 'समस्या पंजीयन करे';
    $breadcrumbs = [
        'कार्यालय' => '#',
        'समस्या पंजीयन करे' => '#',
    ];
@endphp

@extends('layouts.app')
@section('title', 'Register complaint')

@section('content')
    <div class="container">

        <div id="success-alert" class="alert alert-success alert-dismissible fade show d-none" role="alert">
            <span id="success-message"></span>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="row page-titles mx-0">
            <div class="col-md-12 col-sm-12 col-xs-12">
                <form id="complaintForm" method="POST" enctype="multipart/form-data"
                    action="{{ route('operator_complaint.store') }}">
                    @csrf
                    <div class="form-group row justify-content-center">
                        <div class="col-md-12 d-flex justify-content-center" style="padding-top:15px;">
                            <div id="type_row" class="d-flex justify-content-center flex-wrap">
                                @php
                                    $types = [
                                        'शुभ सुचना' => ['text' => 'सूचनाकर्ता का नाम', 'heading' => 'शुभ सूचना'],
                                        'अशुभ सुचना' => ['text' => 'सूचनाकर्ता का नाम', 'heading' => 'अशुभ सूचना'],
                                        'समस्या' => ['text' => 'शिकायतकर्ता का नाम', 'heading' => 'समस्या '],
                                        'विकास' => ['text' => 'मांगकर्ता का नाम', 'heading' => 'विकास'],
                                    ];
                                @endphp
                                @foreach ($types as $type => $data)
                                    <label class="btn btn-success text-white m-1">
                                        <input type="radio" name="type" value="{{ $type }}" class="check"
                                            data-text="{{ $data['text'] }}" data-legend="{{ $data['heading'] }}"
                                            style="width: 25px; height: 25px;" required>
                                        <br>{{ $type }}
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <div id="form_container" style="display: none; color: #000">
                        <fieldset class="scheduler-border mb-3">
                            <div class="form-group row">
                                <div class="col-md-4 mb-3 d-flex align-items-center">
                                    <label class="me-2 mr-2 mb-0" style="white-space: nowrap;">
                                        <span class="data-text">नाम</span> <span class="error">*</span>
                                    </label>
                                    <input type="text" class="form-control" name="txtname" required>
                                </div>

                                <div class="col-md-4 mb-3 d-flex align-items-center">
                                    <label class="me-2 mr-2 mb-0" style="white-space: nowrap;">
                                        <span class="mobile-label">मोबाइल</span>
                                    </label>
                                    <input type="text" class="form-control" name="mobile">
                                </div>

                                <div class="col-md-4 mb-3 d-flex align-items-center">
                                    <label class="me-2 mr-2 mb-0" style="white-space: nowrap;">
                                        मतदाता पहचान <span class="error">*</span>
                                    </label>
                                    <input type="text" class="form-control" name="voter" required>
                                </div>

                                <div class="col-md-4 mb-3 d-flex align-items-center">
                                    <label class="me-2 mr-2 mb-0" style="white-space: nowrap;">
                                        संभाग का नाम <span class="error">*</span>
                                    </label>
                                    <select class="form-control bg-light text-muted" disabled required>
                                        <option value="2">ग्वालियर</option>
                                    </select>
                                    <input type="hidden" name="division_id" value="2">
                                </div>

                                {{-- जिले का नाम --}}
                                <div class="col-md-4 mb-3 d-flex align-items-center">
                                    <label class="me-2 mr-2 mb-0" style="white-space: nowrap;">
                                        जिले का नाम <span class="error">*</span>
                                    </label>
                                    <select class="form-control bg-light text-muted" disabled required>
                                        <option value="11">ग्वालियर</option>
                                    </select>
                                    <input type="hidden" name="txtdistrict_name" value="11">
                                </div>

                                {{-- विधानसभा --}}
                                <div class="col-md-4 mb-3 d-flex align-items-center">
                                    <label class="me-2 mr-2 mb-0" style="white-space: nowrap;">
                                        विधानसभा <span class="error">*</span>
                                    </label>
                                    <select class="form-control bg-light text-muted" disabled required>
                                        <option value="49">भितरवार(18)</option>
                                    </select>
                                    <input type="hidden" name="txtvidhansabha" value="49">
                                </div>


                                <div class="col-md-4 mb-3 d-flex align-items-center">
                                    <label class="me-2 mr-2 mb-0" style="white-space: nowrap;">नगर/मंडल <span
                                            class="error">*</span></label>
                                    <select name="txtgram" class="form-control" id="txtgram" required>
                                        <option value="">--चुने--</option>
                                        @foreach ($nagars as $nagar)
                                            <option value="{{ $nagar->nagar_id }}">
                                                {{ $nagar->nagar_name }} - {{ $nagar->mandal->mandal_name ?? '' }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-4 mb-3 d-flex align-items-center">
                                    <label class="me-2 mr-2 mb-0" style="white-space: nowrap;">पोलिंग/क्षेत्र <span
                                            class="error">*</span></label>
                                    <select name="txtpolling" class="form-control" id="txtpolling" required>
                                        <option value="">--चुने--</option>
                                    </select>
                                    <input type="hidden" name="area_id" id="area_id" />
                                </div>

                                <div class="col-md-4 mb-3 d-flex align-items-center">
                                    <label class="me-2 mr-2 mb-0" style="white-space: nowrap;">
                                        फाइल अपलोड करें</label>
                                    <input type="file" class="form-control" name="file_attach">
                                </div>
                            </div>

                            <fieldset class="scheduler-border fieldset-bordered">
                                <legend class="scheduler-border dynamic-legend">विवरण -</legend>

                                {{-- Department Row --}}
                                <div class="form-group row department_row">
                                    <div class="col-md-4 d-flex align-items-center">
                                        <label for="department-select" class="me-2 mr-2 mb-0"
                                            style="white-space: nowrap;">विभाग <span class="error">*</span></label>
                                        <select name="department" id="department-select" class="form-control">
                                            <option value="">--चुने--</option>
                                            @foreach ($departments as $department)
                                                <option value="{{ $department->department_name }}">
                                                    {{ $department->department_name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="col-md-4 d-flex align-items-center">
                                        <label for="post-select" class="me-2 mr-2 mb-0" style="white-space: nowrap;">
                                            पद <span class="error">*</span>
                                        </label>
                                        <select name="post" class="form-control" id="post-select" required>
                                            <option value="">--चुने--</option>
                                        </select>
                                    </div>

                                    <div class="col-md-4 d-flex align-items-center">
                                        <label for="subject-select" class="me-2 mr-2 mb-0"
                                            style="white-space: nowrap;">विषय
                                            <span class="error">*</span></label>
                                        <select placeholder="हिंदी में टाइप करने के लिए कृपया हिंदी कीबोर्ड चालू करें"
                                            name="CharCounter" id="subject-select" class="form-control" required>
                                            <option value="">--चुने--</option>
                                            {{-- @foreach ($subjects as $subject)
                                            <option value="{{ $subject->subject }}">{{ $subject->subject }}</option>
                                        @endforeach --}}
                                            <option value="अन्य">अन्य</option>
                                        </select>
                                    </div>
                                </div>

                                    {{-- Date Row --}}
                                    <div class="form-group row date_row" style="display: none;">
                                        <div class="col-md-3 d-flex align-items-center">
                                            <label for="from_date" class="me-2 mr-2 mb-0"
                                                style="white-space: nowrap;">सूचना
                                                दिनांक</label>
                                            <input type="date" class="form-control" name="from_date">
                                        </div>

                                        <div class="col-md-3 d-flex align-items-center">
                                            <label for="program_date" class="me-2 mr-2 mb-0"
                                                style="white-space: nowrap;">कार्यक्रम दिनांक</label>
                                            <input type="date" class="form-control" name="program_date">
                                        </div>


                                        <div class="col-md-3 d-flex align-items-center">
                                            <label for="to_date" class="me-2 mr-2 mb-0"
                                                style="white-space: nowrap;">कार्यक्रम समय</label>
                                            <input type="time" class="form-control" name="to_date">
                                        </div>
                                    </div>

                                    {{-- Subject, Description, File --}}
                                    <div class="form-group row">
                                        {{-- <div class="col-md-12 mb-3">
                                        <label>विषय <span class="error">*</span></label>
                                        <input type="text" class="form-control"
                                            placeholder="हिंदी में टाइप करने के लिए कृपया हिंदी कीबोर्ड चालू करें"
                                            id="transliterateTextarea" name="CharCounter" maxlength="100" required>
                                    </div> --}}

                                        <div class="col-md-12 mb-3">
                                            <label>विवरण <span class="error">*</span></label>
                                            <textarea class="form-control" placeholder="हिंदी में टाइप करने के लिए कृपया हिंदी कीबोर्ड चालू करें" name="NameText"
                                                id="NameText" rows="5" maxlength="2000" required></textarea>
                                        </div>


                                    </div>
                            </fieldset>
                        </fieldset>


                        <input class="btn btn-primary" type="submit" value="समस्या भेजे"
                            style="background-color:blue; color:#fff; width:100%; height:50px;">

                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            $(document).ready(function() {

                $('#complaintForm').on('submit', function(e) {
                    e.preventDefault();
                    $("#loader-wrapper").show();

                    var formData = new FormData(this);

                    $.ajax({
                        url: $(this).attr('action'),
                        method: 'POST',
                        data: formData,
                        processData: false,
                        contentType: false,
                        success: function(response) {
                            $("#loader-wrapper").hide();


                            if (response.success) {
                                $('#success-message').text(response.message);

                                $('#success-alert').removeClass('d-none');
                                window.scrollTo({
                                    top: 0,
                                    behavior: 'smooth'
                                });

                                $('#complaintForm')[0].reset();

                                $('#txtmandal').html('<option value="">--चुने--</option>');
                                $('#txtvidhansabha').html('<option value="">--चुने--</option>');
                                $('#district_name').html('<option value="">--चुने--</option>');
                                $('#division_id').html('<option value="">--चुने--</option>');
                                $('#txtpolling').html('<option value="">--चुने--</option>');
                                $('#txtarea').html('<option value="">--चुने--</option>');
                            }

                            setTimeout(function() {
                                $('#success-alert').addClass('d-none');
                            }, 5000);
                        },
                        error: function(xhr) {
                            $("#loader-wrapper").hide();
                            alert('त्रुटि: शिकायत दर्ज नहीं की जा सकी।');
                            console.log(xhr.responseText);
                        }
                    });
                });

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
                            url: '/operator/get-subjects-department/' + encodeURIComponent(
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

                $('#txtgram').on('change', function() {
                    const nagarId = $(this).val();

                    if (nagarId) {
                        $.ajax({
                            url: `/get-polling-area/${nagarId}`,
                            type: 'GET',
                            dataType: 'json',
                            success: function(response) {
                                $('#txtpolling').empty().append(
                                    '<option value="">--चुने--</option>');
                                $('#area_id').val('');

                                $.each(response, function(index, item) {
                                    const areaName = item.area ? item.area.area_name : '—';
                                    const optionText =
                                        `${item.polling_name} (${item.polling_no}) - ${areaName}`;
                                    $('#txtpolling').append(
                                        `<option value="${item.gram_polling_id}" data-area-id="${item.area?.area_id ?? ''}">${optionText}</option>`
                                    );
                                });
                            },
                            error: function() {
                                alert('Error loading polling data.');
                            }
                        });
                    } else {
                        $('#txtpolling').html('<option value="">--चुने--</option>');
                        $('#area_id').val('');
                    }
                });

                $('#txtpolling').on('change', function() {
                    const areaId = $(this).find(':selected').data('area-id');
                    $('#area_id').val(areaId || '');
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
                        $(".department_row select").prop("required", false);
                        $(".date_row").show();
                    } else {
                        $(".department_row").show();
                        $(".department_row select").prop("required", true);
                        $(".date_row").hide();
                    }
                });

                if ($(".check:checked").length > 0) {
                    $(".check:checked").trigger("change");
                }
            });
        </script>

        {{-- <script>
            google.load("elements", "1", {
                packages: "transliteration"
            });

            function onLoad() {
                var options = {
                    sourceLanguage: google.elements.transliteration.LanguageCode.ENGLISH,
                    destinationLanguage: google.elements.transliteration.LanguageCode.HINDI,
                    shortcutKey: 'ctrl+g',
                    transliterationEnabled: true
                };
                var control =
                    new google.elements.transliteration.TransliterationControl(options);
                control.makeTransliteratable(['transliterateTextarea']);
            }
            google.setOnLoadCallback(onLoad);
        </script> --}}
    @endpush
@endsection
