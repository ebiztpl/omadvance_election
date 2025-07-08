@php
$pageTitle = 'सदस्य जन्मदिन';
$breadcrumbs = [
'एडमिन' => '#',
'सदस्य जन्मदिन' => '#'
];
@endphp

@extends('layouts.app')
@section('title', 'Member Birthday')


@section('content')
<div class="container">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="example" style="min-width: 845px" class="display table-bordered">
                            <thead>
                                <tr>
                                    <th>Sr.No.</th>
                                    <th>Member ID</th>
                                    <th>Name</th>
                                    <th>Mobile-1</th>
                                    <th>Gender</th>
                                    <th>Entry Date</th>
                                    <th>DOB</th>
                                    <th>Birthday</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($birthdays as $index => $b)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td><a class="member-link" href="{{ route('register.show', ['id' => $b->registration_id]) }}">{{ $b->member_id }}</a>
                                    </td>
                                    <td>{{ $b->name }}</td>
                                    <td>{{ $b->mobile1 }}</td>
                                    <td>{{ $b->gender }}</td>
                                    <td>{{ $b->date_time }}</td>
                                    <td>{{ $b->dob }}</td>
                                    <td>{{ $b->currbirthday }}</td>
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
@endsection