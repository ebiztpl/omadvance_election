@php
$pageTitle = 'शिकायत का जवाब अपडेट करें';
$breadcrumbs = [
'मैनेजर' => '#',
'शिकायत का जवाब अपडेट करें' => '#'
];
@endphp

@extends('layouts.app')
@section('title', 'Edit Complaint Reply')

@section('content')
<div class="container">
    <div class="row page-titles mx-0">
        <div class="col-md-12 col-sm-12 col-xs-12">
            <h3>शिकायत का जवाब</h3>
            <form method="POST" action="{{ route('complaintReply.update', $reply->reply_id) }}">
                @csrf
                <div class="item form-group row">
                    <div class="col-md-3 col-sm-3 col-xs-12">
                        <label>शिकायत का जवाब<span class="required">*</span></label>
                        <input type="text" name="reply" class="form-control" value="{{ $reply->reply }}" required>
                        @error('reply') <div class="text-danger">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-6 mt-2">
                        <br />
                        <button type="submit" class="btn btn-success">Update</button>
                        <a href="{{ route('complaintReply.index') }}" class="btn btn-secondary">Back</a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection