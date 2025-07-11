@php
    $pageTitle = 'दायित्व कार्यकर्ता देखे';
    $breadcrumbs = [
        'एडमिन' => '#',
        'दायित्व कार्यकर्ता देखे' => '#',
    ];
@endphp

@extends('layouts.app')
@section('title', 'View Responsibility Member')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">

                    {{-- Success messages --}}
                    @foreach (['success', 'update_msg', 'delete_msg'] as $msg)
                        @if (session($msg))
                            <div class="alert alert-{{ $msg == 'delete_msg' ? 'danger' : 'success' }} alert-dismissible fade show">
                                {{ session($msg) }}
                                <button type="button" class="close" data-dismiss="alert">&times;</button>
                            </div>
                        @endif
                    @endforeach

                    <div class="table-responsive">
                        <table id="example" class="display table-bordered" style="min-width: 845px;">
                            <thead>
                                <tr>
                                    <th>क्रमांक</th>
                                    <th>नाम</th>
                                    <th>पता</th>
                                    <th>फ़ोटो</th>
                                    <th>कार्य क्षेत्र</th>
                                    <th>दायित्व</th>
                                    <th>दायित्व क्षेत्र</th>
                                    <th>Action</th>
                                    <th>Edit</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($assignments as $index => $assign)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ $assign->member->name ?? '' }}</td>
                                        <td>{{ $assign->addressInfo->permanent_address ?? '' }}</td>
                                        <td>
                                            <img src="{{ asset('assets/upload/' . ($assign->member->photo ?? 'default.png')) }}"
                                                 width="100" height="100" alt="Photo">
                                        </td>
                                        <td>{{ $assign->level_name ?? '' }}</td>
                                        <td>{{ $assign->position->position_name ?? '' }}</td>
                                        <td>{{ $assign->district->district_name ?? '' }}</td>
                                        <td>
                                            <div class="d-flex">
                                                <a href="{{ route('register.show', $assign->member->registration_id) }}" class="btn btn-success btn-sm mr-2">View</a>
                                                <a href="{{ route('register.card', $assign->member->registration_id) }}" class="btn btn-warning btn-sm mr-2" target="_blank">IDCard</a>
                                                <form action="{{ route('assign.destroy', $assign->assign_position_id) }}" method="POST">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button class="btn btn-danger btn-sm" type="submit">Delete</button>
                                                </form>
                                            </div>
                                        </td>
                                        <td>
                                            <a href="#" class="btn btn-primary btn-sm chk"
                                               data-id="{{ $assign->member_id }}"
                                               data-assign-id="{{ $assign->assign_position_id }}"
                                               data-toggle="modal"
                                               data-target="#assignModal">Edit</a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="9">No responsibilities assigned.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Assign Modal --}}
    <div class="modal fade" id="assignModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg">
            <form method="POST" id="assignForm" action="">
                @csrf
                <div class="modal-content">
                    <div class="modal-header">
                        <h5>दायित्व अपडेट करे</h5>
                        <button type="button" class="btn btn-danger" data-dismiss="modal">×</button>
                    </div>
                    <div class="modal-body">
                        
                        <div class="form-group">
                            <label>कार्य क्षेत्र</label>
                            <select id="workarea" name="workarea" class="form-control" required>
                                <option value="">--चुनें--</option>
                                @foreach (DB::table('level_master')->get() as $level)
                                    <option value="{{ $level->level_name }}">{{ $level->level_name }}</option>
                                @endforeach
                            </select>
                        </div>

                      
                        <div class="row">
                            <div class="col-md-3" id="pradesh" style="display:none;">
                                <label>प्रदेश</label>
                                <select name="txtpradesh" class="form-control" required>
                                    <option value="मध्य प्रदेश">मध्य प्रदेश</option>
                                </select>
                            </div>

                            <div class="col-md-3" id="district" style="display:none;">
                                <label>जिला</label>
                                <select id="txtdistrict" name="txtdistrict" class="form-control">
                                    <option value="">--चुनें--</option>
                                    @foreach (DB::table('district_master')->where('division_id', 2)->get() as $d)
                                        <option value="{{ $d->district_id }}">{{ $d->district_name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-3" id="vidhansabha" style="display:none;">
                                <label>विधानसभा</label>
                                <select name="txtvidhansabha" id="txtvidhansabha" class="form-control"></select>
                            </div>

                            <div class="col-md-3" id="mandal" style="display:none;">
                                <label>मंडल</label>
                                <select name="txtmandal" id="txtmandal" class="form-control"></select>
                            </div>

                            <div class="col-md-3" id="gram" style="display:none;">
                                <label>कमाण्ड ऐरिया</label>
                                <select name="txtgram" id="txtgram" class="form-control"></select>
                            </div>

                            <div class="col-md-3" id="polling" style="display:none;">
                                <label>मतदान केंद्र</label>
                                <select name="txtpolling" id="txtpolling" class="form-control"></select>
                            </div>

                            <div class="col-md-3" id="area" style="display:none;">
                                <label>चौपाल</label>
                                <select name="area_name" id="area_name" class="form-control"></select>
                            </div>
                        </div>

                        {{-- Position & Dates --}}
                        <div class="form-group mt-3">
                            <label>दायित्व</label>
                            <select name="position_id" class="form-control" required>
                                <option value="">--चुनें--</option>
                                @foreach (DB::table('position_master')->get() as $pos)
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
$(document).ready(function () {
    $(document).on('click', '.chk', function () {
        const memberId = $(this).data('id');
        const assignPositionId = $(this).data('assign-id');

        $('#member_id').val(memberId);
        $('#assignForm').attr('action', '/admin/responsibility/update/' + assignPositionId);

        $('#txtvidhansabha, #txtmandal, #txtgram, #txtpolling, #area_name').html('<option value="">--चुनें--</option>');

        $.ajax({
            url: '/admin/fetch-responsibility/' + memberId,
            type: 'GET',
            success: function (data) {
                $('#workarea').val(data.workarea).trigger('change');
                $('select[name="position_id"]').val(data.position_id);
                $('input[name="from"]').val(data.from);
                $('input[name="to"]').val(data.to);
                $('#txtdistrict').val(data.district_id);

                loadVidhansabha(data.district_id, data.vidhansabha, () => {
                    loadMandal(data.vidhansabha, data.mandal, () => {
                        loadGram(data.mandal, data.gram, () => {
                            loadPolling(data.gram, data.polling, () => {
                                loadArea(data.polling, data.area, () => {
                                    $('#assignModal').modal('show');
                                });
                            });
                        });
                    });
                });
            },
            error: function () {
                alert('Failed to fetch data');
            }
        });
    });

    function loadVidhansabha(districtId, selectedId, callback) {
        $.get('/admin/get-vidhansabha/' + districtId, function (res) {
            $('#txtvidhansabha').html('<option value="">--चुनें--</option>');
            res.forEach(v => {
                $('#txtvidhansabha').append(`<option value="${v.vidhansabha_id}">${v.vidhansabha}</option>`);
            });
            $('#txtvidhansabha').val(String(selectedId)).trigger('change');
            if (callback) callback();
        });
    }

    function loadMandal(vidhansabhaId, selectedId, callback) {
        $.get('/admin/get-mandal/' + vidhansabhaId, function (res) {
            $('#txtmandal').html('<option value="">--चुनें--</option>');
            res.forEach(m => {
                $('#txtmandal').append(`<option value="${m.mandal_id}">${m.mandal_name}</option>`);
            });
            $('#txtmandal').val(String(selectedId)).trigger('change');
            if (callback) callback();
        });
    }

    function loadGram(mandalId, selectedId, callback) {
        $.get('/admin/get-nagar/' + mandalId, function (res) {
            $('#txtgram').html('<option value="">--चुनें--</option>');
            res.forEach(g => {
                $('#txtgram').append(`<option value="${g.nagar_id}">${g.nagar_name}</option>`);
            });
            $('#txtgram').val(String(selectedId)).trigger('change');
            if (callback) callback();
        });
    }

    function loadPolling(gramId, selectedId, callback) {
        $.get('/admin/get-polling/' + gramId, function (res) {
            $('#txtpolling').html('<option value="">--चुनें--</option>');
            res.forEach(p => {
                $('#txtpolling').append(`<option value="${p.gram_polling_id}">${p.polling_name}</option>`);
            });
            $('#txtpolling').val(String(selectedId)).trigger('change');
            if (callback) callback();
        });
    }

    function loadArea(pollingId, selectedId, callback) {
        $.get('/admin/get-area/' + pollingId, function (res) {
            $('#area_name').html('<option value="">--चुनें--</option>');
            res.forEach(a => {
                $('#area_name').append(`<option value="${a.area_id}">${a.area_name}</option>`);
            });
            $('#area_name').val(String(selectedId));
            if (callback) callback();
        });
    }

$('#txtvidhansabha').on('change', function () {
    const vidhansabhaId = $(this).val();
    $('#txtmandal').html('<option value="">--चुनें--</option>');
    $('#txtgram, #txtpolling, #area_name').html('<option value="">--चुनें--</option>');
    if (vidhansabhaId) loadMandal(vidhansabhaId);
});

$('#txtmandal').on('change', function () {
    const mandalId = $(this).val();
    $('#txtgram, #txtpolling, #area_name').html('<option value="">--चुनें--</option>');
    if (mandalId) loadGram(mandalId);
});

$('#txtgram').on('change', function () {
    const gramId = $(this).val();
    $('#txtpolling, #area_name').html('<option value="">--चुनें--</option>');
    if (gramId) loadPolling(gramId);
});

$('#txtpolling').on('change', function () {
    const pollingId = $(this).val();
    $('#area_name').html('<option value="">--चुनें--</option>');
    if (pollingId) loadArea(pollingId);
});

    $('#workarea').on('change', function () {
        const val = $(this).val();
        const fields = ['#pradesh', '#district', '#vidhansabha', '#mandal', '#gram', '#polling', '#area'];
        fields.forEach(f => $(f).hide().find('select').removeAttr('required'));

        switch (val) {
            case 'प्रदेश': $('#pradesh').show().find('select').attr('required', true); break;
            case 'जिला': $('#district').show().find('select').attr('required', true); break;
            case 'विधानसभा': $('#vidhansabha').show().find('select').attr('required', true); break;
            case 'मंडल': $('#vidhansabha, #mandal').show(); $('#txtvidhansabha, #txtmandal').attr('required', true); break;
            case 'कमाण्ड ऐरिया':
            case 'कमाण्ड ऐरिया': $('#vidhansabha, #mandal, #gram').show(); $('#txtvidhansabha, #txtmandal, #txtgram').attr('required', true); break;
            case 'ग्राम/वार्ड चौपाल': $('#vidhansabha, #mandal, #gram, #polling, #area').show(); $('#txtvidhansabha, #txtmandal, #txtgram, #txtpolling, #area_name').attr('required', true); break;
        }
    });
});
</script>
@endpush
@endsection
