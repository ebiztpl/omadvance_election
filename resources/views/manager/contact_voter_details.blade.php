@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row page-titles mx-0">
            <div class="col-md-12 col-sm-12 col-xs-12">
                <h4>{{ $title }}</h4>
                <div class="card">
                    <div class="card-body">

                        <div class="table-responsive">
                            <button id="download_full_data" class="btn btn-primary mb-3" style="float: left">
                                पूरा डेटा डाउनलोड करें
                            </button>
                            <span
                                style="margin-bottom: 8px; font-size: 18px; color: green; text-align: right; margin-left: 50px; float: right">
                                कुल - <span id="complaint-count">लोड हो रहा है...</span>
                            </span>
                            <table id="example" class="display table-bordered">
                                <thead>
                                    <tr>
                                        <th>क्र.</th>
                                        <th>नाम</th>
                                        <th>पिता/पति</th>
                                        <th>मकान क्र.</th>
                                        <th>उम्र</th>
                                        <th>लिंग</th>
                                        <th>मतदाता आईडी</th>
                                        <th>मतदान क्षेत्र</th>
                                        <th>जाति</th>
                                        <th>मतदान क्र.</th>
                                        <th>कुल सदस्य</th>
                                        <th>मुखिया मोबाइल</th>
                                        <th>मृत्यु/स्थानांतरित</th>
                                        <th>दिनांक</th>
                                        <th>क्रिया</th>
                                    </tr>
                                </thead>
                                {{-- <tbody>
                                  @php $i = 1; @endphp
                                @foreach ($entries as $voter)
                                   <tr>
                                            <td>{{ $i++ }}</td>
                                            <td>{{ $voter->name }}</td>
                                            <td>{{ $voter->father_name }}</td>
                                            <td>{{ $voter->step2->house ?? '' }}</td>
                                            <td>{{ $voter->age }}</td>
                                            <td>{{ $voter->gender }}</td>
                                            <td>{{ $voter->voter_id }}</td>
                                            <td>{{ $voter->step2->area->area_name ?? '-' }}</td>
                                            <td>{{ $voter->jati }}</td>
                                            <td>{{ $voter->step2->matdan_kendra_no ?? '' }}</td>
                                            <td>{{ $voter->step3->total_member ?? '' }}</td>
                                            <td>{{ $voter->step3->mukhiya_mobile ?? '' }}</td>
                                            <td>{{ $voter->{'death/left'} ?? '' }}</td>
                                            <td>{{ \Carbon\Carbon::parse($voter->date_time)->format('d-m-Y') }}</td>
                                            <td style="white-space: nowrap;">
                                                <a href="{{ route('voter.show', $voter->registration_id) }}"
                                                    class="btn btn-sm btn-success mr-1">View</a>
                                                <form action="{{ route('register.destroy', $voter->registration_id) }}"
                                                    method="POST" style="display: inline-block;"
                                                    onsubmit="return confirm('क्या आप वाकई रिकॉर्ड हटाना चाहते हैं?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                                </form>
                                            </td>
                                        </tr>
                                @endforeach
                            </tbody> --}}
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
                const urlParams = new URLSearchParams(window.location.search);
                const filter = urlParams.get('filter');

                if ($.fn.DataTable.isDataTable('#example')) {
                    $('#example').DataTable().clear().destroy();
                }

                $('#example').DataTable({
                    processing: true,
                    serverSide: true,
                    ajax: '{{ url()->full() }}',
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
                    columns: [{
                            title: "क्र."
                        },
                        {
                            title: "नाम"
                        },
                        {
                            title: "पिता/पति"
                        },
                        {
                            title: "मकान क्र."
                        },
                        {
                            title: "उम्र"
                        },
                        {
                            title: "लिंग"
                        },
                        {
                            title: "मतदाता आईडी"
                        },
                        {
                            title: "मतदान क्षेत्र"
                        },
                        {
                            title: "जाति"
                        },
                        {
                            title: "मतदान क्र."
                        },
                        {
                            title: "कुल सदस्य"
                        },
                        {
                            title: "मुखिया मोबाइल"
                        },
                        {
                            title: "मृत्यु/स्थानांतरित"
                        },
                        {
                            title: "दिनांक"
                        },
                        {
                            title: "क्रिया",
                            orderable: false,
                            searchable: false
                        }
                    ],
                    language: {
                        url: "//cdn.datatables.net/plug-ins/1.13.4/i18n/hi.json",
                        processing: "लोड हो रहा है..."
                    },
                    drawCallback: function(settings) {
                        $('#complaint-count').text(settings._iRecordsTotal);
                    },
                    preDrawCallback: function() {
                        $('#complaint-count').text('लोड हो रहा है...');
                    }
                });
                $('#example').on('preXhr.dt', function() {
                    $("#loader-wrapper").show();
                });

                $('#example').on('xhr.dt', function() {
                    $("#loader-wrapper").hide();
                });

                $("#download_full_data").off('click').on('click', function() {
                    if (filter) {
                        $("#loader-wrapper").show();
                        window.location.href = `{{ route('voterlistdashboard.download') }}?filter=${filter}`;
                    } else {
                        alert("Filter parameter missing from URL!");
                    }
                });


            });
        </script>
    @endpush
@endsection
