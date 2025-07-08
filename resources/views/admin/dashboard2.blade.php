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

                    <form method="post" action="{{ route('dashboard2.download') }}" class="mb-5">
                        @csrf
                        <input type="hidden" name="download_data_whr" id="download_data_whr" value="">
                        <button type="submit" name="download" class="btn btn-danger pull-right">
                            <i class="fa fa-download"></i> Download Filter Data
                        </button>
                    </form>

                    <div id="filtered_data" class="table-responsive">
                        <table class="display table table-bordered" style="min-width: 845px" id="example">
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>



@push('scripts')
<script>
    $("#data-filter").click(function() {
        $("#loader-wrapper").show();

        var whereClauses = [];

        if ($("#main_mobile").val() != "") {
            whereClauses.push("B.mobile1='" + $("#main_mobile").val() + "'");
        }

        var where = whereClauses.join(" AND ");

        $("#download_data_whr").val(where);

        $.ajax({
            url: "{{ route('dashboard2.filter') }}",
            type: "POST",
            data: {
                _token: "{{ csrf_token() }}",
                where: where
            },
            success: function(response) {
                if (response.count > 0) {
                    $("#filtered_data").html(response.html);
                    $('#example').DataTable({
                        destroy: true,
                        responsive: true
                    });
                    $("#table_card").show();
                } else {
                    $("#filtered_data").html('<div class="text-danger">No data found.</div>');
                    $("#table_card").show();
                }

                $("#total").text(response.count);
                $("#loader-wrapper").hide();
            },
            error: function() {
                $("#loader-wrapper").hide();
            }
        });
    });
</script>


<script>
    // This script ensures that download uses the latest WHERE condition
    $("form[action='{{ route('dashboard2.download') }}']").submit(function(e) {
        let whereClauses = [];

        if ($("#main_mobile").val() !== "") {
            whereClauses.push("B.mobile1='" + $("#main_mobile").val() + "'");
        }

        const whereStr = whereClauses.join(" AND ");

        if (!whereStr) {
            e.preventDefault();
            alert("Please apply a filter before downloading.");
            return;
        }

        $("#download_data_whr").val(whereStr);
    });
</script>

@endpush

@endsection