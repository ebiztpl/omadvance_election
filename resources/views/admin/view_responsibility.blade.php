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

                        @foreach (['success', 'update_msg', 'delete_msg'] as $msg)
                            @if (session($msg))
                                <div
                                    class="alert alert-{{ $msg == 'delete_msg' ? 'danger' : 'success' }} alert-dismissible fade show">
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
                                        <?php
                                        $level = $assign->level_name;
                                        $refId = $assign->refrence_id;
                                        switch ($level) {
                                            case 'प्रदेश':
                                                $workarea_name = 'मध्य प्रदेश';
                                                break;
                                        
                                            case 'जिला':
                                                $district_id = $refId;
                                                $district = DB::table('district_master')->where('district_id', $district_id)->value('district_name');
                                                $workarea_name = $district;
                                                break;
                                        
                                            case 'विधानसभा':
                                                $vidhansabha_id = $refId;
                                                $vidhansabha = DB::table('vidhansabha_loksabha')->where('vidhansabha_id', $vidhansabha_id)->value('vidhansabha');
                                                $workarea_name = $vidhansabha;
                                                break;
                                        
                                            case 'मंडल':
                                                $mandal = DB::table('mandal')->where('mandal_id', $refId)->first();
                                                $mandal_id = $mandal->mandal_id;
                                                $vidhansabha_id = $mandal->vidhansabha_id;
                                                $workarea_name = "मंडल : $mandal->mandal_name";
                                                break;
                                        
                                            case 'कमाण्ड ऐरिया':
                                                $nagar = DB::table('nagar_master')->where('nagar_id', $refId)->first();
                                                $gram_id = $nagar->nagar_id;
                                                $mandal_id = $nagar->mandal_id;
                                                $mandal = DB::table('mandal')->where('mandal_id', $mandal_id)->first();
                                                $vidhansabha_id = $mandal->vidhansabha_id;
                                                $workarea_name = "नगर केंद्र/ग्राम केंद्र : $nagar->nagar_name, मंडल : $mandal->mandal_name";
                                                break;
                                        
                                            case 'ग्राम/वार्ड चौपाल':
                                                $area = DB::table('area_master')->where('area_id', $refId)->first();
                                                $polling_id = $area->polling_id;
                                                $polling = DB::table('gram_polling')->join('nagar_master', 'gram_polling.nagar_id', '=', 'nagar_master.nagar_id')->where('gram_polling.gram_polling_id', $polling_id)->select('gram_polling.gram_polling_id', 'nagar_master.nagar_name', 'nagar_master.mandal_id', 'nagar_master.nagar_id')->first();
                                        
                                                $mandal_id = $polling->mandal_id;
                                                $gram_id = $polling->nagar_id;
                                                $mandal = DB::table('mandal')->where('mandal_id', $mandal_id)->first();
                                                $vidhansabha_id = $mandal->vidhansabha_id;
                                                $area_id = $refId;
                                        
                                                $workarea_name = "$level : $area->area_name,<br/>नगर केंद्र/ग्राम केंद्र : $polling->nagar_name,<br/>मंडल : $mandal->mandal_name";
                                                break;
                                        }
                                        
                                        ?>
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
                                            <td>{!! $workarea_name ?? '' !!}</td>
                                            <td>
                                                <div class="d-flex">
                                                    <a href="{{ route('register.show', $assign->member->registration_id) }}"
                                                        class="btn btn-success btn-sm mr-2">View</a>
                                                    <a href="{{ route('register.card', $assign->member->registration_id) }}"
                                                        class="btn btn-warning btn-sm mr-2" target="_blank">IDCard</a>
                                                    <form
                                                        action="{{ route('assign.destroy', $assign->assign_position_id) }}"
                                                        method="POST">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button class="btn btn-danger btn-sm" type="submit">Delete</button>
                                                    </form>
                                                </div>
                                            </td>
                                            <td>
                                                <a href="#" class="btn btn-primary btn-sm chk"
                                                    data-id="{{ $assign->member_id }}"
                                                    data-assign-id="{{ $assign->assign_position_id }}" data-toggle="modal"
                                                    data-target="#assignModal">Edit</a>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="9">No responsibilities assigned.</td>
                                        </tr>
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
                                    <div class="d-flex justify-content-between align-items-center">
                                        <label class="mb-0">नगर केंद्र/ग्राम केंद्र</label>
                                        <button type="button" class="btn btn-success btn-sm ml-2" id="addGramBtn"
                                            title="Add Command Area">
                                            <i class="fa fa-plus"></i>
                                        </button>
                                    </div>
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
                                        <option value="{{ $pos->position_id }}">{{ $pos->position_name }}
                                            ({{ $pos->level }})
                                        </option>
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
                                    <input type="date" name="to" class="form-control" value="2026-12-31"
                                        required>
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






        <!-- command area add Modal -->
        <div class="modal fade" id="addGramModal" tabindex="-1" role="dialog">
            <div class="modal-dialog modal-lg">
                <form method="POST" id="addGramForm">
                    @csrf
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5>नया नगर केंद्र/ग्राम केंद्र जोड़ें</h5>
                            <button type="button" class="btn btn-danger" data-dismiss="modal">×</button>
                        </div>
                        <div class="modal-body">
                            <div class="form-group row">
                                <div class="col-md-3">
                                    <label>जिला</label>
                                    <select id="district_id" name="district_id" class="form-control" required>
                                        <option value="">--जिला चुनें--</option>
                                        @foreach ($districts as $d)
                                            <option value="{{ $d->district_id }}">{{ $d->district_name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label>विधानसभा</label>
                                    <select id="vidhansabha_id" name="vidhansabha_id" class="form-control"
                                        required></select>
                                </div>
                                <div class="col-md-3">
                                    <label>मंडल</label>
                                    <select name="txtmandal" id="mandal_id" class="form-control" required></select>
                                </div>

                                <div class="col-md-3">
                                    <label>मंडल का प्रकार</label>
                                    <select name="mandal_type" class="form-control" required>
                                        <option value="">--प्रकार चुनें--</option>
                                        <option value="1">ग्रामीण मंडल</option>
                                        <option value="2">नगर मंडल</option>
                                    </select>
                                </div>
                            </div>

                            <div id="modalRowGroup">
                                <div class="form-group row mb-2">
                                    <div class="col-md-4">
                                        <label>नगर केंद्र/ग्राम केंद्र का नाम</label>
                                        <input type="text" name="gram_name[]" class="form-control" required>
                                    </div>
                                    <div class="col-md-2">
                                        <button type="button" class="btn btn-primary add-row mt-4">
                                            <i class="fa fa-plus"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group row">
                                <div class="col-md-6">
                                    <button type="submit" class="btn btn-success">Save</button>
                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            $(document).ready(function() {
                $(document).on('click', '.chk', function() {
                    const memberId = $(this).data('id');
                    const assignPositionId = $(this).data('assign-id');

                    $('#member_id').val(memberId);
                    $('#assignForm').attr('action', '/admin/responsibility/update/' + assignPositionId);

                    $('#txtvidhansabha, #txtmandal, #txtgram, #txtpolling, #area_name').html(
                        '<option value="">--चुनें--</option>');

                    $.ajax({
                        url: '/admin/fetch-responsibility/' + memberId,
                        type: 'GET',
                        success: function(data) {
                            console.log(data)
                            $('#workarea').val(data.workarea).trigger('change');
                            $('select[name="position_id"]').val(data.position_id);
                            $('input[name="from"]').val(data.from);
                            $('input[name="to"]').val(data.to);
                            $('#txtdistrict').val(data.district_id);

                            const {
                                district_id,
                                vidhansabha_id,
                                mandal_id,
                                gram_id,
                                polling_id,
                                area_id
                            } = data;

                            loadVidhansabha(district_id, vidhansabha_id, () => {
                                if (mandal_id) {
                                    loadMandal(vidhansabha_id, mandal_id, () => {
                                        if (gram_id) {
                                            loadGram(mandal_id, gram_id, () => {
                                                if (polling_id) {
                                                    loadPolling(gram_id,
                                                        polling_id,
                                                        () => {
                                                            if (
                                                                area_id
                                                            ) {
                                                                loadArea
                                                                    (polling_id,
                                                                        area_id,
                                                                        () => {
                                                                            $('#assignModal')
                                                                                .modal(
                                                                                    'show'
                                                                                );
                                                                        }
                                                                    );
                                                            } else {
                                                                $('#assignModal')
                                                                    .modal(
                                                                        'show'
                                                                    );
                                                            }
                                                        });
                                                } else {
                                                    $('#assignModal').modal(
                                                        'show');
                                                }
                                            });
                                        } else {
                                            $('#assignModal').modal('show');
                                        }
                                    });
                                } else {
                                    $('#assignModal').modal('show');
                                }
                            });
                        },

                        error: function() {
                            alert('Failed to fetch data');
                        }
                    });
                });

                function loadVidhansabha(districtId, selectedId, callback) {
                    $.get('/admin/get-vidhansabha/' + districtId, function(res) {
                        console.log("Vidhansabha response:", res);
                        $('#txtvidhansabha').html('<option value="">--चुनें--</option>' + res.join(''));
                        $('#txtvidhansabha').val(String(selectedId)).trigger('change');
                        $("#txtvidhansabha option[value=selectedId]").prop('selected', true);
                        if (callback) callback();
                    });
                }

                function loadMandal(vidhansabhaId, selectedId, callback) {
                    console.log(selectedId);
                    $.get('/admin/get-mandal/' + vidhansabhaId, function(res) {
                        $('#txtmandal').html('<option value="">--चुनें--</option>' + res.join(''));

                        $('#txtmandal').val(String(selectedId)).trigger('change');
                        $("#txtmandal option[value=selectedId]").prop('selected', true);
                        if (callback) callback();
                    });
                }

                function loadGram(mandalId, selectedId, callback) {
                    $.get('/admin/get-nagar/' + mandalId, function(res) {
                        $('#txtgram').html('<option value="">--चुनें--</option>' + res.join(''));

                        $('#txtgram').val(String(selectedId)).trigger('change');
                        if (callback) callback();
                    });
                }

                function loadPolling(gramId, selectedId, callback) {
                    $.get('/admin/get-polling/' + gramId, function(res) {
                        $('#txtpolling').html('<option value="">--चुनें--</option>');
                        res.forEach(p => {
                            $('#txtpolling').append(
                                `<option value="${p.gram_polling_id}">${p.polling_name}</option>`);
                        });
                        $('#txtpolling').val(String(selectedId)).trigger('change');
                        if (callback) callback();
                    });
                }

                function loadArea(pollingId, selectedId, callback) {
                    $.get('/admin/get-area/' + pollingId, function(res) {
                        $('#area_name').html('<option value="">--चुनें--</option>');
                        res.forEach(a => {
                            $('#area_name').append(
                                `<option value="${a.area_id}">${a.area_name}</option>`);
                        });
                        $('#area_name').val(String(selectedId));
                        if (callback) callback();
                    });
                }

                $('#txtvidhansabha').on('change', function() {
                    const vidhansabhaId = $(this).val();
                    $('#txtmandal').html('<option value="">--चुनें--</option>');
                    $('#txtgram, #txtpolling, #area_name').html('<option value="">--चुनें--</option>');
                    if (vidhansabhaId) loadMandal(vidhansabhaId);
                });

                $('#txtmandal').on('change', function() {
                    const mandalId = $(this).val();
                    $('#txtgram, #txtpolling, #area_name').html('<option value="">--चुनें--</option>');
                    if (mandalId) loadGram(mandalId);
                });

                $('#txtgram').on('change', function() {
                    const gramId = $(this).val();
                    $('#txtpolling, #area_name').html('<option value="">--चुनें--</option>');
                    if (gramId) loadPolling(gramId);
                });

                $('#txtpolling').on('change', function() {
                    const pollingId = $(this).val();
                    $('#area_name').html('<option value="">--चुनें--</option>');
                    if (pollingId) loadArea(pollingId);
                });

                $('#workarea').on('change', function() {
                    const val = $(this).val();
                    const fields = ['#pradesh', '#district', '#vidhansabha', '#mandal', '#gram', '#polling',
                        '#area'
                    ];
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
                        case 'कमाण्ड ऐरिया':
                            $('#vidhansabha, #mandal, #gram').show();
                            $('#txtvidhansabha, #txtmandal, #txtgram').attr('required', true);
                            break;
                        case 'ग्राम/वार्ड चौपाल':
                            $('#vidhansabha, #mandal, #gram, #polling, #area').show();
                            $('#txtvidhansabha, #txtmandal, #txtgram, #txtpolling, #area_name').attr('required',
                                true);
                            break;
                    }
                });





                $('#district_id').on('change', function() {
                    const districtId = $(this).val();

                    $('#vidhansabha_id').html('<option value="">--चुनें--</option>');
                    $('#mandal_id').html('<option value="">--चुनें--</option>');

                    if (districtId) {
                        $.get('/get-vidhansabhas?district_id=' + districtId, function(res) {
                            res.forEach(v => {
                                $('#vidhansabha_id').append(
                                    `<option value="${v.vidhansabha_id}">${v.vidhansabha}</option>`
                                );
                            });
                        });
                    }
                });


                $(document).ready(function() {
                    $('#addGramBtn').on('click', function() {
                        $('#addGramModal').modal('show');

                        const districtId = $('#txtdistrict').val();
                        const vidhansabhaId = $('#txtvidhansabha').val();
                        const mandalId = $('#txtmandal').val();

                        $('#district_id').val(districtId).trigger('change');

                        if (districtId) {
                            $.get('/get-vidhansabhas?district_id=' + districtId, function(res) {
                                $('#vidhansabha_id').html(
                                    '<option value="">--चुनें--</option>');
                                res.forEach(v => {
                                    $('#vidhansabha_id').append(
                                        `<option value="${v.vidhansabha_id}">${v.vidhansabha}</option>`
                                    );
                                });

                                $('#vidhansabha_id').val(vidhansabhaId).trigger('change');

                                if (vidhansabhaId) {
                                    $.get('/admin/get-mandal/' + vidhansabhaId, function(res) {
                                        let options =
                                            '<option value="">--चुनें--</option>' + res
                                            .join('');
                                        $('#mandal_id').html(options);
                                        $('#mandal_id').val(mandalId);
                                    });
                                }
                            });
                        }
                    });

                    $('#district_id').on('change', function() {
                        const districtId = $(this).val();
                        $('#vidhansabha_id').html('<option value="">--चुनें--</option>');
                        $('#mandal_id').html('<option value="">--चुनें--</option>');

                        if (districtId) {
                            $.get('/get-vidhansabhas?district_id=' + districtId, function(res) {
                                res.forEach(v => {
                                    $('#vidhansabha_id').append(
                                        `<option value="${v.vidhansabha_id}">${v.vidhansabha}</option>`
                                    );
                                });
                            });
                        }
                    });

                    $('#vidhansabha_id').on('change', function() {
                        const vidhansabhaId = $(this).val();
                        $('#mandal_id').html('<option value="">--चुनें--</option>');

                        if (vidhansabhaId) {
                            $.get('/admin/get-mandal/' + vidhansabhaId, function(res) {
                                let options = '<option value="">--चुनें--</option>' + res.join(
                                    '');
                                $('#mandal_id').html(options);
                            });
                        }
                    });
                });


                $(document).ready(function() {
                    let modalTemplate = $('#modalRowGroup .form-group.row').first().clone();

                    modalTemplate.find('input').val('');
                    modalTemplate.find('.add-row').replaceWith(`
                        <button type="button" class="btn btn-danger remove-row mt-4">
                            <i class="fa fa-minus"></i>
                        </button>
                     `);

                    $('#modalRowGroup').on('click', '.add-row', function() {
                        $('#modalRowGroup').append(modalTemplate.clone());
                    });

                    $('#modalRowGroup').on('click', '.remove-row', function() {
                        $(this).closest('.form-group.row').remove();
                    });



                    $('#addGramForm').on('submit', function(e) {
                        e.preventDefault();

                        let formData = new FormData(this);
                        $.ajax({
                            url: "{{ route('nagaradd.store') }}",
                            method: "POST",
                            data: formData,
                            processData: false,
                            contentType: false,
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            },
                            success: function(response) {
                                if (response.success && response.nagars.length > 0) {
                                    response.nagars.forEach(nagar => {
                                        $('#nagar_id').append(
                                            $('<option>', {
                                                value: nagar.id,
                                                text: nagar.name,
                                                selected: true // auto-select newly added
                                            })
                                        );
                                    });

                                    $('#addGramModal').modal('hide');
                                    $('#assignModal').modal('show');
                                    $('#txtmandal').change();
                                }
                            },
                            error: function(xhr) {
                                if (xhr.status === 409 && xhr.responseJSON?.error) {
                                    alert(xhr.responseJSON.error);
                                } else {
                                    alert(
                                        'त्रुटि: नगर केंद्र/ग्राम केंद्र जोड़ने में विफल'
                                        );
                                }
                                console.log(xhr.responseText);
                            }
                        });
                    });



                });
            });
        </script>
    @endpush
@endsection
