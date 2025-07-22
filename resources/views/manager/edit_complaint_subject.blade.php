@php
$pageTitle = 'शिकायत का विषय अपडेट करें';
$breadcrumbs = [
'मैनेजर' => '#',
'शिकायत का विषय अपडेट करें' => '#'
];
@endphp

@extends('layouts.app')
@section('title', 'Edit Compalint subject')

@section('content')
<div class="container">

    <div class="row page-titles mx-0">
        <div class="col-md-12 col-sm-12 col-xs-12">
            <h3>शिकायत का विषय</h3>
            <form method="POST" action="{{ route('complaintSubject.update', $subject->subject_id) }}">
                @csrf
                <div class="item form-group row">
                    <div class="col-md-3 col-sm-3 col-xs-12">
                        <label>विभाग <span class="required">*</span></label>
                        <select name="department_id" class="form-control" required>
                            @foreach($departments as $department)
                            <option value="{{ $department->department_id }}" {{ $subject->department_id == $department->department_id ? 'selected' : '' }}>
                                {{ $department->department_name }}
                            </option>
                            @endforeach
                        </select>
                        @error('department_id') <div class="text-danger">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-3 col-sm-3 col-xs-12">
                        <label>शिकायत का विषय<span class="required">*</span></label>
                        <input type="text" name="subject" class="form-control" value="{{ $subject->subject }}" required>
                        @error('subject') <div class="text-danger">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-6 mt-2">
                        <br />
                        <button type="submit" class="btn btn-success">Update</button>
                        <a href="{{ route('complaintSubject.master') }}" class="btn btn-primary ml-2">Back</a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection