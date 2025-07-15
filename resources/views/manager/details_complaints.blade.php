@php
    $pageTitle = 'समस्याएँ देखे';
    $breadcrumbs = [
        'ऑपरेटर' => '#',
        'समस्याएँ देखे' => '#',
    ];
@endphp

@extends('layouts.app')
@section('title', 'View Complaints')

@section('content')
    <div class="container">

        <div class="card mb-3">
            <div class="card-header d-flex justify-content-between">
                <h5>{{ $complaint->complaint_number }}</h5>
                <span style="color: gray;">Status: {!! $complaint->statusText() !!}</span>
            </div>
            <div class="col-md-12 col-sm-12 col-xs-12">
                <form method="POST" enctype="multipart/form-data"
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
                                    <label>संभाग का नाम <span class="error">*</span></label>
                                    <select class="form-control" name="division_id" id="division_id" required>
                                        <option value="">--Select--</option>
                                        @foreach ($divisions as $division)
                                            <option value="{{ $division->division_id }}"
                                                {{ $complaint->division_id == $division->division_id ? 'selected' : '' }}>
                                                {{ $division->division_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label>जिले का नाम <span class="error">*</span></label>
                                    <select class="form-control" name="txtdistrict_name" id="district_name" required>
                                        <option value="{{ $complaint->district_id }}">
                                            {{ $complaint->district->district_name ?? 'Current' }}</option>
                                    </select>
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label>विधानसभा <span class="error">*</span></label>
                                    <select name="txtvidhansabha" class="form-control" id="txtvidhansabha" required>
                                        <option value="{{ $complaint->vidhansabha_id }}">
                                            {{ $complaint->vidhansabha->vidhansabha ?? 'Current' }}</option>
                                    </select>
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label>मंडल <span class="error">*</span></label>
                                    <select name="txtmandal" class="form-control" id="txtmandal" required>
                                        <option value="{{ $complaint->mandal_id }}">
                                            {{ $complaint->mandal->mandal_name ?? 'Current' }}</option>
                                    </select>
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label>नगर/ग्राम <span class="error">*</span></label>
                                    <select name="txtgram" class="form-control" id="txtgram" required>
                                        <option value="{{ $complaint->gram_id }}">
                                            {{ $complaint->gram->nagar_name ?? 'Current' }}</option>
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
                                            {{ $complaint->area->area_name ?? 'Current' }}</option>
                                    </select>
                                </div>
                            </div>

                            {{-- Detail Section --}}
                            <fieldset class="scheduler-border fieldset-bordered">
                                <legend class="scheduler-border dynamic-legend">विवरण</legend>

                                {{-- Department --}}
                                <div class="form-group row department_row">
                                    <div class="col-md-6 mb-3">
                                        <label>विभाग <span class="error">*</span></label>
                                        <select name="department" class="form-control" required>
                                            <option value="">--चुने--</option>
                                            @foreach (['राजस्व विभाग', 'विद्युत विभाग', 'सहकारिता', 'पंचायत', 'पी.एच.ई.', 'नगरीय निकाय', 'पुलिस', 'सिंचाई', 'स्वास्थ्य विभाग', 'पी.डब्ल्यू.डी.', 'खाद्य'] as $dept)
                                                <option value="{{ $dept }}"
                                                    {{ $complaint->complaint_department == $dept ? 'selected' : '' }}>
                                                    {{ $dept }}</option>
                                            @endforeach
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
                                            value="{{ old('CharCounter', $complaint->issue_title) }}" maxlength="100"
                                            required>
                                    </div>

                                    <div class="col-md-12 mb-3">
                                        <label>विवरण</label>
                                        <textarea class="form-control" name="NameText" rows="5" maxlength="2000" required>{{ old('NameText', $complaint->issue_description) }}</textarea>
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label>फाइल संलग्न करें</label><br>
                                        @if ($complaint->issue_attachment)
                                            <a href="{{ asset('assets/upload/complaints/' . $complaint->issue_attachment) }}"
                                                class="btn btn-sm btn-info mb-2" target="_blank">View Current</a>
                                        @endif
                                        <input type="file" class="form-control" name="file_attach">
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

        {{-- Reply History --}}
        <div class="card container" style="color: #000; ">
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
                                <p>{{ $reply->complaint_reply }}</p>

                                <label class="form-label mr-3">Before Images: </label>
                                @if (!empty($reply->cb_photo))
                                    <a href="{{ asset($reply->cb_photo) }}" class="btn btn-primary mb-3"
                                        target="_blank">Open Attachment</a>
                                @else
                                    <p>No attachment</p>
                                @endif

                                <label class="form-label mr-4">After Images: </label>
                                @if (!empty($reply->ca_photo))
                                    <a href="{{ asset($reply->ca_photo) }}" class="btn btn-primary" target="_blank">Open
                                        Attachment</a>
                                @else
                                    <p>No attachment</p>
                                @endif

                                <label class="form-label mr-4">Youtube Link: </label>
                                @if (!empty($reply->c_video))
                                    <a href="{{ asset($reply->c_video) }}" class="btn btn-primary"
                                        target="_blank">{{ $reply->c_video }}</a>
                                @else
                                    <p>No attachment</p>
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
                <form method="POST" action="{{ route('complaint_reply.reply', $complaint->complaint_id) }}"
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
                                <option value="4" {{ $complaint->status == 5 ? 'selected' : '' }}>Cancel</option>
                            </select>
                        </div>

                        <div class="col-12 mt-3">
                            <button type="submit" class="btn btn-primary">Post Reply</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            $(document).ready(function() {
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

                const complaint = {
                    division: "{{ $complaint->division_id ?? '' }}",
                    district: "{{ $complaint->district_id ?? '' }}",
                    vidhansabha: "{{ $complaint->vidhansabha_id ?? '' }}",
                    mandal: "{{ $complaint->mandal_id ?? '' }}",
                    gram: "{{ $complaint->gram_id ?? '' }}",
                    polling: "{{ $complaint->polling_id ?? '' }}",
                    area: "{{ $complaint->area_id ?? '' }}"
                };

                if (complaint.division) {
                    $('#division_id').val(complaint.division).trigger("change");

                    $.get('/operator/get-districts/' + complaint.division, function(data) {
                        $('#district_name').html(data);
                        $('#district_name').val(complaint.district).trigger("change");

                        $.get('/operator/get-vidhansabha/' + complaint.district, function(data) {
                            $('#txtvidhansabha').html(data);
                            $('#txtvidhansabha').val(complaint.vidhansabha).trigger("change");

                            $.get('/operator/get-mandal/' + complaint.vidhansabha, function(data) {
                                $('#txtmandal').html(data);
                                $('#txtmandal').val(complaint.mandal).trigger("change");

                                $.get('/operator/get-nagar/' + complaint.mandal, function(
                                    data) {
                                    $('#txtgram').html(data);
                                    $('#txtgram').val(complaint.gram);
                                });

                                $.get('/operator/get-polling/' + complaint.mandal, function(
                                    data) {
                                    $('#txtpolling').html(data);
                                    $('#txtpolling').val(complaint.polling).trigger(
                                        "change");

                                    $.get('/operator/get-area/' + complaint.polling,
                                        function(data) {
                                            $('#txtarea').html(data);
                                            $('#txtarea').val(complaint.area);
                                        });
                                });
                            });
                        });
                    });
                }

                $('#division_id').change(function() {
                    const divisionID = $(this).val();
                    $('#district_name').html('<option value="">Loading...</option>');
                    $.get('/operator/get-districts/' + divisionID, function(data) {
                        $('#district_name').html(data);
                    });
                });

                $('#district_name').change(function() {
                    const districtID = $(this).val();
                    $('#txtvidhansabha').html('<option value="">Loading...</option>');
                    $.get('/operator/get-vidhansabha/' + districtID, function(data) {
                        $('#txtvidhansabha').html(data);
                    });
                });

                $('#txtvidhansabha').change(function() {
                    const vidhansabhaID = $(this).val();
                    $('#txtmandal').html('<option value="">Loading...</option>');
                    $.get('/operator/get-mandal/' + vidhansabhaID, function(data) {
                        $('#txtmandal').html(data);
                    });
                });

                $('#txtmandal').change(function() {
                    const mandalID = $(this).val();
                    $('#txtgram').html('<option value="">Loading Gram...</option>');
                    $('#txtpolling').html('<option value="">Loading Polling...</option>');
                    $.get('/operator/get-nagar/' + mandalID, function(data) {
                        $('#txtgram').html(data);
                    });
                    $.get('/operator/get-polling/' + mandalID, function(data) {
                        $('#txtpolling').html(data);
                    });
                });

                $('#txtpolling').change(function() {
                    const pollingID = $(this).val();
                    $('#txtarea').html('<option value="">Loading...</option>');
                    $.get('/operator/get-area/' + pollingID, function(data) {
                        $('#txtarea').html(data);
                    });
                });
            });
        </script>
    @endpush


@endsection
