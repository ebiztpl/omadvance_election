@php
$pageTitle = 'सदस्य द्वारा जोड़े गए सदस्य';
$breadcrumbs = [
    'एडमिन' => '#',
    'सदस्य द्वारा जोड़े गए सदस्य' => '#'
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
                            <button type="button" id="data-filter" class="btn btn-success mr-4">Filter Data</button>
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
                                {{-- AJAX injected rows --}}
                            </tbody>
                        </table>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function () {
        $("#data-filter").click(function () {
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
                success: function (response) {
                    $("#loader-wrapper").hide();
                    console.log('AJAX response:', response);

                    if (response.count > 0) {
                        $('#table_body').html(response.html);
                        $("#table_card").show();
                    } else {
                        $('#table_body').html('');
                        $("#filtered_data").append('<div class="text-danger">No data found.</div>');
                        $("#table_card").show();
                    }

                    $("#total").text(response.count);
                },
                error: function (xhr) {
                    $("#loader-wrapper").hide();
                    alert("Error loading data.");
                }
            });
        });
    });
</script>
@endpush
