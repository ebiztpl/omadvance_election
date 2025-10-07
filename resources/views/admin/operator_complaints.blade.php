@php
    $pageTitle = 'ऑपरेटर समस्याएँ';
    $breadcrumbs = [
        'एडमिन' => '#',
        'ऑपरेटर समस्याएँ' => '#',
    ];
@endphp

@extends('layouts.app')
@section('title', 'View Operator Complaints')

@section('content')
    <div class="container">

        <div class="row page-titles mx-0">
            <div class="col-md-12 col-sm-12 col-xs-12">
                <form method="GET" id="complaintFilterForm">
                    <div class="row mt-1">
                        <div class="col-md-2">
                            <label>स्थिति</label>
                            <select name="complaint_status" id="complaint_status" class="form-control">
                                <option value="">-- सभी --</option>
                                <option value="1">शिकायत दर्ज</option>
                                <option value="2">प्रक्रिया में</option>
                                <option value="3">स्थगित</option>
                                <option value="4">पूर्ण</option>
                                <option value="5">रद्द</option>
                            </select>
                        </div>

                        <div class="col-md-2">
                            <label>शिकायत प्रकार</label>
                            <select name="complaint_type" id="complaint_type" class="form-control">
                                {{-- <option value="शुभ सुचना">शुभ सुचना</option>
                                <option value="अशुभ सुचना">अशुभ सुचना</option> --}}
                                <option value="समस्या" selected>समस्या</option>
                                <option value="विकास">विकास</option>
                            </select>
                        </div>

                        <div class="col-md-2">
                            <label>विभाग</label>
                            <select name="department_id" id="department_id" class="form-control">
                                <option value="">-- सभी --</option>
                                @foreach ($departments as $dept)
                                    <option value="{{ $dept->department_id }}">{{ $dept->department_name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-2">
                            <label>विषय</label>
                            <select name="subject_id" id="subject_id" class="form-control">
                                <option value="">-- सभी --</option>
                                @foreach ($subjects as $subject)
                                    <option value="{{ $subject->subject_id }}">{{ $subject->subject }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-2">
                            <label>संभाग</label>
                            <select name="division_id" id="division_id" class="form-control">
                                <option value="">--चुने--</option>
                                @foreach ($divisions as $division)
                                    <option value="{{ $division->division_id }}">
                                        {{ $division->division_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label>जिला</label>
                            <select name="district_id" id="district_id" class="form-control">
                                <option value="">--चुने--</option>
                                @foreach ($districts as $district)
                                    <option value="{{ $district->district_id }}">
                                        {{ $district->district_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-2">
                            <label>विधानसभा</label>
                            <select name="vidhansabha_id" id="vidhansabha_id" class="form-control">
                                <option value="">--चुने--</option>
                                @foreach ($vidhansabhas as $vidhansabha)
                                    <option value="{{ $vidhansabha->vidhansabha_id }}">{{ $vidhansabha->vidhansabha }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-2">
                            <label>मंडल</label>
                            <select name="mandal_id" id="mandal_id" class="form-control">
                                <option value="">-- सभी --</option>
                                @foreach ($mandals as $mandal)
                                    <option value="{{ $mandal->mandal_id }}">{{ $mandal->mandal_name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-2">
                            <label>ग्राम/नगर</label>
                            <select name="gram_id" id="gram_id" class="form-control">
                                <option value="">-- सभी --</option>
                                @foreach ($grams as $g)
                                    <option value="{{ $g->nagar_id }}">{{ $g->nagar_name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-2">
                            <label>मतदान केंद्र</label>
                            <select name="polling_id" id="polling_id" class="form-control">
                                <option value="">-- सभी --</option>
                                @foreach ($pollings as $p)
                                    <option value="{{ $p->gram_polling_id }}">{{ $p->polling_name }}
                                        ({{ $p->polling_no }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-2">
                            <label>क्षेत्र</label>
                            <select name="area_id" id="area_id" class="form-control">
                                <option value="">-- सभी --</option>
                                @foreach ($areas as $a)
                                    <option value="{{ $a->area_id }}">{{ $a->area_name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-2">
                            <label>जाति</label>
                            <select name="jati_id" id="jati_id" class="form-control">
                                <option value="">-- सभी --</option>
                                @foreach ($jatis as $j)
                                    <option value="{{ $j->jati_id }}">{{ $j->jati_name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-2">
                            <label>उत्तर</label>
                            <select name="reply_id" id="reply_id" class="form-control">
                                <option value="">-- सभी --</option>
                                @foreach ($replyOptions as $option)
                                    <option value="{{ $option->reply_id }}">{{ $option->reply }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-2">
                            <label>तिथि से</label>
                            <input type="date" name="from_date" id="from_date" class="form-control">

                        </div>

                        <div class="col-md-2">
                            <label>तिथि तक</label>
                            <input type="date" name="to_date" id="to_date" class="form-control">
                        </div>

                        <div class="col-md-2">
                            <label>फॉरवर्ड</label>
                            <select name="admin_id" id="admin_id" class="form-control">
                                <option value="">-- सभी --</option>
                                @foreach ($managers as $manager)
                                    <option value="{{ $manager->admin_id }}">{{ $manager->admin_name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-2">
                            <label>अन्य फ़िल्टर चुनें</label>
                            <select id="complaintOtherFilter" class="form-control">
                                <option value="">सभी</option>
                                <option value="forwarded_manager">कुल निर्देशित</option>
                                <option value="not_opened">नई शिकायतें</option>
                                <option value="reviewed">रीव्यू की गई</option>
                                <option value="important">महत्त्वपूर्ण</option>
                                <option value="closed">पूर्ण</option>
                                <option value="cancel">रद्द</option>
                                <option value="reference_null">रेफरेंस नहीं है</option>
                                <option value="reference">रेफरेंस है</option>
                            </select>
                            <span id="filterMsg" style="color: red; display: none; font-size: 11px;">पहले फॉरवर्ड मैनेजर
                                चुनें</span>
                        </div>

                        <div class="col-md-2 mt-2">
                            <br>
                            <button type="submit" class="btn btn-primary" style="font-size: 12px"
                                id="applyFilters">फ़िल्टर</button>
                        </div>
                    </div>
                </form>

                <div class="text-center">
                    <i id="toggleFilterIcon" class="fa fa-angle-up"
                        style="float: right; cursor: pointer; font-size: 24px;" title="फ़िल्टर छुपाएं"></i>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">

                        @if (session('success'))
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                {{ session('success') }}
                                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                        @endif

                        <ul class="nav nav-tabs nav-filters mb-1" id="complaintFilterTabs">
                            <li class="nav-item">
                                <a class="nav-link filter-link {{ request('filter') === null ? 'active' : '' }}"
                                    style="color: black" data-filter="" href="#">सभी</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link filter-link {{ request('filter') === 'not_opened' ? 'active' : '' }}"
                                    style="color: black" data-filter="not_opened" href="#">नई शिकायतें</a>
                            </li>

                            <li class="nav-item">
                                <a class="nav-link filter-link {{ request('filter') === 'reviewed' ? 'active' : '' }}"
                                    style="color: black" data-filter="reviewed" href="#">रीव्यू की गई</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link filter-link {{ request('filter') === 'important' ? 'active' : '' }}"
                                    style="color: black" data-filter="important" href="#">महत्त्वपूर्ण</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link filter-link {{ request('filter') === 'closed' ? 'active' : '' }}"
                                    style="color: black" data-filter="closed" href="#">पूर्ण शिकायतें</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link filter-link {{ request('filter') === 'cancel' ? 'active' : '' }}"
                                    style="color: black" data-filter="cancel" href="#">रद्द शिकायतें</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link filter-link {{ request('filter') === 'reference_null' ? 'active' : '' }}"
                                    style="color: black" data-filter="reference_null" href="#">रेफरेंस नहीं है</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link filter-link {{ request('filter') === 'reference' ? 'active' : '' }}"
                                    style="color: black" data-filter="reference" href="#">रेफरेंस है</a>
                            </li>
                        </ul>

                        <div class="table-responsive">
                            <span
                                style="margin-bottom: 0px; font-size: 18px; color: green; text-align: right; margin-left: 50px; float: right">कुल
                                शिकायत - <span id="complaint-count"></span></span>

                            <table id="example" style="min-width: 845px" class="display table-bordered">
                                <thead>
                                    <tr>
                                        <th>क्र.</th>
                                        <th style="min-width: 100px;">शिकायतकर्ता</th>
                                        <th>रेफरेंस</th>
                                        <th style="min-width: 130px;">क्षेत्र</th>
                                        <th style="min-width: 100px;">शिकायत विवरण</th>
                                        <th>विभाग</th>
                                        <th>शिकायत की स्थिति</th>
                                        {{-- <th>से बकाया</th> --}}
                                        {{-- <th>स्थिति</th> --}}
                                        <th>रीव्यू दिनांक</th>
                                        <th>महत्त्व स्तर</th>
                                        <th>आवेदक</th>
                                        <th>फॉरवर्ड अधिकारी</th>
                                        <th>विस्तार से</th>
                                        <th style="display: none;">मतदाता पहचान</th>
                                    </tr>
                                </thead>
                                <tbody id="complaintsTableBody">

                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            $(document).ready(function() {
                const adminSelect = document.getElementById('admin_id');
                const filterSelect = document.getElementById('complaintOtherFilter');
                const filterMsg = document.getElementById('filterMsg');

                filterSelect.addEventListener('change', function() {
                    if (this.value === 'forwarded_manager' && adminSelect.value === "") {
                        this.value = '';

                        filterMsg.style.display = 'inline';
                        setTimeout(() => {
                            filterMsg.style.display = 'none';
                        }, 3000);
                    } else {
                        filterMsg.style.display = 'none';
                    }
                });


                $('#division_id').on('change', function() {
                    const divisionId = $(this).val();
                    $('#district_id, #vidhansabha_id, #gram_id, #polling_id, #area_id').html('');
                    $('#district_id').append('<option value="">--चुने--</option>');
                    $('#vidhansabha_id').append('<option value="">--चुने--</option>');
                    $('#gram_id, #polling_id, #area_id').append('<option value="">-- सभी --</option>');
                    if (!divisionId) return;
                    $.get('/admin/get-districts/' + divisionId, function(data) {
                        data.forEach(option => $('#district_id').append(option));
                    });
                });

                $('#district_id').on('change', function() {
                    const districtId = $(this).val();
                    $('#vidhansabha_id, #gram_id, #polling_id, #area_id').html('');
                    $('#vidhansabha_id').append('<option value="">--चुने--</option>');
                    $('#gram_id, #polling_id, #area_id').append('<option value="">-- सभी --</option>');
                    if (!districtId) return;
                    $.get('/admin/get-vidhansabha/' + districtId, function(data) {
                        data.forEach(option => $('#vidhansabha_id').append(option));
                    });
                });

                $('#vidhansabha_id').on('change', function() {
                    const vidhansabhaId = $(this).val();
                    $('#mandal_id, #gram_id, #polling_id, #area_id').html('');
                    $('#mandal_id').append('<option value="">-- सभी --</option>');
                    $('#gram_id, #polling_id, #area_id').append('<option value="">-- सभी --</option>');
                    if (!vidhansabhaId) return;
                    $.get('/admin/get-mandal/' + vidhansabhaId, function(data) {
                        data.forEach(option => $('#mandal_id').append(option));
                    });
                });

                // Mandal → Gram
                $('#mandal_id').on('change', function() {
                    let mandalId = $(this).val();
                    $('#gram_id').html('<option value="">ग्राम चयन करें</option>');
                    $('#polling_id').html('<option value="">मतदान केंद्र</option>');
                    $('#area_id').html('<option value="">क्षेत्र</option>');

                    if (mandalId) {
                        $.get('/admin/get-nagar/' + mandalId, function(data) {
                            $('#gram_id').append(data);
                        });

                        // $.get('/admin/get-pollings/' + mandalId, function(data) {
                        //     let html = '<option value="">मतदान केंद्र</option>';
                        //     data.forEach(function(polling) {
                        //         html +=
                        //             `<option value="${polling.gram_polling_id}">${polling.polling_name} (${polling.polling_no})</option>`;
                        //     });
                        //     $('#polling_id').html(html);
                        // });
                    }
                });

                // Gram → Polling (optional if using polling from mandal)
                $('#gram_id').on('change', function() {
                    let gramId = $(this).val();
                    $('#polling_id').html('<option value="">मतदान केंद्र</option>');
                    $('#area_id').html('<option value="">क्षेत्र</option>');

                    if (gramId) {
                        $.get('/admin/get-gram_pollings/' + gramId, function(data) {
                            let html = '<option value="">मतदान केंद्र</option>';
                            data.forEach(function(polling) {
                                html +=
                                    `<option value="${polling.gram_polling_id}">${polling.polling_name} (${polling.polling_no})</option>`;
                            });
                            $('#polling_id').html(html);
                        });
                    }
                });

                // Polling → Area
                $('#polling_id').on('change', function() {
                    let pollingId = $(this).val();
                    $('#area_id').html('<option value="">क्षेत्र</option>');

                    if (pollingId) {
                        $.get('/admin/get-areas/' + pollingId, function(data) {
                            let html = '<option value="">क्षेत्र</option>';
                            data.forEach(function(area) {
                                html +=
                                    `<option value="${area.area_id}">${area.area_name}</option>`;
                            });
                            $('#area_id').html(html);
                        });
                    }
                });

                // Department → Subject
                $('#department_id').on('change', function() {
                    let departmentId = $(this).val();
                    $('#subject_id').html('<option value="">विषय</option>');

                    if (departmentId) {
                        $.get('/admin/get-subjects/' + departmentId, function(data) {
                            let html = '<option value="">विषय</option>';
                            data.forEach(function(subject) {
                                html +=
                                    `<option value="${subject.subject_id}">${subject.subject}</option>`;
                            });
                            $('#subject_id').html(html);
                        });
                    }
                });




                const filterForm = $('#complaintFilterForm');
                const toggleIcon = $('#toggleFilterIcon');

                // Check saved state on page load
                const isHidden = localStorage.getItem('filterHidden') === 'true';

                if (isHidden) {
                    filterForm.hide();
                    toggleIcon.removeClass('fa-angle-up').addClass('fa-angle-down').attr('title', 'फ़िल्टर दिखाएं');
                }

                // Toggle on icon click
                toggleIcon.on('click', function() {
                    filterForm.slideToggle(300, function() {
                        const isVisible = filterForm.is(':visible');

                        // Save state
                        localStorage.setItem('filterHidden', !isVisible);

                        // Toggle icon direction and tooltip
                        if (isVisible) {
                            toggleIcon.removeClass('fa-angle-down').addClass('fa-angle-up').attr(
                                'title', 'फ़िल्टर छुपाएं');
                        } else {
                            toggleIcon.removeClass('fa-angle-up').addClass('fa-angle-down').attr(
                                'title', 'फ़िल्टर दिखाएं');
                        }
                    });
                });

                $('#complaintFilterTabs a').on('click', function(e) {
                    e.preventDefault();

                    $('#complaintFilterTabs a').removeClass('active');
                    $(this).addClass('active');

                    const filter = $(this).data('filter');
                    $('#loader-wrapper').show();

                    table.ajax.reload(function() {
                        $('#loader-wrapper').hide();
                    }, false);
                });


                const urlParams = new URLSearchParams(window.location.search);

                if (urlParams.has('complaint_status')) $('#complaint_status').val(urlParams.get('complaint_status'));
                if (urlParams.has('complaint_type')) $('#complaint_type').val(urlParams.get('complaint_type'));
                if (urlParams.has('department_id')) $('#department_id').val(urlParams.get('department_id'));
                if (urlParams.has('subject_id')) $('#subject_id').val(urlParams.get('subject_id'));
                if (urlParams.has('division_id')) $('#division_id').val(urlParams.get('division_id'));
                if (urlParams.has('district_id')) $('#district_id').val(urlParams.get('district_id'));
                if (urlParams.has('vidhansabha_id')) $('#vidhansabha_id').val(urlParams.get('vidhansabha_id'));
                if (urlParams.has('mandal_id')) $('#mandal_id').val(urlParams.get('mandal_id'));
                if (urlParams.has('gram_id')) $('#gram_id').val(urlParams.get('gram_id'));
                if (urlParams.has('polling_id')) $('#polling_id').val(urlParams.get('polling_id'));
                if (urlParams.has('area_id')) $('#area_id').val(urlParams.get('area_id'));
                if (urlParams.has('from_date')) $('#from_date').val(urlParams.get('from_date'));
                if (urlParams.has('to_date')) $('#to_date').val(urlParams.get('to_date'));
                if (urlParams.has('admin_id')) $('#admin_id').val(urlParams.get('admin_id'));
                if (urlParams.has('reply_id')) $('#reply_id').val(urlParams.get('reply_id'));

                // Handle jati_id or jati_null
                if (urlParams.has('jati_null') && urlParams.get('jati_null') === '1') {
                    $('#jati_id').val(''); // Keep empty for null
                } else if (urlParams.has('jati_id')) {
                    $('#jati_id').val(urlParams.get('jati_id'));
                }


                let table = $('#example').DataTable({
                    processing: true,
                    serverSide: true,
                    destroy: true,
                    dom: '<"row mb-2"<"col-sm-3"l><"col-sm-6"B><"col-sm-3"f>>' +
                        '<"row"<"col-sm-12"tr>>' +
                        '<"row mt-2"<"col-sm-5"i><"col-sm-7"p>>',
                    buttons: [{
                            extend: "csv",
                            exportOptions: {
                                modifier: {
                                    page: "all"
                                },
                                columns: ':visible:not(.not-export-col), :hidden:not(.not-export-col)'
                            },
                        },
                        {
                            extend: "excel",
                            exportOptions: {
                                modifier: {
                                    page: "all"
                                },
                                columns: ':visible:not(.not-export-col), :hidden:not(.not-export-col)'
                            },
                        }

                    ],
                    lengthMenu: [
                        [10, 25, 50, 100, 500, 1000],
                        [10, 25, 50, 100, 500, 1000],
                    ],
                    ajax: {
                        url: "{{ route('operator.complaint.view') }}",
                        data: function(d) {
                            if (urlParams.has('show_unavailable')) {
                                d.show_unavailable = urlParams.get('show_unavailable');
                            }
                            d.complaint_status = $('#complaint_status').val();
                            d.complaint_type = $('#complaint_type').val();
                            if (urlParams.has('department_null') && urlParams.get('department_null') ===
                                '1') {
                                d.department_null = '1';
                            } else {
                                d.department_id = $('#department_id').val();
                            }
                            d.subject_id = $('#subject_id').val();
                            d.division_id = $('#division_id').val();
                            d.district_id = $('#district_id').val();
                            d.vidhansabha_id = $('#vidhansabha_id').val();
                            d.mandal_id = $('#mandal_id').val();
                            d.gram_id = $('#gram_id').val();
                            d.polling_id = $('#polling_id').val();
                            if (urlParams.has('jati_null') && urlParams.get('jati_null') === '1') {
                                d.jati_null = '1';
                            } else {
                                d.jati_id = $('#jati_id').val();
                            }
                            d.area_id = $('#area_id').val();
                            d.from_date = $('#from_date').val();
                            d.to_date = $('#to_date').val();
                            d.reply_id = $('#reply_id').val();
                            d.admin_id = $('#admin_id').val();
                            if (urlParams.has('reference_null') && urlParams.get('reference_null') ===
                                '1') {
                                d.reference_null = '1';
                            } else if (urlParams.has('reference_name')) {
                                d.reference_name = urlParams.get('reference_name');
                            }
                            d.complaintOtherFilter = $('#complaintOtherFilter').val();
                            d.filter = $('#complaintFilterTabs a.active').data('filter') || '';

                        }
                    },
                    columns: [{
                            data: 'index'
                        },
                        {
                            data: 'name'
                        },
                        {
                            data: 'reference_name'
                        },
                        {
                            data: 'area_details'
                        },
                        {
                            data: 'issue_description'
                        },
                        {
                            data: 'complaint_department'
                        },
                        {
                            data: 'posted_date'
                        },
                        {
                            data: 'review_date'
                        },
                        {
                            data: 'importance'
                        },
                        {
                            data: 'applicant_name'
                        },
                        {
                            data: 'forwarded_to_name'
                        },
                        {
                            data: 'action',
                            orderable: false,
                            searchable: false,
                            className: 'not-export-col'
                        },
                        {
                            data: 'voter_id',
                            visible: false,
                            searchable: false
                        }
                    ]
                });

                table.on('preXhr.dt', function() {
                    $('#loader-wrapper').show();
                });

                table.on('xhr.dt', function() {
                    $('#loader-wrapper').hide();
                });

                table.on('draw', function() {
                    let info = table.page.info();
                    $('#complaint-count').text(info.recordsDisplay);
                });

                $('#applyFilters').click(function(e) {
                    e.preventDefault();
                    table.ajax.reload();
                });
            });

            if (performance.navigation.type === 1) {
                $('#complaintFilterForm')[0].reset();

                if (window.location.search) {
                    window.location.href = window.location.origin + window.location.pathname;
                }
            }

            $(document).on('click', '.delete-complaint', function(e) {
                e.preventDefault();

                let complaintId = $(this).data('id');

                if (confirm('क्या आप वाकई इस शिकायत को हटाना चाहते हैं?')) {
                    $.ajax({
                        url: '/complaints/' + complaintId,
                        type: 'DELETE',
                        data: {
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(response) {
                            alert(response.success);
                            location.reload();
                        },
                        error: function(xhr) {
                            if (xhr.responseJSON && xhr.responseJSON.error) {
                                alert(xhr.responseJSON.error);
                            } else {
                                alert('कुछ गलत हो गया, कृपया पुनः प्रयास करें।');
                            }
                        }
                    });
                }
            });
        </script>
    @endpush
@endsection
