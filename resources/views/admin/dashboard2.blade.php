@php
    $pageTitle = 'सदस्य द्वारा जोड़े गए सदस्य';
    $breadcrumbs = [
        'एडमिन' => '#',
        'सदस्य द्वारा जोड़े गए सदस्य' => '#',
    ];
@endphp

@extends('layouts.app')
@section('title', 'Admin Dashboard2')

@section('content')
    <div class="container">
        <div class="row page-titles mx-0">
            <div class="col-md-12 col-sm-12 col-xs-12">
                <form method="POST" action="{{ route('dashboard2.filter') }}">
                    @csrf
                    <div id="rowGroup">
                        <div class="form-row align-items-end mb-2">
                            <div class="col-md-4">
                                <label>मोबाइल नंबर <span class="text-danger">*</span></label>
                                <input type="text" name="mobile" id="main_mobile" class="form-control">
                            </div>
                            <div class="col-md-6 mt-2" style="color:rgb(55, 64, 75)">
                                <br />
                                <button type="button" id="data-filter" class="btn btn-success mr-4">सर्च करें</button>
                                Data Count: <span id="total">{{ $count }}</span>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="text-center my-3">
                    <button type="button" class="btn btn-primary btn-lg" id="show-data">देखें</button>
                </div>

                <div class="card" id="table_card" style="display: none;">
                    <div class="card-body">
                        <div class="table-responsive" id="filtered_data">
                            <table class="display table table-bordered" style="min-width: 845px" id="example">
                                <thead>
                                    <tr>
                                        <th>Sr.No.</th>
                                        <th>Member ID</th>
                                        <th>Name</th>
                                        <th>Mobile1</th>
                                        <th>Mobile2</th>
                                        <th>Gender</th>
                                        <th>Entry Date</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody id="table_body">
                                    @php $x = 1; @endphp
                                    @foreach ($registrations as $row)
                                        <tr>
                                            <td>{{ $x++ }}</td>
                                            <td>{{ $row->member_id }}</td>
                                            <td>{{ $row->name }}</td>
                                            <td>{{ $row->mbl }}</td>
                                            <td>{{ $row->mobile2 ?? '-' }}</td>
                                            <td>{{ $row->gender ?? '-' }}</td>
                                            <td>{{ \Carbon\Carbon::parse($row->pdate)->format('d-m-Y') }}</td>
                                            <td style="white-space: nowrap;">
                                                <a href="{{ route('register.show', $row->added_member_id) }}"
                                                    class="btn btn-sm btn-success">View</a>
                                                <a href="{{ route('register.card', $row->added_member_id) }}"
                                                    class="btn btn-sm btn-primary">Card</a>
                                                <a href="{{ route('register.destroy', $row->added_member_id) }}"
                                                    class="btn btn-sm btn-danger">Delete</a>
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
                const toggleSearchButton = () => {
                    const hasValue = $("#main_mobile").val().trim() !== '';
                    $("#data-filter").prop('disabled', !hasValue);
                };

                toggleSearchButton(); // initial
                $("#main_mobile").on("input", toggleSearchButton);

                $('#show-data').click(function() {
                    $('#table_card').show(); 
                    $(this).hide(); 
                });

                // $("#show-data").click(function() {
                //     $("#loader-wrapper").show();

                //     $.ajax({
                //         url: "{{ route('dashboard2.filter') }}",
                //         method: "POST",
                //         data: {
                //             _token: "{{ csrf_token() }}"
                //         },
                //         success: function(response) {
                //             $("#loader-wrapper").hide();

                //             if (response.count > 0) {
                //                 // $('#table_body').html(response.html);
                //                 $("#table_card").show();
                //             } else {
                //                 // $('#table_body').html('');
                //                 $("#filtered_data").append(
                //                     '<div class="text-danger">No data found.</div>');
                //                 $("#table_card").show();
                //             }

                //             $("#total").text(response.count);
                //             $('#show-data').hide();
                //         },
                //         error: function() {
                //             $("#loader-wrapper").hide();
                //             alert("Error loading data.");
                //         }
                //     });
                // });

                // "सर्च करें" button
                $("#data-filter").click(function() {
                    $("#loader-wrapper").show();

                    let where = '';
                    const mobile = $("#main_mobile").val().trim();
                    if (mobile !== "") {
                        where = "B.mobile1='" + mobile + "'";
                    }

                    $.ajax({
                        url: "{{ route('dashboard2.filter') }}",
                        method: "POST",
                        data: {
                            _token: "{{ csrf_token() }}",
                            where: where
                        },
                        success: function(response) {
                            $("#loader-wrapper").hide();

                            if (response.count > 0) {
                                $('#table_body').html(response.html);
                                $("#table_card").show();
                            } else {
                                $('#table_body').html('');
                                $("#filtered_data").append(
                                    '<div class="text-danger">No data found.</div>');
                                $("#table_card").show();
                            }

                            $("#total").text(response.count);
                            $('#show-data').hide();
                        },
                        error: function() {
                            $("#loader-wrapper").hide();
                            alert("Error loading data.");
                        }
                    });
                });
            });
        </script>
    @endpush

@endsection

{{-- @push('scripts')
    <script>
        $(document).ready(function() {
            $("#data-filter").click(function() {
                $("#loader-wrapper").show();

                let whereClauses = [];

                if ($("#main_mobile").val() !== "") {
                    whereClauses.push("B.mobile1='" + $("#main_mobile").val() + "'");
                }

                let where = whereClauses.join(" AND ");

                $.ajax({
                    url: "{{ route('dashboard2.filter') }}",
                    method: "POST",
                    data: {
                        _token: "{{ csrf_token() }}",
                        where: where
                    },
                    success: function(response) {
                        $("#loader-wrapper").hide();
                        console.log('AJAX response:', response);

                        if (response.count > 0) {
                            $('#table_body').html(response.html);
                            $("#table_card").show();
                        } else {
                            $('#table_body').html('');
                            $("#filtered_data").append(
                                '<div class="text-danger">No data found.</div>');
                            $("#table_card").show();
                        }

                        $("#total").text(response.count);
                    },
                    error: function(xhr) {
                        $("#loader-wrapper").hide();
                        alert("Error loading data.");
                    }
                });
            });
        });
    </script>
@endpush --}}
