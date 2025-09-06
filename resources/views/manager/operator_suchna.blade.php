@php
    $pageTitle = 'ऑपरेटर सूचनाएँ';
    $breadcrumbs = [
        'मैनेजर' => '#',
        'ऑपरेटर सूचनाएँ' => '#',
    ];
@endphp

@extends('layouts.app')
@section('title', 'View Operator Suchna')

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
                                <option value="11">सूचना प्राप्त</option>
                                <option value="12">फॉरवर्ड किया</option>
                                <option value="13">सम्मिलित हुए</option>
                                <option value="14">सम्मिलित नहीं हुए</option>
                                <option value="15">फोन पर संपर्क किया</option>
                                <option value="16">ईमेल पर संपर्क किया</option>
                                <option value="17">व्हाट्सएप पर संपर्क किया</option>
                                <option value="18">रद्द</option>
                            </select>
                        </div>

                        <div class="col-md-2">
                            <label>सुचना प्रकार</label>
                            <select name="complaint_type" id="complaint_type" class="form-control">
                                <option value="शुभ सुचना" selected>शुभ सुचना</option>
                                <option value="अशुभ सुचना">अशुभ सुचना</option>
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
                            <label>सूचना का विषय</label>
                            <select name="issue_title" id="issue_title" class="form-control">
                                <option value="">-- सभी --</option>

                            </select>
                        </div>

                        <div class="col-md-2">
                            <label>सुचना तिथि से</label>
                            <input type="date" name="from_date" id="from_date" class="form-control">

                        </div>

                        <div class="col-md-2">
                            <label>सुचना तिथि तक</label>
                            <input type="date" name="to_date" id="to_date" class="form-control">
                        </div>

                        <div class="col-md-2">
                            <label>कार्यक्रम दिनांक से</label>
                            <input type="date" name="programfrom_date" id="programfrom_date" class="form-control">
                        </div>

                        <div class="col-md-2">
                            <label>कार्यक्रम दिनांक तक</label>
                            <input type="date" name="programto_date" id="programto_date" class="form-control">
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
                                <option value="forwarded_manager">निर्देशित सूचनाएँ</option>
                                <option value="not_opened">नई सूचनाएँ</option>
                                <option value="sammilit_done">सम्मिलित हुए</option>
                                <option value="sammilit_notdone">सम्मिलित नहीं हुए</option>
                                <option value="cancel">रद्द</option>
                                <option value="reference_null">रेफरेंस नहीं है</option>
                                <option value="reference">रेफरेंस है</option>
                            </select>
                        </div>

                        <div class="col-md-2 mt-2">
                            <br>
                            <button type="submit" class="btn btn-primary" style="font-size: 12px" id="applyFilters">फ़िल्टर</button>
                        </div>
                    </div>
                </form>

                <div class="text-center">
                    <i id="toggleFilterIcon" class="fa fa-angle-up" style="float: right; cursor: pointer; font-size: 24px;"
                        title="फ़िल्टर छुपाएं"></i>
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
                                <a class="nav-link filter-link {{ request('filter') === 'forwarded_manager' ? 'active' : '' }}"
                                    style="color: black" data-filter="forwarded_manager" href="#">निर्देशित</a>
                            </li>

                            <li class="nav-item">
                                <a class="nav-link filter-link {{ request('filter') === null ? 'active' : '' }}"
                                    style="color: black" data-filter="" href="#">सभी</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link filter-link {{ request('filter') === 'not_opened' ? 'active' : '' }}"
                                    style="color: black" data-filter="not_opened" href="#">नई
                                    सूचनाएँ</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link filter-link {{ request('filter') === 'cancel' ? 'active' : '' }}"
                                    style="color: black" data-filter="cancel" href="#">रद्द
                                    सूचनाएँ</a>
                            </li>

                            <li class="nav-item">
                                <a class="nav-link filter-link {{ request('filter') === 'sammilit_done' ? 'active' : '' }}"
                                    style="color: black" data-filter="sammilit_done" href="#">सम्मिलित हुए
                                </a>
                            </li>

                             <li class="nav-item">
                                <a class="nav-link filter-link {{ request('filter') === 'reference_null' ? 'active' : '' }}" style="color: black" data-filter="reference_null" href="#">रेफरेंस नहीं है</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link filter-link {{ request('filter') === 'reference' ? 'active' : '' }}" style="color: black" data-filter="reference" href="#">रेफरेंस है</a>
                            </li>
                        </ul>

                        <div class="table-responsive">
                            <span id="count-button"
                                style="margin-bottom: 0px; font-size: 18px; color: green; text-align: right; margin-left: 50px; float: right">कुल
                                सूचना - <span id="complaint-count"></span></span>

                            <table id="example" style="min-width: 845px" class="display table-bordered">
                                <thead>
                                    <tr>
                                        <th>क्र.</th>
                                        <th style="min-width: 100px;">सूचनाकर्ता</th>
                                        <th>रेफरेंस</th>
                                        <th style="min-width: 100px;">क्षेत्र</th>
                                        <th>सूचना की स्थिति</th>
                                        <th>आवेदक</th>
                                        <th>फॉरवर्ड अधिकारी</th>
                                        <th>सूचना का विषय</th>
                                        <th>कार्यक्रम दिनांक</th>
                                        <th>विस्तार से</th>
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
                // Mandal → Gram
                $('#mandal_id').on('change', function() {
                    let mandalId = $(this).val();
                    $('#gram_id').html('<option value="">ग्राम चयन करें</option>');
                    $('#polling_id').html('<option value="">मतदान केंद्र</option>');
                    $('#area_id').html('<option value="">क्षेत्र</option>');

                    if (mandalId) {
                        $.get('/manager/get-nagar/' + mandalId, function(data) {
                            $('#gram_id').append(data);
                        });

                        // $.get('/manager/get-pollings/' + mandalId, function(data) {
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
                        $.get('/manager/get-gram_pollings/' + gramId, function(data) {
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
                        $.get('/manager/get-areas/' + pollingId, function(data) {
                            let html = '<option value="">क्षेत्र</option>';
                            data.forEach(function(area) {
                                html +=
                                    `<option value="${area.area_id}">${area.area_name}</option>`;
                            });
                            $('#area_id').html(html);
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


                let table = $('#example').DataTable({
                    processing: true,
                    serverSide: true,
                    destroy: true,
                    dom:
                            '<"row mb-2"<"col-sm-3"l><"col-sm-6"B><"col-sm-3"f>>' +
                            '<"row"<"col-sm-12"tr>>' +
                            '<"row mt-2"<"col-sm-5"i><"col-sm-7"p>>',
                        buttons: [
                            {
                                extend: "csv",
                                exportOptions: { modifier: { page: "all" } },
                            },
                            {
                                extend: "excel",
                                exportOptions: { modifier: { page: "all" } },
                            }
                        
                        ],
                        lengthMenu: [
                            [10, 25, 50, 100, 500, 1000],
                            [10, 25, 50, 100, 500, 1000],
                        ],
                    ajax: {
                        url: "{{ route('operator.suchna.view') }}",
                        data: function(d) {
                            d.complaint_status = $('#complaint_status').val();
                            d.complaint_type = $('#complaint_type').val();
                            d.mandal_id = $('#mandal_id').val();
                            d.gram_id = $('#gram_id').val();
                            d.polling_id = $('#polling_id').val();
                            d.area_id = $('#area_id').val();
                            d.from_date = $('#from_date').val();
                            d.to_date = $('#to_date').val();
                            d.jati_id = $('#jati_id').val();
                            d.admin_id = $('#admin_id').val();
                            d.issue_title = $('#issue_title').val();
                            d.programfrom_date = $('#programfrom_date').val();
                            d.programto_date = $('#programto_date').val();
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
                            data: 'posted_date'
                        },
                        {
                            data: 'applicant_name'
                        },
                        {
                            data: 'forwarded_to_name'
                        },
                        {
                            data: 'issue_title'
                        },
                        {
                            data: 'program_date'
                        },
                        {
                            data: 'action',
                            orderable: false,
                            searchable: false
                        },
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

            document.getElementById('complaint_type').addEventListener('change', function() {
                const type = this.value;
                const replySelect = document.getElementById('issue_title');
                replySelect.innerHTML = '<option value="">-- सभी --</option>'; // reset

                if (subjects[type]) {
                    subjects[type].forEach(sub => {
                        let opt = document.createElement('option');
                        opt.value = sub.title;
                        opt.textContent = sub.title;
                        replySelect.appendChild(opt);
                    });
                }
            });

            // trigger once on page load for default
            document.getElementById('complaint_type').dispatchEvent(new Event('change'));
        </script>
    @endpush
@endsection
