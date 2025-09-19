@php
    $pageTitle = 'समस्याएँ अपडेट करें';
    $breadcrumbs = [
        'कार्यालय' => '#',
        'समस्याएँ अपडेट करें' => '#',
    ];
@endphp

@extends('layouts.app')
@section('title', 'Update Complaints')

@section('content')
    <div class="container">

        <div id="success-alert" class="alert alert-success alert-dismissible fade show d-none" role="alert">
            <span id="success-message"></span>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>

        <div id="error-alert" class="alert alert-danger alert-dismissible fade show d-none" role="alert">
            <span id="error-message"></span>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>

        <div class="card mb-3">
            <div class="card-header d-flex justify-content-between">
                <h5>{{ $complaint->complaint_number }}</h5>
                <span style="color: gray;">समस्या स्थिति: {!! $complaint->statusText() !!}</span>
            </div>
            <div class="col-md-12 col-sm-12 col-xs-12">
                <form id="updateComplaintForm" method="POST" enctype="multipart/form-data"
                    action="{{ route('operatorcomplaints.update', $complaint->complaint_id) }}">
                    @csrf

                    <div class="form-group row justify-content-center">
                        <div class="col-md-12 d-flex justify-content-center" style="padding-top:15px;">
                            <div id="type_row" class="d-flex justify-content-center flex-wrap">
                                @php
                                    $types = [
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
                                <div class="col-md-3 mb-3 d-flex align-items-center">
                                    <label class="me-2 mr-2 mb-0" style="white-space: nowrap;">
                                        <span class="data-text">नाम</span> <span class="error">*</span>
                                    </label>
                                    <div class="flex-grow-1">
                                        <input type="text" class="form-control" name="txtname" id="name"
                                            value="{{ old('txtname', $complaint->name) }}" required>
                                    </div>
                                </div>

                                <div class="col-md-3 mb-3 d-flex align-items-center">
                                    <label class="me-2 mr-2 mb-0" style="white-space: nowrap;">
                                        पिता का नाम <span class="error">*</span>
                                    </label>
                                    <div class="flex-grow-1">
                                        <input type="text" class="form-control" name="father_name" id="father_name"
                                            value="{{ old('father_name', $complaint->father_name) }}" required>
                                    </div>
                                </div>

                                <div class="col-md-3 mb-3 d-flex align-items-center">
                                    <label class="me-2 mr-2 mb-0" style="white-space: nowrap;">
                                        <span class="mobile-label">मोबाइल</span>
                                    </label>
                                    <div class="flex-grow-1">
                                        <input type="text" class="form-control" name="mobile"
                                            value="{{ old('mobile', $complaint->mobile_number) }}">
                                    </div>
                                </div>

                                <div class="col-md-3 mb-3 d-flex align-items-center">
                                    <label class="me-2 mr-2 mb-0" style="white-space: nowrap;">
                                        संभाग का नाम <span class="error">*</span>
                                    </label>
                                    <select class="form-control" name="division_id" id="division_id" required>
                                        @foreach ($divisions as $division)
                                            <option value="{{ $division->division_id }}"
                                                {{ $complaint->division_id == $division->division_id ? 'selected' : '' }}>
                                                {{ $division->division_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-3 mb-3 d-flex align-items-center">
                                    <label class="me-2 mr-2 mb-0" style="white-space: nowrap;">
                                        जिले का नाम <span class="error">*</span>
                                    </label>
                                    <select class="form-control" name="txtdistrict_name" id="district_id" required>
                                        <option value="{{ $complaint->district_id }}"
                                            {{ $complaint->district_id == $complaint->district_id ? 'selected' : '' }}>
                                            {{ $complaint->district_name }}
                                        </option>
                                    </select>
                                </div>

                                {{-- Vidhansabha --}}
                                <div class="col-md-3 mb-3 d-flex align-items-center">
                                    <label class="me-2 mr-2 mb-0" style="white-space: nowrap;">
                                        विधानसभा <span class="error">*</span>
                                    </label>
                                    <select class="form-control" name="txtvidhansabha" id="vidhansabha_id" required>
                                        <option value="{{ $complaint->vidhansabha_id }}"
                                            {{ $complaint->vidhansabha_id == $complaint->vidhansabha_id ? 'selected' : '' }}>
                                            {{ $complaint->vidhansabha }}
                                        </option>
                                    </select>
                                </div>


                                <div class="col-md-3 mb-3 d-flex align-items-center">
                                    <label class="me-2 mr-2 mb-0" style="white-space: nowrap;">नगर/मंडल</label>
                                    <select name="txtgram" class="form-control" id="txtgram">
                                        <option value="">--चुने--</option>
                                        @foreach ($nagars as $nagar)
                                            <option value="{{ $nagar->nagar_id }}"
                                                {{ $nagar->nagar_id == $complaint->gram_id ? 'selected' : '' }}>
                                                {{ $nagar->nagar_name }} - {{ $nagar->mandal->mandal_name ?? '' }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>


                                <div class="col-md-3 mb-3 d-flex align-items-center">
                                    <label class="me-2 mr-2 mb-0" style="white-space: nowrap;">मतदान
                                        केंद्र/ग्राम/वार्ड</label>
                                    <select name="txtpolling" class="form-control" id="txtpolling" >
                                        <option value="{{ $complaint->polling_id }}">
                                            {{ $complaint->polling->polling_name ?? '' }}
                                            ({{ $complaint->polling->polling_no ?? '' }}) -
                                            {{ $complaint->polling->area->area_name ?? '' }}
                                        </option>
                                    </select>
                                    <input type="hidden" id="area_id" name="area_id"
                                        value="{{ $complaint->polling->area->area_id ?? '' }}">
                                </div>


                                <div class="col-md-3 d-flex align-items-center">
                                    <label for="jati-select" class="me-2 mr-2 mb-0" style="white-space: nowrap;">जाति
                                    </label>
                                    <select name="jati" id="jati-select" class="form-control">
                                        <option value="">--चुने--</option>
                                        @foreach ($jatis as $jati)
                                            <option value="{{ $jati->jati_id }}"
                                                {{ old('jati', $complaint->jati->jati_id ?? '') == $jati->jati_id ? 'selected' : '' }}>
                                                {{ $jati->jati_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-3 mb-3 d-flex align-items-center">
                                    <label class="me-2 mr-2 mb-0" style="white-space: nowrap;">
                                        मतदाता पहचान <span class="error">*</span>
                                    </label>

                                    <div class="d-flex flex-column w-100">
                                        <input type="text" class="form-control" name="voter" id="voter_id_input"
                                            value="{{ old('voter', $complaint->voter_id) }}" required>
                                        <small id="voter-error" class="text-danger mt-1" style="display:none;"></small>
                                    </div>
                                </div>


                                <div class="col-md-3 mb-3 d-flex align-items-center">
                                    <label class="me-2 mr-2 mb-0" style="white-space: nowrap;">
                                        रेफरेंस नाम
                                    </label>
                                    <div class="flex-grow-1">
                                        <input type="text" class="form-control" name="reference"
                                            value="{{ old('reference', $complaint->reference_name) }}">
                                    </div>
                                </div>

                                <div class="col-md-3 mb-3">
                                    <div class="d-flex align-items-center mb-2">
                                        <label class="mr-2 mb-0 flex-shrink-0">
                                            @if ($complaint->type == 1)
                                                कमांडर द्वारा भेजा गया वीडियो
                                            @else
                                                कार्यालय द्वारा भेजी गई फ़ाइल
                                            @endif
                                        </label>

                                        @if ($complaint->issue_attachment)
                                            <a href="{{ asset('assets/upload/complaints/' . $complaint->issue_attachment) }}"
                                                class="btn btn-sm btn-info flex-shrink-0" target="_blank">अटैचमेंट
                                                खोलें</a>
                                        @else
                                            <button class="btn btn-sm btn-secondary flex-shrink-0" disabled>कोई अटैचमेंट
                                                नहीं है</button>
                                        @endif
                                    </div>

                                    <input type="file" class="form-control form-control-sm" name="file_attach"
                                        id="file_attach">
                                    <span class="text-danger small" id="file_attach_error"></span>
                                </div>
                            </div>

                            <fieldset class="scheduler-border fieldset-bordered">
                                <legend class="scheduler-border dynamic-legend">विवरण</legend>


                                <div class="form-group row department_row">
                                    <div class="col-md-3 d-flex align-items-center">
                                        <label for="department-select" class="me-2 mr-2 mb-0"
                                            style="white-space: nowrap;">विभाग
                                            <span class="error">*</span></label>
                                        <select name="department" id="department-select" class="form-control" required>
                                            <option value="">--चुने--</option>
                                            @foreach ($departments as $department)
                                                <option value="{{ $department->department_name }}"
                                                    {{ old('department', $complaint->complaint_department ?? '') == $department->department_name ? 'selected' : '' }}>
                                                    {{ $department->department_name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="col-md-3 d-flex align-items-center">
                                        <label for="post-select" class="me-2 mr-2 mb-0" style="white-space: nowrap;">पद
                                            <span class="error">*</span></label>
                                        <select name="post" class="form-control" id="post-select" required>
                                            @if (!empty($complaint->complaint_designation))
                                                <option value="{{ $complaint->complaint_designation }}" selected>
                                                    {{ $complaint->complaint_designation }}
                                                </option>
                                            @else
                                                <option value="">--चुने--</option>
                                            @endif
                                        </select>
                                    </div>

                                    <div class="col-md-3 d-flex align-items-center">
                                        <label for="subject-select" class="me-2 mr-2 mb-0"
                                            style="white-space: nowrap;">विषय
                                            <span class="error">*</span></label>
                                        <select placeholder="हिंदी में टाइप करने के लिए कृपया हिंदी कीबोर्ड चालू करें"
                                            name="CharCounter" id="subject-select" class="form-control" required>
                                            @if (!empty($complaint->issue_title))
                                                <option value="{{ $complaint->issue_title }}" selected>
                                                    {{ $complaint->issue_title }}
                                                </option>
                                            @else
                                                <option value="">--चुने--</option>
                                            @endif
                                            <option value="अन्य">अन्य</option>
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
                                location.reload();
                            }, 3000);

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
                        $(".mobile-label").html("शिकायतकर्ता का मोबाइल <span class='error'>*</span>");
                    } else {
                        $(".mobile-label").html("मांगकर्ता का मोबाइल <span class='error'>*</span>");
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
                            url: '/operator/get-subjects-department/' + encodeURIComponent(
                                departmentName),
                            method: 'GET',
                            success: function(data) {
                                let options = '<option value="">--चुने--</option>';
                                data.forEach(function(subject) {
                                    options +=
                                        `<option value="${subject.subject}">${subject.subject}</option>`;
                                });
                                options += '<option value="अन्य">अन्य</option>';
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


                let selectedDivision = $('#division_id').val();
                let selectedDistrict = "{{ $complaint->district_id }}";
                let selectedVidhansabha = "{{ $complaint->vidhansabha_id }}";

                if (selectedDivision) {
                    $.get('/operator/get-districts/' + selectedDivision, function(data) {
                        $('#district_id').html(data);

                        // Set current district
                        $('#district_id').val(selectedDistrict);

                        // Populate vidhansabha based on district
                        if (selectedDistrict) {
                            $.get('/operator/get-vidhansabha/' + selectedDistrict, function(data) {
                                $('#vidhansabha_id').html(data);

                                // Set current vidhansabha
                                $('#vidhansabha_id').val(selectedVidhansabha);
                            });
                        }
                    });
                }

               $('#division_id').on('change', function() {
                    let divisionId = $(this).val();
                    if (!divisionId) return;

                    $.get('/manager/get-districts/' + divisionId, function(data) {
                        $('#district_id').html(data);

                        let firstDistrict = $('#district_id option:first').val();
                        if (firstDistrict) {
                            $.get('/manager/get-vidhansabha/' + firstDistrict, function(data) {
                                $('#vidhansabha_id').html(data);
                            });
                        }
                    });
                });

                // On district change
                $('#district_id').on('change', function() {
                    let districtId = $(this).val();
                    if (!districtId) return;

                    $.get('/manager/get-vidhansabha/' + districtId, function(data) {
                        $('#vidhansabha_id').html(data);
                    });
                });

                // On vidhansabha change
                $('#vidhansabha_id').on('change', function() {
                    let vidhansabhaId = $(this).val();
                    if (!vidhansabhaId) return;

                    $.get('/operator/get-nagars-by-vidhansabha/' + vidhansabhaId, function(data) {
                        $('#txtgram').html('<option value="">--चुने--</option>');
                        $('#txtpolling').html('<option value="">--चुने--</option>');
                        $('#area_id').val('');

                        $.each(data, function(i, option) {
                            $('#txtgram').append(option);
                        });
                    });
                });

                $('#txtgram').change(function() {
                    const nagarId = $(this).val();
                    if (!nagarId) return;

                    $('#txtpolling').html('<option value="">लोड हो रहा है...</option>');

                    $.get('/operator/get-pollings-gram/' + nagarId, function(data) {
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
                                    .text(
                                        'मतदाता पहचान नहीं मिली (Voter ID not found for given details)'
                                    )
                                    .show();
                            }
                        },
                        error: function(xhr) {
                            $('#voter_id_input').val('');
                            $('#voter-error')
                                .text(
                                    'मतदाता पहचान नहीं मिली (Voter ID not found for given details)'
                                )
                                .show();
                            console.error("Error fetching voter: " + xhr.responseText);
                        }
                    });
                }

                $('#name, #father_name').on('blur', fetchVoterId);


                $('#file_attach').on('change', function() {
                    const file = this.files[0];
                    $('#file_attach_error').text('');

                    if (!file) return;

                    const extension = file.name.split('.').pop().toLowerCase();
                    const imageMaxSize = 2 * 1024 * 1024; // 5 MB
                    const videoMaxSize = 15 * 1024 * 1024; // 15 MB
                    const imageTypes = ['jpg', 'jpeg', 'png'];
                    const videoTypes = ['mp4', 'mov', 'avi', 'mkv'];
                    const blocked = ['exe', 'php', 'js', 'sh', 'bat'];

                    if (blocked.includes(extension)) {
                        $('#file_attach_error').text('यह फ़ाइल प्रकार अनुमति नहीं है।');
                        $(this).val('');
                        return;
                    }

                    if (imageTypes.includes(extension)) {
                        if (file.size > imageMaxSize) {
                            $('#file_attach_error').text('छवि फ़ाइल अधिकतम 2MB हो सकती है।');
                            $(this).val('');
                        }
                    } else if (videoTypes.includes(extension)) {
                        if (file.size > videoMaxSize) {
                            $('#file_attach_error').text('वीडियो फ़ाइल अधिकतम 15MB हो सकती है।');
                            $(this).val('');
                        }
                    } else {
                        $('#file_attach_error').text(
                            'केवल छवि (JPG, PNG) या वीडियो (MP4, MOV, AVI, MKV) फ़ाइलें अपलोड की जा सकती हैं।'
                        );
                        $(this).val('');
                    }
                });
            });
        </script>
    @endpush


@endsection
