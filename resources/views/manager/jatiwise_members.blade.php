@php
$pageTitle = 'जातिगत मतदाता देखे';
$breadcrumbs = [
'मैनेजर' => '#',
'जातिगत मतदाता देखे' => '#'
];
@endphp

@extends('layouts.app')
@section('title', 'Jatiwise Members')

@section('content')
<div class="container">
    <div class="row page-titles mx-0">
        <div class="col-md-12 col-sm-12 col-xs-12">
            <form id="filterForm">
                @csrf
                <div class="item form-group row" id="subjectSection">
                    <div class="col-md-3 col-sm-3 col-xs-12">
                        <label class="control-label">विधानसभा का नाम <span class="required">*</span></label>
                        <select name="txtvidhansabha" required class="form-control" id="txtvidhansabha">
                            <option value="">--Select--</option>
                            @foreach($vidhansabhas as $vidhansabha)
                            <option value="{{ $vidhansabha->vidhansabha_id }}">{{ $vidhansabha->vidhansabha }}</option>
                            @endforeach
                        </select>
                    </div>


                    <div class="col-md-3 col-sm-3 col-xs-12">
                        <label class="control-label">मंडल का नाम <span class="required">*</span></label>
                        <select name="txtmandal" required class="form-control" id="txtmandal">
                            <option value="">--Select--</option>
                        </select>
                    </div>


                    <div class="col-md-3 col-sm-3 col-xs-12">
                        <label class="control-label">कमाण्ड ऐरिया <span class="required">*</span></label>
                        <select name="txtgram" required class="form-control" id="txtgram">
                            <option value="">--Select--</option>
                        </select>
                    </div>


                    <div class="col-md-3 col-sm-3 col-xs-12">
                        <label class="control-label">मतदान केंद्र का नाम/क्रमांक <span class="required">*</span></label>
                        <select name="txtpolling" required class="form-control" id="txtpolling">
                            <option value="">--Select--</option>
                        </select>
                    </div>

                    <!-- Buttons -->
                    <div class="col-md-3">
                        <br />
                        <button type="button" class="btn btn-success" id="filter_data">
                            <i class="fa fa-search"></i> Filter Data
                        </button>

                        <input type="button" id="btn" class="btn btn-danger" value="Print" onclick="printFunc('content')">
                    </div>
                </div>
            </form>
        </div>
    </div>


    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body" id="jatiwisedata" style="display: none;">
                    <div class="table-responsive">
                        <div id="content" style="height: 650px; overflow-y: scroll;">

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


@push('scripts')
<script>
    $("#filter_data").click(function() {
        $("#loader-wrapper").show();

        var data = {
            _token: '{{ csrf_token() }}',
            vidhansabha_id: $("#txtvidhansabha").val(),
            mandal_id: $("#txtmandal").val(),
            gram_id: $("#txtgram").val(),
            polling_id: $("#txtpolling").val(),
        };

        $.post("{{ route('jatiwise.filter') }}", data, function(response) {
            if (response.trim() !== "") {
                $("#content").html(response);
                $("#jatiwisedata").slideDown();
            } else {
                $("#content").html("<p class='text-center'>No data found.</p>");
                $("#jatiwisedata").slideDown();
            }
            $("#loader-wrapper").hide();
        });
    });


    $('#txtvidhansabha').on('change', function() {
        let id = $(this).val();
        $.post('{{ route("jatiwise.dropdown") }}', {
            _token: '{{ csrf_token() }}',
            type: 'mandal',
            id: id
        }, function(data) {
            $('#txtmandal').html('<option value="">--Select--</option>');
            data.forEach(function(item) {
                $('#txtmandal').append(`<option value="${item.mandal_id}">${item.mandal_name}</option>`);
            });
        });
    });

    $('#txtmandal').on('change', function() {
        let id = $(this).val();
        $.post('{{ route("jatiwise.dropdown") }}', {
            _token: '{{ csrf_token() }}',
            type: 'gram',
            id: id
        }, function(data) {
            $('#txtgram').html('<option value="">--Select--</option>');
            data.forEach(function(item) {
                $('#txtgram').append(`<option value="${item.nagar_id}">${item.nagar_name}</option>`);
            });
        });
    });

    $('#txtgram').on('change', function() {
        let id = $(this).val();
        $.post('{{ route("jatiwise.dropdown") }}', {
            _token: '{{ csrf_token() }}',
            type: 'polling',
            id: id
        }, function(data) {
            $('#txtpolling').html('<option value="">--Select--</option>');
            data.forEach(function(item) {
                $('#txtpolling').append(`<option value="${item.gram_polling_id}">${item.polling_name} - ${item.polling_no}</option>`);
            });
        });
    });


    function printFunc(tagid) {
        var hashid = "#" + tagid;
        var tagname = $(hashid).prop("tagName").toLowerCase();
        var attributes = "";
        var attrs = document.getElementById(tagid).attributes;

        $.each(attrs, function(i, elem) {
            attributes += " " + elem.name + "='" + elem.value + "' ";
        });

        var divToPrint = $(hashid).html();
        var head = "<html><head>" + $("head").html() + "</head>";
        var allcontent = head + "<body onload='window.print()'><" +
            tagname + attributes + ">" + divToPrint + "</" + tagname + "></body></html>";

        var newWin = window.open('', 'Print-Window');
        newWin.document.open();
        newWin.document.write(allcontent);
        newWin.document.close();
    }
</script>
</script>
@endpush
@endsection