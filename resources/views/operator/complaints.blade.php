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
                                <div class="col-md-4 mb-3">
                                    <label><span class="data-text">नाम</span> <span class="error">*</span></label>
                                    <input type="text" class="form-control" name="txtname" required>
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label><span class="mobile-label">मोबाइल</span></label>
                                    <input type="text" class="form-control" name="mobile">
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label>मतदाता पहचान<span class="error">*</span></label>
                                    <input type="text" class="form-control" name="voter" required>
                                </div>


                                <div class="col-md-4 mb-3">
                                    <label>नगर केंद्र/ग्राम केंद्र का नाम <span class="error">*</span></label>
                                    <select name="txtgram" class="form-control" id="txtgram" required>
                                        <option value="">--चुने--</option>
                                        @foreach ($nagars as $nagar)
                                            <option value="{{ $nagar->nagar_id }}">{{ $nagar->nagar_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label>मंडल का नाम <span class="error">*</span></label>
                                    <select name="txtmandal" class="form-control" id="txtmandal" required></select>
                                </div>


                                <div class="col-md-4 mb-3">
                                    <label>विधानसभा का नाम <span class="error">*</span></label>
                                    <select name="txtvidhansabha" class="form-control" id="txtvidhansabha"
                                        required></select>
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label>जिले का नाम <span class="error">*</span></label>
                                    <select class="form-control" name="txtdistrict_name" id="district_name"
                                        required></select>
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label>संभाग का नाम <span class="error">*</span></label>
                                    <select class="form-control" name="division_id" id="division_id" required>
                                    </select>
                                </div>








                                <div class="col-md-4 mb-3">
                                    <label>मतदान केंद्र का नाम/क्रमांक <span class="error">*</span></label>
                                    <select name="txtpolling" class="form-control" id="txtpolling" required></select>
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label>ग्राम चौपाल/वार्ड चौपाल का नाम <span class="error">*</span></label>
                                    <select name="txtarea" class="form-control" id="txtarea" required></select>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label>फाइल अपलोड करें</label>
                                    <input type="file" class="form-control" name="file_attach">
                                </div>
                            </div>

                            <fieldset class="scheduler-border fieldset-bordered">
                                <legend class="scheduler-border dynamic-legend">विवरण -</legend>

                                {{-- Department Row --}}
                                <div class="form-group row department_row">
                                    <div class="col-md-6 mb-3">
                                        <label>विभाग <span class="error">*</span></label>
                                        <select name="department" class="form-control" id="department-select" required>
                                            <option value="">--चुने--</option>
                                            @foreach (['राजस्व विभाग', 'विद्युत विभाग', 'सहकारिता', 'पंचायत', 'पी.एच.ई.', 'नगरीय निकाय', 'पुलिस', 'सिंचाई', 'स्वास्थ्य विभाग', 'पी.डब्ल्यू.डी.', 'खाद्य', 'शिक्षा विभाग', 'कृषि विभाग', 'पशु चिकित्सा', 'एम.बी.वी', 'जनजातीय विभाग', 'वन विभाग'] as $dept)
                                                <option value="{{ $dept }}">{{ $dept }}</option>
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

                                {{-- Date Row --}}
                                <div class="form-group row date_row" style="display: none;">
                                    <div class="col-md-4 mb-3">
                                        <label>सूचना दिनांक <span class="error">*</span></label>
                                        <input type="date" class="form-control" name="from_date">
                                    </div>

                                    <div class="col-md-4 mb-3">
                                        <label>कार्यक्रम दिनांक <span class="error">*</span></label>
                                        <input type="date" class="form-control" name="program_date">
                                    </div>

                                    <div class="col-md-4 mb-3">
                                        <label>कार्यक्रम समय <span class="error">*</span></label>
                                        <input type="time" class="form-control" name="to_date">
                                    </div>
                                </div>

                                {{-- Subject, Description, File --}}
                                <div class="form-group row">
                                    <div class="col-md-12 mb-3">
                                        <label>विषय <span class="error">*</span></label>
                                        <input type="text" class="form-control"
                                            placeholder="हिंदी में टाइप करने के लिए कृपया हिंदी कीबोर्ड चालू करें"
                                            id="transliterateTextarea" name="CharCounter" maxlength="100" required>
                                    </div>

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

                $('#txtgram').change(function() {
                    let nagarID = $(this).val();
                    if (!nagarID) return;

                    $('#txtmandal, #txtvidhansabha, #district_name, #division_id, #txtpolling, #txtarea').html(
                        '<option value="">--चुने--</option>');

                    $.get('/operator/get-parent-mandal/' + nagarID, function(data) {
                        let mandalID = data.mandal_id;
                        if (!mandalID) return;

                        $.get('/operator/get-mandal-from-id/' + mandalID, function(mandalOptions) {
                            $('#txtmandal').html(mandalOptions);
                            $('#txtmandal').val(mandalID).trigger('change');
                        });

                        $.get('/operator/get-pollings/' + mandalID, function(pollings) {
                            let options = '<option value="">--चुने--</option>';
                            pollings.forEach(function(p) {
                                options +=
                                    `<option value="${p.gram_polling_id}">${p.polling_name} (${p.polling_no})</option>`;
                            });
                            $('#txtpolling').html(options);
                        });

                        $.get('/operator/get-parent-vidhansabha/' + mandalID, function(data2) {
                            let vidhansabhaID = data2.vidhansabha_id;
                            if (!vidhansabhaID) return;

                            $.get('/operator/get-vidhansabha-from-id/' + vidhansabhaID,
                                function(vidhansabhaOptions) {
                                    $('#txtvidhansabha').html(vidhansabhaOptions);
                                    $('#txtvidhansabha').val(vidhansabhaID).trigger(
                                        'change');
                                });

                            $.get('/operator/get-parent-district/' + vidhansabhaID, function(
                                data3) {
                                let districtID = data3.district_id;
                                if (!districtID) return;

                                $.get('/operator/get-district-from-id/' + districtID,
                                    function(districtOptions) {
                                        $('#district_name').html(districtOptions);
                                        $('#district_name').val(districtID).trigger(
                                            'change');
                                    });

                                $.get('/operator/get-parent-division/' + districtID,
                                    function(data4) {
                                        let divisionID = data4.division_id;
                                        if (!divisionID) return;

                                        $.get('/operator/get-division-from-id/' +
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
                    $.get('/operator/get-areas/' + pollingID, function(areas) {
                        let options = '<option value="">--चुने--</option>';
                        areas.forEach(function(a) {
                            options += `<option value="${a.area_id}">${a.area_name}</option>`;
                        });
                        $('#txtarea').html(options);
                    });
                });


                // $('#division_id').change(function() {
                //     let divisionID = $(this).val();
                //     $('#district_name').html('<option value="">Loading...</option>');
                //     $.get('/operator/get-districts/' + divisionID, function(data) {
                //         $('#district_name').html(data);
                //     });
                // });

                // $('#district_name').change(function() {
                //     let districtID = $(this).val();
                //     $('#txtvidhansabha').html('<option value="">Loading...</option>');
                //     $.get('/operator/get-vidhansabha/' + districtID, function(data) {
                //         $('#txtvidhansabha').html(data);
                //     });
                // });

                // $('#txtvidhansabha').change(function() {
                //     let vidhansabhaID = $(this).val();
                //     $('#txtmandal').html('<option value="">Loading...</option>');
                //     $.get('/operator/get-mandal/' + vidhansabhaID, function(data) {
                //         $('#txtmandal').html(data);
                //     });
                // });

                // $('#txtmandal').change(function() {
                //     let mandalID = $(this).val();
                //     $('#txtgram').html('<option value="">Loading...</option>');
                //     $.get('/operator/get-nagar/' + mandalID, function(data) {
                //         $('#txtgram').html(data);
                //     });
                //     $.get('/operator/get-polling/' + mandalID, function(data) {
                //         $('#txtpolling').html(data);
                //     });
                // });

                // $('#txtpolling').change(function() {
                //     let pollingID = $(this).val();
                //     $('#txtarea').html('<option value="">Loading...</option>');
                //     $.get('/operator/get-area/' + pollingID, function(data) {
                //         $('#txtarea').html(data);
                //     });
                // });


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
                    'एम.बी.वी': [' सुपरवाइज़र', 'सी.डी.पी.ओ', 'डी.पी.ओ', 'जेडी'],
                    'जनजातीय विभाग': ['सहायक', 'जनजातीय आयुक्त'],
                    'वन विभाग': ['क्षेत्रपाल', 'एस.डी.ओ', 'डी.एफ.ओ']
                };

                $('#department-select').on('change', function() {
                    const selectedDept = $(this).val();
                    const $postSelect = $('#post-select');
                    $postSelect.empty().append('<option value="">--चुने--</option>');

                    if (selectedDept && postsByDepartment[selectedDept]) {
                        postsByDepartment[selectedDept].forEach(function(post) {
                            $postSelect.append(`<option value="${post}">${post}</option>`);
                        });
                    }
                });
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
