@php
    $pageTitle = 'मतदाता डेटा';
    $breadcrumbs = [
        'एडमिन' => '#',
        'मतदाता डेटा' => '#',
    ];
@endphp

@extends('layouts.app')
@section('title', 'View Voter List')

@section('content')
    <div class="container">
        @foreach (['success', 'update_msg', 'delete_msg'] as $msg)
            @if (session($msg))
                <div class="alert alert-{{ $msg == 'delete_msg' ? 'danger' : 'success' }} alert-dismissible fade show">
                    {{ session($msg) }}
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                </div>
            @endif
        @endforeach

        <div class="row page-titles mx-0">
            <div class="col-md-12 col-sm-12 col-xs-12">
                <form method="POST" action="{{ route('voterdata.index') }}">
                    @csrf
                    <div id="rowGroup">
                        <div class="form-row align-items-end mb-2">
                            
                            <div class="col-md-4">
                                <label>मतदाता आईडी सर्च करें <span class="text-danger">*</span></label>
                                <input type="text" name="voter_id" id="main_voter_id" class="form-control">
                            </div>
                            <div class="col-md-6 mt-2" style="color:rgb(55, 64, 75)">
                                <br />
                                <button type="button" id="data-filter" class="btn btn-success mr-3" disabled>सर्च
                                    करें</button>
                                Data Count: <span id="total">{{ $total }}</span>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>


        <div class="row">
            <div class="col-12">
                <div class="text-center my-4">
                    <button type="button" id="show-voters" class="btn btn-primary btn-lg">मतदाता देखें</button>
                </div>
                <div class="card" id="table_card" style="display: none">
                    <div class="card-body">
                        <div class="table-responsive" id="filtered_data">
                            <table class="display table table-bordered" style="min-width: 845px" id="example">
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
                                <tbody id="voter-table-body">
                                    @php $i = 1; @endphp
                                    @foreach ($voters as $voter)
                                        <tr>
                                            <td>{{ $i++ }}</td>
                                            <td>{{ $voter->name }}</td>
                                            <td>{{ $voter->father_name }}</td>
                                            <td>{{ $voter->step2->house ?? '' }}</td>
                                            <td>{{ $voter->age }}</td>
                                            <td>{{ $voter->gender }}</td>
                                            <td>{{ $voter->voter_id }}</td>
                                            <td>{{ $voter->area_name }}</td>
                                            <td>{{ $voter->jati }}</td>
                                            <td>{{ $voter->step2->matdan_kendra_no ?? '' }}</td>
                                            <td>{{ $voter->step3->total_member ?? '' }}</td>
                                            <td>{{ $voter->step3->mukhiya_mobile ?? '' }}</td>
                                            <td>{{ $voter->{'death/left'} ?? '' }}</td>
                                            <td>{{ \Carbon\Carbon::parse($voter->date_time)->format('d-m-Y') }}</td>
                                            <td style="white-space: nowrap;">
                                                <a href="{{ route('voter.show', $voter->registration_id) }}"
                                                    class="btn btn-sm btn-success mr-1">View</a>
                                                {{-- <a href="{{ route('register.show', $voter->registration_id) }}"
                                                    class="btn btn-sm btn-primary mr-1">Edit</a> --}}
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
                $('#main_voter_id').on('input', function() {
                    const hasValue = $(this).val().trim().length > 0;
                    $('#data-filter').prop('disabled', !hasValue);
                });

                // $('#show-voters').click(function() {
                //     $("#loader-wrapper").show();
                //     $.ajax({
                //         url: "{{ route('viewvoter.index') }}",
                //         method: "POST",
                //         data: {
                //             _token: "{{ csrf_token() }}",
                //             voter_id: ""
                //         },
                //         success: function(response) {
                //             $("#loader-wrapper").hide();
                //             $('#total').text(response.count);
                //             $('#table_card').show();
                //             $('#show-voters').hide();
                //         },
                //         error: function() {
                //             $("#loader-wrapper").hide();
                //             alert("Something went wrong.");
                //         }
                //     });
                // });

                $('#show-voters').click(function() {
                    $('#table_card').show();
                    $(this).hide();
                });

                // Filter by Voter ID
                $('#data-filter').click(function() {
                    let voter_id = $('#main_voter_id').val().trim();
                    if (voter_id === '') return;
                    $("#loader-wrapper").show();

                    $.ajax({
                        url: "{{ route('voterdata.index') }}",
                        method: "POST",
                        data: {
                            _token: "{{ csrf_token() }}",
                            voter_id: voter_id
                        },
                        success: function(response) {
                            $("#loader-wrapper").hide();
                            $('#voter-table-body').html(response.table_rows);
                            $('#total').text(response.count);
                            $('#table_card').show();
                        },
                        error: function() {
                            $("#loader-wrapper").hide();
                            alert("Something went wrong.");
                        }
                    });
                });
            });
        </script>
    @endpush

@endsection
