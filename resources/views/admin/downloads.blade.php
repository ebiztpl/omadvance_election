@extends('layouts.app')

@section('content')
<div class="container">
    <h3>Generated CSV Files</h3>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>File Name</th>
                <th>Status</th>
                <th>Download</th>
            </tr>
        </thead>
        <tbody>
            @foreach($files as $file)
            <tr>
                <td>{{ $file->file_name }}</td>
                <td>{{ $file->status }}</td>
                <td>
                    @if($file->status === 'completed')
                        <a href="{{ route('voterlist.file', $file->id) }}" class="btn btn-primary">Download</a>
                    @else
                        Pending
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
