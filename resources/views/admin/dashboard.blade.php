@php
    $pageTitle = 'सदस्यता फाॅर्म डेटा';
    $breadcrumbs = [
        'एडमिन' => '#',
        'सदस्यता फाॅर्म डेटा' => '#',
    ];
@endphp

@extends('layouts.app')
@section('title', 'Admin Dashboard')

@section('content')
    <div class="container">

        <div class="row page-titles mx-0">
            <div class="col-md-12 col-sm-12 col-xs-12" id="complaintFilterForm">
                <div class="row">
                    <div class="col-md-2">
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <label style="white-space: nowrap;">सदस्यता आई डी/मोबाइल</label>
                        </div>
                        <input type="text" id="main_mobile" class="form-control" />
                    </div>

                    <div class="col-md-2">
                        <label>सदस्य का नाम</label>
                        <input type="text" id="name" class="form-control">
                    </div>

                    <div class="col-md-2">
                        <label>लिंग</label>
                        <select id="gender" class="form-control select2">
                            <option value="">--चुनें--</option>
                            <option value="पुरुष">पुरुष</option>
                            <option value="स्त्री">स्त्री</option>
                            <option value="अन्य">अन्य</option>
                        </select>
                    </div>

                    <div class="col-md-2">
                        <label>श्रेणी</label>
                        <select id="category" class="form-control select2">
                            <option value="">--चुनें--</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category->category }}">{{ $category->category }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-2">
                        <label>व्यवसाय</label>
                        <select id="business" class="form-control select2">
                            <option value="">--चुनें--</option>
                            @foreach ($businesses as $business)
                                <option value="{{ $business->business_name }}">{{ $business->business_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-2">
                        <label>जिला</label>
                        <select id="district" class="form-control select2">
                            <option value="">--चुनें--</option>
                            @foreach ($districts as $district)
                                <option value="{{ $district->district_id }}">{{ $district->district_name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-2">
                        <label>विधानसभा का नाम<span class="required text-danger ml-1">*</span></label>
                        <select id="txtvidhansabha" class="form-control select2">
                            <option value="">--चुनें--</option>
                        </select>
                    </div>

                    <div class="col-md-2">
                        <label>मंडल का नाम<span class="required text-danger ml-1">*</span></label>
                        <select id="mandal" class="form-control">
                            <option value="">--चुनें--</option>
                        </select>
                    </div>

                    <div class="col-md-2">
                        <label>जाति<span class="required text-danger ml-1">*</span></label>
                        <select id="txtjati" class="form-control select2">
                            <option value="">--जाति चुनें--</option>
                            @foreach ($jaties as $jati)
                                <option value="{{ $jati->jati_name }}">{{ $jati->jati_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-2">
                        <label>धर्म<span class="required text-danger ml-1">*</span></label>
                        <select id="religion" class="form-control select2">
                            <option value="">--चुनें--</option>
                            @foreach ($religions as $religion)
                                <option value="{{ $religion->religion_name }}">{{ $religion->religion_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>



                    <div class="col-md-2">
                        <label>शैक्षणिक योग्यता<span class="required text-danger ml-1">*</span></label>
                        <select id="education" class="form-control select2">
                            <option value="">--चुनें--</option>
                            @foreach ($educations as $education)
                                <option value="{{ $education->education_name }}">{{ $education->education_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-2">
                        <label>राजनीतिक सक्रियता<span class="required text-danger ml-1">*</span></label>
                        <select id="party_name" class="form-control select2">
                            <option value="">--चुनें--</option>
                            @foreach ($politics as $politic)
                                <option value="{{ $politic->name }}">{{ $politic->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-2">
                        <label>बी.जे.एस सदस्यता<span class="required text-danger ml-1">*</span></label>
                        <select id="membership" class="form-control select2">
                            <option value="">--चुनें--</option>
                            <option>समर्पित कार्यकर्ता</option>
                            <option>सक्रिय कार्यकर्ता</option>
                            <option>साधारण कार्यकर्ता</option>
                        </select>
                    </div>

                    <div class="col-md-2">
                        <label>रुचि<span class="required text-danger ml-1">*</span></label>
                        <select id="interest_area" class="form-control select2">


                            <option value="">--चुनें--</option>
                            @foreach ($interests as $interest)
                                <option value="{{ $interest->interest_name }}">{{ $interest->interest_name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-2">
                        <label>परिवार में कुल सदस्य<span class="required text-danger ml-1">*</span></label>
                        <select id="family_member" class="form-control select2">
                            <option value="">--चुनें--</option>
                            <option value="0 AND 5">0-5</option>
                            <option value="5 AND 10">5-10</option>
                            <option value="10 AND 20">10-20</option>
                        </select>
                    </div>

                    <div class="col-md-2">
                        <label>वाहन<span class="required text-danger ml-1">*</span></label>
                        <select id="vehicle" class="form-control select2">
                            <option value="">--चुनें--</option>
                            <option value="vehicle1">मोटरसाइकिल</option>
                            <option value="vehicle2">कार</option>
                            <option value="vehicle3">ट्रेक्टर</option>
                        </select>
                    </div>

                    <div class="col-md-2">
                        <label>व्हाट्सएप नंबर<span class="required text-danger ml-1">*</span></label>
                        <select id="whatsapp" class="form-control select2">
                            <option value="">--चुनें--</option>
                            <option value="1">Yes</option>
                            <option value="0">No</option>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <div class="row">
                            <div class="col-md-6">
                                <label>आयु से</label>
                                <input type="text" id="from_age" class="form-control">
                            </div>
                            <div class="col-xl-6">
                                <label>आयु तक</label>
                                <input type="text" id="to_age" class="form-control">
                            </div>
                        </div>
                    </div>

                    <div class="col-md-2">
                        <label>तिथि से</label>
                        <input type="date" name="from_date" id="from_date" class="form-control">

                    </div>

                    <div class="col-md-2">
                        <label>तिथि तक</label>
                        <input type="date" name="to_date" id="to_date" class="form-control">
                    </div>


                    <div class="col-md-3 mt-2" style="color:rgb(55, 64, 75)">
                        <br>
                        <button class="btn btn-success mr-4" style="font-size: 12px" id="data-filter">फ़िल्टर</button>
                        <strong style="font-size: 18px">
                            कुल सदस्य: <span id="total">0</span></strong>
                    </div>
                </div>
            </div>
        </div>


        <div class="row">
            <div class="col-md-12">
                <div class="card" id="table_card" style="display: none;">
                    <div class="card-body">
                        <button id="download_full_data" class="btn btn-primary mb-3" style="display: none; float: right">
                            पूरा डेटा डाउनलोड करें
                        </button>

                        @if (session('success'))
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                {{ session('success') }}
                                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                        @endif

                        @if (session('update_msg'))
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                {{ session('update_msg') }}
                                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                        @endif

                        @if (session('delete_msg'))
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                {{ session('delete_msg') }}
                                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                        @endif

                        <div id="filtered_data" class="table-responsive">
                            <table class="display table-bordered" style="min-width: 845px" id="example">
                                <thead>
                                    <tr>
                                        <th>क्र.</th>
                                        <th>सदस्य आईडी</th>
                                        <th>नाम</th>
                                        <th>मोबाइल</th>
                                        <th>लिंग</th>
                                        <th>सदस्यता दिनांक</th>
                                        <th>क्रिया</th>
                                        <th>अपडेट</th>
                                    </tr>
                                </thead>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>



    @push('scripts')
        <script>
            const filterParams = () => {
                return {
                    _token: "{{ csrf_token() }}",
                    main_mobile: $("#main_mobile").val(),
                    name: $("#name").val(),
                    gender: $("#gender").val(),
                    category: $("#category").val(),
                    business: $("#business").val(),
                    district: $("#district").val(),
                    txtvidhansabha: $("#txtvidhansabha").val(),
                    mandal: $("#mandal").val(),
                    txtjati: $("#txtjati").val(),
                    religion: $("#religion").val(),
                    from_age: $("#from_age").val(),
                    to_age: $("#to_age").val(),
                    education: $("#education").val(),
                    party_name: $("#party_name").val(),
                    membership: $("#membership").val(),
                    interest_area: $("#interest_area").val(),
                    family_member: $("#family_member").val(),
                    vehicle: $("#vehicle").val(),
                    whatsapp: $("#whatsapp").val(),
                    from_date: $('#from_date').val(),
                    to_date: $('#to_date').val()
                };
            };


            let table;

            function loadDataTable() {
                if ($.fn.DataTable.isDataTable('#example')) {
                    $('#example').DataTable().clear().destroy();
                }

                $("#loader-wrapper").show();
                $('#total_count').text('');

                table = $('#example').DataTable({
                    processing: true,
                    serverSide: true,
                    pageLength: 10,
                    lengthChange: true,
                    dom: '<"row mb-2"<"col-sm-3"l><"col-sm-6"B><"col-sm-3"f>>' +
                        '<"row"<"col-sm-12"tr>>' +
                        '<"row mt-2"<"col-sm-5"i><"col-sm-7"p>>',
                    buttons: [
                        'csv', 'excel'
                    ],
                    lengthMenu: [
                        [10, 25, 50, 100, 500, 1000],
                        [10, 25, 50, 100, 500, 1000],
                    ],
                    ajax: {
                        url: '{{ route('dashboard.filter') }}',
                        type: 'POST',
                        data: function(d) {
                            d._token = "{{ csrf_token() }}";
                            d.main_mobile = $("#main_mobile").val();
                            d.name = $("#name").val();
                            d.gender = $("#gender").val();
                            d.category = $("#category").val();
                            d.business = $("#business").val();
                            d.district = $("#district").val();
                            d.txtvidhansabha = $("#txtvidhansabha").val();
                            d.mandal = $("#mandal").val();
                            d.txtjati = $("#txtjati").val();
                            d.religion = $("#religion").val();
                            d.from_age = $("#from_age").val();
                            d.to_age = $("#to_age").val();
                            d.education = $("#education").val();
                            d.party_name = $("#party_name").val();
                            d.membership = $("#membership").val();
                            d.interest_area = $("#interest_area").val();
                            d.family_member = $("#family_member").val();
                            d.vehicle = $("#vehicle").val();
                            d.whatsapp = $("#whatsapp").val();
                            d.from_date = $('#from_date').val();
                            d.to_date = $('#to_date').val();
                        },
                        dataSrc: function(json) {
                            $('#loader-wrapper').hide();
                            $('#total').text(json.recordsFiltered);
                            return json.data;
                        },
                        complete: function() {
                            $("#loader-wrapper").hide();
                        },
                        error: function() {
                            $('#loader-wrapper').hide();
                            console.error(xhr.responseText);
                        }
                    },
                    columns: [{
                            data: 'DT_RowIndex',
                            name: 'DT_RowIndex'
                        },
                        {
                            data: 'member_id',
                            name: 'member_id'
                        },
                        {
                            data: 'name',
                            name: 'name'
                        },
                        {
                            data: 'mobile',
                            name: 'mobile'
                        },
                        {
                            data: 'gender',
                            name: 'gender'
                        },
                        {
                            data: 'entry_date',
                            name: 'entry_date'
                        },
                        {
                            data: 'action',
                            name: 'action',
                            orderable: false,
                            searchable: false
                        },
                        {
                            data: 'edit',
                            name: 'edit',
                            orderable: false,
                            searchable: false
                        }
                    ]
                });

                $('#example').on('preXhr.dt', function() {
                    $("#loader-wrapper").show();
                });

                $('#example').on('xhr.dt', function() {
                    $("#loader-wrapper").hide();
                });

                $("#download_full_data").off('click').on('click', function() {
                    $("#loader-wrapper").show();
                    const query = $.param(filterParams);
                    window.location.href = `{{ route('dashboard.download') }}?${query}`;
                });
            }

            $(document).ready(function() {
                $('#data-filter').on('click', function() {
                    $('#table_card').show();
                    $("#download_full_data").show();
                    loadDataTable();
                });

                $('#district').on('change', function() {
                    let district_id = $(this).val();
                    if (district_id) {
                        $.ajax({
                            url: '/admin/get-vidhansabha/' + district_id,
                            type: 'GET',
                            success: function(data) {
                                $('#txtvidhansabha').html('<option value="">--चुनें--</option>' +
                                    data.join(''));
                                $('#mandal').html(
                                    '<option value="">--चुनें--</option>');
                            }
                        });
                    } else {
                        $('#txtvidhansabha').html('<option value="">--चुनें--</option>');
                        $('#mandal').html('<option value="">--चुनें--</option>');
                    }
                });

                $('#txtvidhansabha').on('change', function() {
                    let vidhansabha_id = $(this).val();
                    if (vidhansabha_id) {
                        $.ajax({
                            url: '/admin/get-mandal/' + vidhansabha_id,
                            type: 'GET',
                            success: function(data) {
                                $('#mandal').html('<option value="">--चुनें--</option>' + data.join(
                                    ''));
                            }
                        });
                    } else {
                        $('#mandal').html('<option value="">--चुनें--</option>');
                    }
                });

            });

            $(document).on('click', '.deleteBtn', function() {
                if (!confirm("क्या आप वाकई इस रिकॉर्ड को हटाना चाहते हैं?")) return;

                let id = $(this).data('id');

                $.ajax({
                    url: '/register/' + id,
                    type: 'DELETE',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        alert(response.message);
                        $('#example').DataTable().ajax.reload();
                    },
                    error: function(xhr) {
                        alert("हटाने में समस्या हुई!");
                    }
                });
            });
        </script>
    @endpush

@endsection
