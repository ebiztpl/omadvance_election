@php
$pageTitle = 'दायित्व नियुक्त करना';
$breadcrumbs = [
'एडमिन' => '#',
'दायित्व नियुक्त करना' => '#'
];
@endphp

@extends('layouts.app')
@section('title', 'Assign Responsibility')


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

    <div class="row page-titles mx-0">
        <div class="col-md-12 col-sm-12 col-xs-12">
            <form method="post" id="filter_form" class="mb-5">
                @csrf

                <div id="rowGroup">
                    <div class="form-row align-items-end mb-2">
                        <div class="col-md-2">
                            <label>संकल्प पत्र क्र./मोबाइल</label>
                            <input type="text" name="mobile" id="main_mobile" class="form-control">
                        </div>
                        <div class="col-md-2">
                            <label>शैक्षणिक योग्यता</label>
                            <select name="education" id="education" class="form-control">
                                <option value="">--चुनें--</option>
                                @foreach([
                                'साक्षर/निरक्षर', 'प्राथमिक शिक्षा', 'माध्यमिक शिक्षा', '10th',
                                '12th', 'स्नातक', 'स्नातकोत्तर', 'डॉक्टरेट या उच्चतर', 'डिप्लोमा', 'अन्य'
                                ] as $option)
                                <option value="{{ $option }}">{{ $option }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-2">
                            <label>श्रेणी</label>
                            <select id="category" name="category" class="form-control">
                                <option value="">--चुनें--</option>
                                @foreach(['सामान्य', 'पिछड़ा वर्ग', 'अनुसूचित जाति', 'अनुसूचित जनजाति', 'अन्य'] as $cat)
                                <option value="{{ $cat }}">{{ $cat }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-2">
                            <label>व्यवसाय</label>
                            <select name="business" id="business" class="form-control">

                                <option value="">--चुनें--</option>
                                @foreach([
                                'शासकीय नौकरी', 'अशासकीय नौकरी', 'बिजनेस', 'कृषि', 'बेरोजगार',
                                'गृहिणी', 'विद्यार्थी', 'स्वरोजगार', 'समाजसेवा', 'पत्रकार', 'अन्य'
                                ] as $job)
                                <option value="{{ $job }}">{{ $job ?: '--चुनें--' }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-2">
                            <label>जिला</label>
                            <select class="form-control" id="searchdistrict" name="district_id">
                                <option value="">--चुनें--</option>
                                @foreach($districts as $district)
                                <option value="{{ $district->district_id }}">{{ $district->district_name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-2">
                            <label>विधानसभा का नाम</label>
                            <select name="vidhansabha_id" class="form-control" id="searchvidhansabha">
                                <option value="">--चुनें--</option>
                            </select>
                        </div>


                        <div class="col-md-6 mt-2" style="color:rgb(55, 64, 75)">
                            <br />
                            <button type="button" id="filter_data" class="btn btn-success mr-4">Filter Data</button>
                            Filter Data Count: <span id="total">0</span>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card" id="table_card" style="display: none;">
                <div class="card-body">
                    <div id="filtered_data" class="table-responsive">

                    </div>
                </div>
            </div>
        </div>
    </div>



    <!-- Assign Modal -->
    <div class="modal fade" id="assignModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg">
            <form method="POST" action="{{ route('responsibility.store') }}">
                @csrf
                <div class="modal-content">
                    <div class="modal-header">
                        <h5>दायित्व देना</h5>
                        <button type="button" class="btn btn-danger" data-dismiss="modal">×</button>
                    </div>
                    <div class="modal-body">
                        <!-- Work Area Selection -->
                        <div class="form-group">
                            <label>कार्य क्षेत्र</label>
                            <select id="workarea" name="workarea" class="form-control" required>
                                <option value="">--चुनें--</option>
                                @foreach(DB::table('level_master')->get() as $level)
                                <option value="{{ $level->level_name }}">{{ $level->level_name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Conditional Fields -->
                        <div class="row">
                            <div class="col-md-3" id="pradesh" style="display:none;">
                                <label>प्रदेश</label>
                                <select name="txtpradesh" class="form-control">
                                    <option value="">--चुनें--</option>
                                    <option value="मध्य प्रदेश">मध्य प्रदेश</option>
                                </select>
                            </div>

                            <div class="col-md-3" id="district" style="display:none;">
                                <label>जिला</label>
                                <select id="txtdistrict" name="txtdistrict" class="form-control">
                                    <option value="">--चुनें--</option>
                                    @foreach(DB::table('district_master')->where('division_id', 2)->get() as $d)
                                    <option value="{{ $d->district_id }}">{{ $d->district_name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-3" id="vidhansabha" style="display:none;">
                                <label>विधानसभा</label>
                                <select name="txtvidhansabha" id="txtvidhansabha" class="form-control"></select>
                            </div>

                            <!-- MANDAL -->
                            <div class="col-md-3" id="mandal" style="display:none;">
                                <label>मंडल</label>
                                <select name="txtmandal" id="txtmandal" class="form-control"></select>
                            </div>

                            <!-- GRAM -->
                            <div class="col-md-3" id="gram" style="display:none;">
                                <label>नगर केंद्र/ग्राम केंद्र</label>
                                <select name="txtgram" id="txtgram" class="form-control"></select>
                            </div>

                            <!-- POLLING -->
                            <div class="col-md-3" id="polling" style="display:none;">
                                <label>मतदान केंद्र</label>
                                <select name="txtpolling" id="txtpolling" class="form-control"></select>
                            </div>

                            <!-- AREA -->
                            <div class="col-md-3" id="area" style="display:none;">
                                <label>चौपाल</label>
                                <select name="area_name" id="area_name" class="form-control"></select>
                            </div>
                        </div>

                        <!-- Position & Date -->
                        <div class="form-group mt-3">
                            <label>दायित्व</label>
                            <select name="position_id" class="form-control" required>
                                <option value="">--चुनें--</option>
                                @foreach(DB::table('position_master')->get() as $pos)
                                <option value="{{ $pos->position_id }}">{{ $pos->position_name }} ({{ $pos->level }})</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <label>कार्यकाल प्रारंभ</label>
                                <input type="date" name="from" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label>कार्यकाल समाप्त</label>
                                <input type="date" name="to" class="form-control" value="2026-12-31" required>
                            </div>
                        </div>

                        <input type="hidden" name="member_id" id="member_id">
                        <input type="hidden" id="selected_district" value="{{ request()->district_id }}">
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-success">Save</button>
                        <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
                    </div>
                </div>
            </form>
        </div>
    </div>




</div>

@push('scripts')
<script>
    $('#searchdistrict').on('change', function() {
        let id = $(this).val();
        $.post('{{ url("/get-vidhansabha") }}', {
            id: id,
            _token: '{{ csrf_token() }}'
        }, function(data) {
            $('#searchvidhansabha').html(data);
        });
    });


    $("#filter_data").click(function(e) {
        e.preventDefault();
        $(".loader").show();

        const data = {
            _token: '{{ csrf_token() }}',
            mobile: $("#main_mobile").val(),
            education: $("#education").val(),
            category: $("#category").val(),
            business: $("#business").val(),
            district_id: $("#searchdistrict").val(),
            vidhansabha_id: $("#searchvidhansabha").val()
        };

        $.ajax({
            url: "{{ route('responsibility.filter') }}",
            type: "POST",
            data: data,
            success: function(response) {
                if (response.count > 0) {
                    $("#filtered_data").html(response.html);
                    $('#example').DataTable({
                        destroy: true,
                        responsive: true
                    });
                    $("#table_card").show();
                } else {
                    $("#filtered_data").html('<div class="text-danger">No data found.</div>');
                    $("#table_card").show();
                }

                $("#total").text(response.count);
                $(".loader").hide();
            },
            error: function(xhr) {
                $(".loader").hide();
                console.error(xhr.responseText);
                alert("Something went wrong. Check console.");
            }
        });
    });

    $(document).on('click', '.chk', function() {
        const registrationId = $(this).data('id');

        $.ajax({
            url: '/admin/fetch-location/' + registrationId,
            type: 'GET',
            success: function(data) {
                const districtId = data.district_id;
                const vidhansabhaId = data.vidhansabha;

                $('#txtdistrict').val(districtId);

                // Load Vidhansabha based on district
                $.get('/admin/get-vidhansabha/' + districtId, function(res) {
                    $('#txtvidhansabha').html('<option value="">--चुनें--</option>');
                    res.forEach(v => {
                        $('#txtvidhansabha').append(`<option value="${v.vidhansabha_id}">${v.vidhansabha}</option>`);
                    });
                    $('#txtvidhansabha').val(vidhansabhaId);
                    $('#txtvidhansabha').trigger('change'); // Trigger to load mandal
                });
            },
            error: function() {
                alert('Failed to fetch location.');
            }
        });
    });


    $('#txtvidhansabha').on('change', function() {
        const vidhansabhaId = $(this).val();
        $('#txtmandal').html('<option value="">--चुनें--</option>');
        $('#txtgram').html('<option value="">--चुनें--</option>'); // Clear nagar

        if (vidhansabhaId) {
            $.get('/admin/get-mandal/' + vidhansabhaId, function(mandals) {
                mandals.forEach(m => {
                    $('#txtmandal').append(`<option value="${m.mandal_id}">${m.mandal_name}</option>`);
                });
            });
        }
    });


    $('#txtmandal').on('change', function() {
        const mandalId = $(this).val();
        $('#txtgram').html('<option value="">--चुनें--</option>');
        $('#txtpolling').html('<option value="">--चुनें--</option>');
        $('#area_name').html('<option value="">--चुनें--</option>');

        if (mandalId) {
            $.get('/admin/get-nagar/' + mandalId, function(nagars) {
                nagars.forEach(n => {
                    $('#txtgram').append(`<option value="${n.nagar_id}">${n.nagar_name}</option>`);
                });
            });
        }
    });


    $('#txtgram').on('change', function() {
        const gramId = $(this).val();
        $('#txtpolling').html('<option value="">--चुनें--</option>');
        $('#area_name').html('<option value="">--चुनें--</option>');

        if (gramId) {
            $.get('/admin/get-polling/' + gramId, function(pollings) {
                pollings.forEach(p => {
                    $('#txtpolling').append(`<option value="${p.gram_polling_id}">${p.polling_name}</option>`);
                });
            });
        }
    });

    $('#txtpolling').on('change', function() {
        const pollingId = $(this).val();
        console.log("Polling selected:", pollingId);
        $('#area_name').html('<option value="">--चुनें--</option>');

        if (pollingId) {
            $.get('/admin/get-area/' + pollingId, function(areas) {
                console.log("Areas fetched:", areas);
                areas.forEach(a => {
                    $('#area_name').append(`<option value="${a.area_id}">${a.area_name}</option>`);
                });
            });
        }
    });

    $('#workarea').on('change', function() {
        const val = $(this).val();

        const fields = ['#pradesh', '#district', '#vidhansabha', '#mandal', '#gram', '#polling', '#area'];
        fields.forEach(f => $(f).hide().find('select').removeAttr('required'));

        switch (val) {
            case 'प्रदेश':
                $('#pradesh').show().find('select').attr('required', true);
                break;
            case 'जिला':
                $('#district').show().find('select').attr('required', true);
                break;
            case 'विधानसभा':
                $('#vidhansabha').show().find('select').attr('required', true);
                break;
            case 'मंडल':
                $('#vidhansabha, #mandal').show();
                $('#txtvidhansabha, #txtmandal').attr('required', true);
                break;
            case 'नगर केंद्र/ग्राम केंद्र':
                $('#vidhansabha, #mandal, #gram').show();
                $('#txtvidhansabha, #txtmandal, #txtgram').attr('required', true);
                break;
            case 'ग्राम/वार्ड चौपाल':
                $('#vidhansabha, #mandal, #gram, #polling, #area').show();
                $('#txtvidhansabha, #txtmandal, #txtgram, #txtpolling, #area_name').attr('required', true);
                break;
        }
    });

    $(document).on('click', '.chk', function() {
        const memberId = $(this).data('id');
        $('#member_id').val(memberId);
        $('#assignModal').modal('show');
    });
</script>
@endpush

@endsection