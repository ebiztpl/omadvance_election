@php
    $pageTitle = 'सूचना पंजीयन करे';
    $breadcrumbs = [
        'कार्यालय' => '#',
        'सूचना पंजीयन करे' => '#',
    ];
@endphp

@extends('layouts.app')
@section('title', 'Register Suchna')

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
                    action="{{ route('operator_suchna.store') }}">
                    @csrf
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
                                <div class="col-md-3 mb-3 d-flex align-items-center">
                                    <label class="me-2 mr-2 mb-0" style="white-space: nowrap;">
                                        <span class="data-text">नाम</span> <span class="error">*</span>
                                    </label>
                                    <div class="flex-grow-1">
                                        <input type="text" class="form-control" name="txtname" id="name" required>
                                    </div>
                                </div>

                                <div class="col-md-3 mb-3 d-flex align-items-center">
                                    <label class="me-2 mr-2 mb-0" style="white-space: nowrap;">
                                        पिता का नाम <span class="error">*</span>
                                    </label>
                                    <div class="flex-grow-1">
                                        <input type="text" class="form-control" name="father_name" id="father_name"
                                            required>
                                    </div>
                                </div>

                                <div class="col-md-3 mb-3 d-flex align-items-center">
                                    <label class="me-2 mr-2 mb-0" style="white-space: nowrap;">
                                        <span class="mobile-label">सूचनाकर्ता का मोबाइल<span
                                                class="ml-1 error">*</span></span>
                                    </label>
                                    <div class="flex-grow-1">
                                        <input type="text" class="form-control" name="mobile" required>
                                    </div>
                                </div>



                                <div class="col-md-3 mb-3 d-flex align-items-center">
                                    <label class="me-2 mr-2 mb-0" style="white-space: nowrap;">
                                        संभाग का नाम <span class="error">*</span>
                                    </label>
                                    <select class="form-control bg-light text-muted" disabled required>
                                        <option value="2">ग्वालियर</option>
                                    </select>
                                    <input type="hidden" name="division_id" value="2">
                                </div>

                                {{-- जिले का नाम --}}
                                <div class="col-md-3 mb-3 d-flex align-items-center">
                                    <label class="me-2 mr-2 mb-0" style="white-space: nowrap;">
                                        जिले का नाम <span class="error">*</span>
                                    </label>
                                    <select class="form-control bg-light text-muted" disabled required>
                                        <option value="11">ग्वालियर</option>
                                    </select>
                                    <input type="hidden" name="txtdistrict_name" value="11">
                                </div>

                                {{-- विधानसभा --}}
                                <div class="col-md-3 mb-3 d-flex align-items-center">
                                    <label class="me-2 mr-2 mb-0" style="white-space: nowrap;">
                                        विधानसभा <span class="error">*</span>
                                    </label>
                                    <select class="form-control bg-light text-muted" disabled required>
                                        <option value="49">भितरवार(18)</option>
                                    </select>
                                    <input type="hidden" name="txtvidhansabha" value="49">
                                </div>


                                <div class="col-md-3 mb-3 d-flex align-items-center">
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

                                <div class="col-md-3 mb-3 d-flex align-items-center">
                                    <label class="me-2 mr-2 mb-0" style="white-space: nowrap;">पोलिंग/क्षेत्र <span
                                            class="error">*</span></label>
                                    <select name="txtpolling" class="form-control" id="txtpolling" required>
                                        <option value="">--चुने--</option>
                                    </select>
                                    <input type="hidden" name="area_id" id="area_id" />
                                </div>

                                 <div class="col-md-3 d-flex align-items-center">
                                    <label for="jati-select" class="me-2 mr-2 mb-0" style="white-space: nowrap;">जाति
                                    </label>
                                    <select name="jati" id="jati-select" class="form-control">
                                        <option value="">--चुने--</option>
                                        @foreach ($jatis as $jati)
                                            <option value="{{ $jati->jati_id }}">
                                                {{ $jati->jati_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-3 mb-3 d-flex align-items-center">
                                    <label class="me-2 mr-2 mb-0" style="white-space: nowrap;">
                                        रेफरेंस नाम
                                    </label>
                                    <input type="text" class="form-control" name="reference">
                                </div>

                                <div class="col-md-3 mb-3 d-flex align-items-center">
                                    <label class="me-2 mr-2 mb-0" style="white-space: nowrap;">
                                        मतदाता पहचान <span class="error">*</span>
                                    </label>

                                    <div class="d-flex flex-column w-100">
                                        <input type="text" class="form-control" id="voter_id_input" name="voter"
                                            required>
                                        <small id="voter-error" class="text-danger mt-1" style="display:none;"></small>
                                    </div>
                                </div>

                                <div class="col-md-3 mb-3 d-flex align-items-center">
                                    <label class="me-2 mr-2 mb-0" style="white-space: nowrap;">
                                        फाइल अपलोड करें</label>
                                    <input type="file" class="form-control" name="file_attach">
                                </div>
                            </div>

                            <fieldset class="scheduler-border fieldset-bordered">
                                <legend class="scheduler-border dynamic-legend">विवरण -</legend>
                                <div class="form-group row date_row">
                                    <div class="col-md-3 d-flex align-items-center">
                                        <label for="from_date" class="me-2 mr-2 mb-0" style="white-space: nowrap;">सूचना
                                            दिनांक<span class="ml-1 error">*</span></label>
                                        <input type="date" class="form-control" name="from_date" required>
                                    </div>

                                    <div class="col-md-3 d-flex align-items-center">
                                        <label for="program_date" class="me-2 mr-2 mb-0"
                                            style="white-space: nowrap;">कार्यक्रम दिनांक<span
                                                class="ml-1 error">*</span></label>
                                        <input type="date" class="form-control" name="program_date" required>
                                    </div>


                                    <div class="col-md-3 d-flex align-items-center">
                                        <label for="to_date" class="me-2 mr-2 mb-0"
                                            style="white-space: nowrap;">कार्यक्रम समय</label>
                                        <input type="time" class="form-control" name="to_date">
                                    </div>

                                    <div class="col-md-3 d-flex align-items-center">
                                        <label for="subject-select" class="me-2 mr-2 mb-0"
                                            style="white-space: nowrap;">विषय
                                            <span class="error">*</span></label>
                                        <select name="CharCounter" id="issue_title" class="form-control" required>
                                            <option value="">--चुने--</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="form-group row">
                                    <div class="col-md-12 mb-3">
                                        <label>विवरण <span class="error">*</span></label>
                                        <textarea class="form-control" placeholder="हिंदी में टाइप करने के लिए कृपया हिंदी कीबोर्ड चालू करें" name="NameText"
                                            id="NameText" rows="5" maxlength="2000" required></textarea>
                                    </div>
                                </div>
                            </fieldset>
                        </fieldset>


                        <input class="btn btn-primary" type="submit" value="सूचना भेजे"
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
                            if (xhr.status === 422) {
                                const errors = xhr.responseJSON.errors;

                                $('.error-text').remove();

                                for (let field in errors) {
                                    const input = $(`[name="${field}"]`);
                                    if (input.length) {
                                        input.after(
                                            `<span class="text-danger error-text">${errors[field][0]}</span>`
                                        );
                                    }
                                }
                                $('html, body').animate({
                                    scrollTop: $(".error-text:first").offset().top - 100
                                }, 500);

                            } else {
                                alert('त्रुटि: सूचना दर्ज नहीं की जा सकी।');
                                console.log(xhr.responseText);
                            }
                        }
                    });
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
                    fetchVoterId();
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
                });

                if ($(".check:checked").length > 0) {
                    $(".check:checked").trigger("change");
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
                        url: '/get-voter',
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


                document.querySelectorAll('input[name="type"]').forEach(radio => {
                    radio.addEventListener('change', function() {
                        const type = this.value;
                        const issueSelect = document.getElementById('issue_title');
                        issueSelect.innerHTML = '<option value="">-- सभी --</option>'; // reset

                        if (subjects[type]) {
                            subjects[type].forEach(sub => {
                                const opt = document.createElement('option');
                                opt.value = sub.title;
                                opt.textContent = sub.title;
                                issueSelect.appendChild(opt);
                            });
                        }
                    });
                });

                // Optional: trigger change if a radio is pre-selected
                const checkedRadio = document.querySelector('input[name="type"]:checked');
                if (checkedRadio) {
                    checkedRadio.dispatchEvent(new Event('change'));
                }
            });
        </script>
    @endpush
@endsection
