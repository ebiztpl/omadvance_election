@php
    $pageTitle = 'मतदाता डेटा अपलोड';
    $breadcrumbs = [
        'एडमिन' => '#',
        'मतदाता डेटा अपलोड' => '#',
    ];
@endphp

@extends('layouts.app')
@section('title', 'Upload Voter Data')

@section('content')
    <div class="container">

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif


        <div class="row page-titles mx-0">
            <div class="col-md-12 col-sm-12 col-xs-12">
                <a href="{{ route('voters.download') }}" class="btn btn-info">
                    <i class="fa fa-download"></i> Download Sample
                </a>
            </div>
        </div>


        <div class="row page-titles mx-0">
            <div class="col-md-12 col-sm-12 col-xs-12">
                <form action="{{ route('voter.upload') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="form-group">
                        <label for="voter_excel">Upload Filled Excel File</label>
                        <input type="file" name="voter_excel" id="voter_excel" class="form-control"
                            accept=".xlsx,.xls,.csv" required>
                    </div>
                    <button type="submit" class="btn btn-success mt-2">Upload</button>
                </form>
            </div>
        </div>
    </div>
@endsection
