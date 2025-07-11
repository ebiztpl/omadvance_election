@php
$pageTitle = 'समस्या पंजीयन करे';
$breadcrumbs = [
'मेंबर' => '#',
'समस्या पंजीयन करे' => '#'
];
@endphp

@extends('layouts.app')
@section('title', 'Register complaint')

@section('content')
<div class="container">

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
    @endif

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
            <form method="POST" enctype="multipart/form-data" action="{{ route('complaint.store') }}">
                @csrf
                {{-- Type Selector --}}
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
                            @foreach($types as $type => $data)
                            <label class="btn btn-success text-white m-1">
                                <input type="radio"
                                    name="type"
                                    value="{{ $type }}"
                                    class="check"
                                    data-text="{{ $data['text'] }}"
                                    data-legend="{{ $data['heading'] }}"
                                    style="width: 25px; height: 25px;" required>
                                <br>{{ $type }}
                            </label>
                            @endforeach
                        </div>
                    </div>
                </div>

                {{-- Dynamic Heading/Legend --}}
                <div id="form_container" style="display: none; color: #000">
                    <fieldset class="scheduler-border mb-3">
                        {{-- Name, Mobile, Address Info --}}
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
                                <label>संभाग का नाम <span class="error">*</span></label>
                                <select class="form-control" name="division_id" id="division_id" required>
                                    <option value="">--Select--</option>
                                    @foreach($divisions as $division)
                                    <option value="{{ $division->division_id }}">{{ $division->division_name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-4 mb-3">
                                <label>जिले का नाम <span class="error">*</span></label>
                                <select class="form-control" name="txtdistrict_name" id="district_name" required></select>
                            </div>

                            <div class="col-md-4 mb-3">
                                <label>विधानसभा का नाम <span class="error">*</span></label>
                                <select name="txtvidhansabha" class="form-control" id="txtvidhansabha" required></select>
                            </div>

                            <div class="col-md-4 mb-3">
                                <label>मंडल का नाम <span class="error">*</span></label>
                                <select name="txtmandal" class="form-control" id="txtmandal" required></select>
                            </div>

                            <div class="col-md-4 mb-3">
                                <label>कमाण्ड ऐरिया का नाम <span class="error">*</span></label>
                                <select name="txtgram" class="form-control" id="txtgram" required></select>
                            </div>

                            <div class="col-md-4 mb-3">
                                <label>मतदान केंद्र का नाम/क्रमांक <span class="error">*</span></label>
                                <select name="txtpolling" class="form-control" id="txtpolling" required></select>
                            </div>

                            <div class="col-md-4 mb-3">
                                <label>ग्राम चौपाल/वार्ड चौपाल का नाम <span class="error">*</span></label>
                                <select name="txtarea" class="form-control" id="txtarea" required></select>
                            </div>
                        </div>

                        <fieldset class="scheduler-border fieldset-bordered">
                            <legend class="scheduler-border dynamic-legend">विवरण -</legend>

                            {{-- Department Row --}}
                            <div class="form-group row department_row">
                                <div class="col-md-6 mb-3">
                                    <label>विभाग <span class="error">*</span></label>
                                    <select name="department" class="form-control" required}}>
                                        <option value="">--चुने--</option>
                                        @foreach(['राजस्व विभाग', 'विद्युत विभाग', 'सहकारिता', 'पंचायत', 'पी.एच.ई.', 'नगरीय निकाय', 'पुलिस', 'सिंचाई', 'स्वास्थ्य विभाग', 'पी.डब्ल्यू.डी.', 'खाद्य'] as $dept)
                                        <option value="{{ $dept }}">{{ $dept }}</option>
                                        @endforeach
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
                                    <input type="text" class="form-control" name="CharCounter" maxlength="100" required>
                                </div>

                                <div class="col-md-12 mb-3">
                                    <label>विवरण <span class="error">*</span></label>
                                    <textarea class="form-control" name="NameText" rows="5" maxlength="2000" required></textarea>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label>फाइल संलग्न करें</label>
                                    <input type="file" class="form-control" name="file_attach">
                                </div>
                            </div>
                        </fieldset>
                    </fieldset>


                    <input class="btn btn-primary" type="submit" value="समस्या भेजे" style="background-color:blue; color:#fff; width:100%; height:50px;">

                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    $(document).ready(function() {

        $('#division_id').change(function() {
            let divisionID = $(this).val();
            $('#district_name').html('<option value="">Loading...</option>');
            $.get('/get-districts/' + divisionID, function(data) {
                $('#district_name').html(data);
            });
        });

        $('#district_name').change(function() {
            let districtID = $(this).val();
            $('#txtvidhansabha').html('<option value="">Loading...</option>');
            $.get('/get-vidhansabha/' + districtID, function(data) {
                $('#txtvidhansabha').html(data);
            });
        });

        $('#txtvidhansabha').change(function() {
            let vidhansabhaID = $(this).val();
            $('#txtmandal').html('<option value="">Loading...</option>');
            $.get('/get-mandal/' + vidhansabhaID, function(data) {
                $('#txtmandal').html(data);
            });
        });

        $('#txtmandal').change(function() {
            let mandalID = $(this).val();
            $('#txtgram').html('<option value="">Loading...</option>');
            $.get('/get-nagar/' + mandalID, function(data) {
                $('#txtgram').html(data);
            });
            $.get('/get-polling/' + mandalID, function(data) {
                $('#txtpolling').html(data);
            });
        });

        $('#txtpolling').change(function() {
            let pollingID = $(this).val();
            $('#txtarea').html('<option value="">Loading...</option>');
            $.get('/get-area/' + pollingID, function(data) {
                $('#txtarea').html(data);
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
                $(".date_row").show();
            } else {
                $(".department_row").show();
                $(".date_row").hide();
            }
        });

        if ($(".check:checked").length > 0) {
            $(".check:checked").trigger("change");
        }
    });
</script>
@endpush
@endsection