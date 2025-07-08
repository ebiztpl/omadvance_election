@php
$pageTitle = 'पासवर्ड बदलें';
$breadcrumbs = [
'पासवर्ड बदलें' => '#'
];
@endphp

@extends('layouts.app')
@section('title', 'Change Password')


@section('content')
<div class="container">


    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
    @endif

    

    @if($errors->any())
    <div class="alert alert-danger">
        @foreach($errors->all() as $err)
        <div>{{ $err }}</div>
        @endforeach
    </div>
    @endif

    <form method="POST" action="{{ route('change-password') }}">
        @csrf

        <div class="form-group">
            <label>Old Password</label>
            <input type="password" name="old_password" class="form-control">
        </div>

        <div class="form-group">
            <label>New Password</label>
            <input type="password" name="new_password" class="form-control">
        </div>

        <div class="form-group">
            <label>Confirm New Password</label>
            <input type="password" name="new_password_confirmation" class="form-control">
        </div>

        <button type="submit" class="btn btn-primary">Change Password</button>
    </form>

</div>
@endsection