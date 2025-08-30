@php
    $pageTitle = 'ऑपरेटर समस्याएँ';
    $breadcrumbs = [
        'मैनेजर' => '#',
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
                                <option value="forwarded_manager">निर्देशित</option>
                                <option value="not_opened">नई शिकायतें</option>
                                <option value="reviewed">रीव्यू की गई</option>
                                <option value="important">महत्त्वपूर्ण</option>
                                {{-- <option value="critical">गंभीर</option> --}}
                                <option value="closed">पूर्ण</option>
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

                          <ul class="nav nav-tabs nav-filters mb-1">
                            <li class="nav-item">
                                <a class="nav-link filter-link {{ request('filter') === 'forwarded_manager' ? 'active' : '' }}" style="color: black"
                                    href="{{ route('operator.complaints.view', ['filter' => 'forwarded_manager']) }}">निर्देशित</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link filter-link {{ request('filter') === null ? 'active' : '' }}" style="color: black"
                                    href="{{ route('operator.complaints.view') }}">सभी</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link filter-link {{ request('filter') === 'not_opened' ? 'active' : '' }}" style="color: black"
                                    href="{{ route('operator.complaints.view', ['filter' => 'not_opened']) }}">नई शिकायतें</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link filter-link {{ request('filter') === 'reviewed' ? 'active' : '' }}" style="color: black"
                                    href="{{ route('operator.complaints.view', ['filter' => 'reviewed']) }}">रीव्यू की गई</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link filter-link {{ request('filter') === 'important' ? 'active' : '' }}" style="color: black"
                                    href="{{ route('operator.complaints.view', ['filter' => 'important']) }}">महत्त्वपूर्ण</a>
                            </li>
                            {{-- <li class="nav-item">
                                <a class="nav-link filter-link {{ request('filter') === 'critical' ? 'active' : '' }}" style="color: black"
                                    href="{{ route('operator.complaints.view', ['filter' => 'critical']) }}">गंभीर</a>
                            </li> --}}
                            <li class="nav-item">
                                <a class="nav-link filter-link {{ request('filter') === 'closed' ? 'active' : '' }}" style="color: black"
                                    href="{{ route('operator.complaints.view', ['filter' => 'closed']) }}">पूर्ण शिकायतें</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link filter-link {{ request('filter') === 'cancel' ? 'active' : '' }}" style="color: black"
                                    href="{{ route('operator.complaints.view', ['filter' => 'cancel']) }}">रद्द शिकायतें</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link filter-link {{ request('filter') === 'reference_null' ? 'active' : '' }}" style="color: black"
                                    href="{{ route('operator.complaints.view', ['filter' => 'reference_null']) }}">रेफरेंस नहीं है</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link filter-link {{ request('filter') === 'reference' ? 'active' : '' }}" style="color: black"
                                    href="{{ route('operator.complaints.view', ['filter' => 'reference']) }}">रेफरेंस है</a>
                            </li>
                        </ul>

                        <div class="table-responsive">
                            <span id="count-button"
                                style="margin-bottom: 0px; font-size: 18px; color: green; text-align: right; margin-left: 50px; float: right">कुल
                                शिकायत - <span id="complaint-count">{{ $complaints->count() }}</span></span>

                            <table id="example" style="min-width: 845px" class="display table-bordered">
                                <thead>
                                    <tr>
                                        <th>क्र.</th>
                                        <th style="min-width: 100px;">शिकायतकर्ता</th>
                                        <th>रेफरेंस</th>
                                        <th style="min-width: 100px;">क्षेत्र</th>
                                        <th style="min-width: 100px;">शिकायत विवरण</th>
                                        <th>विभाग</th>
                                        <th>शिकायत की स्थिति</th>
                                        {{-- <th>से बकाया</th> --}}
                                        {{-- <th>स्थिति</th> --}}
                                        <th>रीव्यू दिनांक</th>
                                        <th>महत्त्व स्तर</th>
                                        {{-- <th>गंभीरता स्तर</th> --}}
                                        <th>आवेदक</th>
                                        <th>फॉरवर्ड अधिकारी</th>
                                        <th>विस्तार से</th>
                                    </tr>
                                </thead>
                                <tbody id="complaintsTableBody">
                                    @foreach ($complaints as $index => $complaint)
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td> <strong>शिकायत क्र.: </strong>{{ $complaint->complaint_number ?? 'N/A' }}
                                                <br>
                                                <strong>नाम: </strong>{{ $complaint->name ?? 'N/A' }} <br>
                                                <strong>मोबाइल: </strong>{{ $complaint->mobile_number ?? '' }} <br>
                                                <strong>पुत्र श्री: </strong>{{ $complaint->father_name ?? '' }} <br><br>
                                                <strong>स्थिति: </strong>{!! $complaint->statusTextPlain() !!}
                                            </td>

                                             <td>{{ $complaint->reference_name }}</td>

                                            <td
                                                title="
                                                
                                                
विभाग:  {{ $complaint->division->division_name ?? 'N/A' }}
जिला:  {{ $complaint->district->district_name ?? 'N/A' }}
विधानसभा:  {{ $complaint->vidhansabha->vidhansabha ?? 'N/A' }}
मंडल:  {{ $complaint->mandal->mandal_name ?? 'N/A' }}
नगर/ग्राम:  {{ $complaint->gram->nagar_name ?? 'N/A' }}
मतदान केंद्र:  {{ $complaint->polling->polling_name ?? 'N/A' }} ({{ $complaint->polling->polling_no ?? 'N/A' }})
क्षेत्र:  {{ $complaint->area->area_name ?? 'N/A' }}
">
                                                {{ $complaint->division->division_name ?? 'N/A' }}<br>
                                                {{ $complaint->district->district_name ?? 'N/A' }}<br>
                                                {{ $complaint->vidhansabha->vidhansabha ?? 'N/A' }}<br>
                                                {{ $complaint->mandal->mandal_name ?? 'N/A' }}<br>
                                                {{ $complaint->gram->nagar_name ?? 'N/A' }}<br>
                                                {{ $complaint->polling->polling_name ?? 'N/A' }}
                                                ({{ $complaint->polling->polling_no ?? 'N/A' }})
                                                <br>
                                                {{ $complaint->area->area_name ?? 'N/A' }}
                                            </td>

                                             <td>{{ $complaint->issue_description }}</td>

                                            <td>{{ $complaint->complaint_department ?? 'N/A' }}</td>
                                            <td>
                                                <strong>तिथि:
                                                    {{ \Carbon\Carbon::parse($complaint->posted_date)->format('d-m-Y h:i A') }}</strong><br>

                                                @if ($complaint->complaint_status == 4)
                                                    पूर्ण
                                                @elseif ($complaint->complaint_status == 5)
                                                    रद्द
                                                @else
                                                    {{ $complaint->pending_days }} दिन
                                                @endif
                                            </td>

                                            <td> {{ optional($complaint->replies->sortByDesc('reply_date')->first())->review_date ?? 'N/A' }}
                                            </td>

                                            <td>
                                                {{ $complaint->latestReply?->importance ?? 'N/A' }}
                                            </td>

                                            {{-- <td>
                                                {{ $complaint->latestReply?->criticality ?? 'N/A' }}
                                            </td> --}}


                                            {{-- <td>{{ \Carbon\Carbon::parse($complaint->posted_date)->format('d-m-Y h:i A') }}
                                            </td> --}}
                                            {{-- <td>
                                                @if (!in_array($complaint->complaint_status, [4, 5]))
                                                    {{ $complaint->pending_days }} दिन
                                                @else
                                                @endif
                                            </td> --}}

                                            {{-- <td>
                                                @if ($complaint->complaint_status == 4)
                                                    पूर्ण
                                                @elseif ($complaint->complaint_status == 5)
                                                    रद्द
                                                @else
                                                    {{ $complaint->pending_days }} दिन
                                                @endif
                                            </td> --}}

                                            {{-- <td>{!! $complaint->statusTextPlain() !!}</td> --}}
                                            <td>{{ $complaint->admin_name }}</td>
                                            {{-- <td>{{ $complaint->registrationDetails->mobile1 ?? '' }}</td> --}}
                                            <td>
                                                {{ $complaint->forwarded_to_name ?? '-' }} <br>
                                                {{ $complaint->forwarded_reply_date }}
                                            </td>
                                            <td>
                                                {{-- <a href="{{ route('complaints_show.details', $complaint->complaint_id) }}"
                                                    class="btn btn-sm btn-primary" style="white-space: nowrap;">
                                                    क्लिक करें
                                                </a> --}}

                                                @if ($complaint->complaint_type === 'शुभ सुचना' || $complaint->complaint_type === 'अशुभ सुचना')
                                                    <a href="{{ route('suchna_show.details', $complaint->complaint_id) }}"
                                                        class="btn btn-sm btn-primary" style="white-space: nowrap;">
                                                        क्लिक करें
                                                    </a>
                                                @elseif($complaint->complaint_type === 'समस्या' || $complaint->complaint_type === 'विकास')
                                                    <a href="{{ route('complaints_show.details', $complaint->complaint_id) }}"
                                                        class="btn btn-sm btn-primary" style="white-space: nowrap;">
                                                        क्लिक करें
                                                    </a>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
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

                // Apply Filters
                $('#applyFilters').click(function(e) {
                     e.preventDefault();
                    let data = {
                        complaint_status: $('#complaint_status').val(),
                        complaint_type: $('#complaint_type').val(),
                        department_id: $('#department_id').val(),
                        subject_id: $('#subject_id').val(),
                        mandal_id: $('#mandal_id').val(),
                        gram_id: $('#gram_id').val(),
                        polling_id: $('#polling_id').val(),
                        area_id: $('#area_id').val(),
                        from_date: $('#from_date').val(),
                        to_date: $('#to_date').val(),
                        reply_id: $('#reply_id').val(),
                        admin_id: $('#admin_id').val(),
                        complaintOtherFilter: $('#complaintOtherFilter').val()
                    };

                    $.ajax({
                        url: "{{ route('operator.complaints.view') }}",
                        type: 'GET',
                        data: data,
                         beforeSend: function() {
                            $("#loader-wrapper").show();
                        },
                        success: function(response) {
                        

                            if ($.fn.DataTable.isDataTable('#example')) {
                                $('#example').DataTable().destroy();
                            }

                            $('#complaintsTableBody').html(response.html);
                            $('#complaint-count').text(response.count);

                            $('#example').DataTable({
                                dom: '<"row mb-2"<"col-sm-3"l><"col-sm-6"B><"col-sm-3"f>>' +
                                    '<"row"<"col-sm-12"tr>>' +
                                    '<"row mt-2"<"col-sm-5"i><"col-sm-7"p>>',
                                buttons: [{
                                        extend: "csv",
                                        exportOptions: {
                                            modifier: {
                                                page: "all"
                                            },
                                        },
                                    },
                                    {
                                        extend: "excel",
                                        exportOptions: {
                                            modifier: {
                                                page: "all"
                                            },
                                        },
                                    }
                                ],
                                lengthMenu: [
                                    [10, 25, 50, 100, 500, -1],
                                    [10, 25, 50, 100, 500, "All"],
                                ],
                            });
                        },
                         complete: function() {
                            $("#loader-wrapper").hide();
                        },
                        error: function() {
                            alert('कुछ गड़बड़ हो गई। कृपया पुनः प्रयास करें।');
                        }
                    });
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
                    // e.preventDefault();
                    $('#complaintFilterTabs a').removeClass('active');
                    $(this).addClass('active');

                    const filter = $(this).data('filter');

                    $.ajax({
                        url: '{{ route("operator.complaints.view") }}',
                        data: {
                            filter: filter
                        },
                        success: function(response) {
                            $('#complaintsTableBody').html(response.html);
                            $('#complaintCount').text(response.count);
                        }
                    });
                });
            });


            if (performance.navigation.type === 1) {
                $('#complaintFilterForm')[0].reset();

                if (window.location.search) {
                    window.location.href = window.location.origin + window.location.pathname;
                }
            }



              
        </script>
    @endpush
@endsection
