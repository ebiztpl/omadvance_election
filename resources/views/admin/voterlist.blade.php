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
        <div class="row page-titles mx-0">
            <div class="col-md-12 col-sm-12 col-xs-12">
                <form method="POST" action="{{ route('voterdata.index') }}">
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
                <div class="card" id="table_card" style="display: none">
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

                        <div class="table-responsive" id="filtered_data">
                            <table class="display table table-bordered" style="min-width: 845px" id="example">
                                <thead>
                                    <tr>
                                        <th>S.No.</th>
                                        <th>Member ID</th>
                                        <th>Name</th>
                                        <th>Mobile1</th>
                                        <th>mobile2</th>
                                        <th>Gender</th>
                                        <th>Entry Date</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody id="voter-table-body">

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
    $(document).ready(function () {
        $("#data-filter").click(function () {
            let mobile = $('#main_mobile').val();

            $.ajax({
                url: "{{ route('voterdata.index') }}",
                method: "POST",
                data: {
                    _token: "{{ csrf_token() }}",
                    mobile: mobile
                },
                success: function (response) {
                    $('#voter-table-body').html(response.table_rows);
                    $('#total').text(response.count);
                    $('#table_card').show();
                },
                error: function () {
                    alert("Something went wrong.");
                }
            });
        });
    });
</script>
@endpush






@endsection
