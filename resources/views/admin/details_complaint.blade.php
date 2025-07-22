@php
    $pageTitle = 'समस्याएँ देखे';
    $breadcrumbs = [
        'एडमिन' => '#',
        'समस्याएँ देखे' => '#',
    ];
@endphp

@extends('layouts.app')
@section('title', 'View Complaints')

@section('content')
    <div class="container">

        <div class="card mb-3">
            <div class="card-header d-flex justify-content-between">
                <h5>{{ $complaint->complaint_number }}</h5>
                <span style="color: gray;">Status: {!! $complaint->statusText() !!}</span>
            </div>
            <div class="card-body row g-3">
                @php

                    $fields = [
                        'शिकायतकर्ता का नाम' => $complaint->name,
                        'शिकायतकर्ता का मोबाइल' => $complaint->email,
                        'पदाधिकारी का मोबाइल' => $complaint->mobile_number,
                        'मतदाता पहचान' => $complaint->voter_id,
                        'संभाग का नाम' => $complaint->division->division_name ?? '',
                        'जिले का नाम' => $complaint->district->district_name ?? '',
                        'विधानसभा का नाम' => $complaint->vidhansabha->vidhansabha ?? '',
                        'मंडल का नाम' => $complaint->mandal->mandal_name ?? '',
                        'नगर केंद्र/ग्राम केंद्र' => $complaint->gram->nagar_name ?? '',
                        'मतदान केंद्र' =>
                            ($complaint->polling->polling_name ?? '') . '-' . $complaint->polling->polling_no,
                        'ग्राम चौपाल/वार्ड चौपाल' => $complaint->area->area_name ?? '',
                        'Gender' => $complaint->registration->gender ?? '',
                        'Religion' => $complaint->registration->religion ?? '',
                        'Caste' => $complaint->registration->caste ?? '',
                        'Jati' => $complaint->registration->jati ?? '',
                        'Education' => $complaint->registration->education ?? '',
                        'Business' => $complaint->registration->business ?? '',
                        'Position' => $complaint->registration->position ?? '',
                        'शिकायत का दिनांक' => $complaint->posted_date,
                    ];
                @endphp

                @foreach ($fields as $label => $value)
                    <div class="col-md-4" style="margin-top: 8px;">
                        <label class="form-label">{{ $label }}</label>
                        <input type="text" class="form-control" value="{{ $value }}" disabled>
                    </div>
                @endforeach

                <div class="col-md-6" style="margin-top: 8px;">
                    <label class="form-label">पूरा पता</label>
                    <textarea class="form-control" rows="4" disabled>{{ $complaint->address }}</textarea>
                </div>

                <div class="col-md-6" style="margin-top: 8px;">
                    <label class="form-label">समस्या का विषय</label>
                    <textarea class="form-control" rows="4" disabled>{{ $complaint->issue_title }}</textarea>
                </div>

                <div class="col-md-12" style="margin-top: 8px;">
                    <label class="form-label">समस्या</label>
                    <textarea class="form-control" rows="5" disabled>{{ $complaint->issue_description }}</textarea>
                </div>

                <div class="col-md-12" style="margin-top: 10px;">
                    <label class="form-label">Attachment</label>
                    @if (!empty($complaint->issue_attachment))
                        <a href="{{ asset('assets/upload/complaints/' . $complaint->issue_attachment) }}"
                            class="btn btn-primary" target="_blank">Open Attachment</a>
                    @else
                        <p>No attachment</p>
                    @endif
                </div>

            </div>
        </div>

        {{-- Reply History --}}
        <div class="card container" style="color: #000; ">
            <h5 class="my-3">Reply History for {{ $complaint->complaint_number }}</h5>
            <div class="row">
                @foreach ($complaint->replies as $reply)
                    <div class="col-md-4 mb-4">
                        <div class="card h-100">
                            <div class="card-body">
                                <p><strong>Date:</strong>
                                    {{ $reply->reply_date ? \Carbon\Carbon::parse($reply->reply_date)->format('d-m-Y') : 'N/A' }}
                                </p>
                                @php
                                    $replyFromName =
                                        $reply->reply_from == 1 ? $reply->complaint->user->name ?? 'User' : 'BJS Team';
                                @endphp

                                <p><strong>Reply Given By:</strong> {{ $replyFromName }}</p>
                                <p><strong>समस्या/समाधान:</strong> {{ $reply->complaint_reply }}</p>

                                @if (!empty($reply->cb_photo))
                                    <label class="form-label mr-3">Before Images: </label>
                                    <a href="{{ asset($reply->cb_photo) }}" class="btn btn-primary mb-3"
                                        target="_blank">Open Attachment</a>
                                @endif

                                @if (!empty($reply->ca_photo))
                                    <label class="form-label mr-4">After Images: </label>
                                    <a href="{{ asset($reply->ca_photo) }}" class="btn btn-primary" target="_blank">Open
                                        Attachment</a>
                                @endif

                                @if (!empty($reply->c_video))
                                    <label class="form-label mr-4">Youtube Link: </label>
                                    <a href="{{ asset($reply->c_video) }}" class="btn btn-primary"
                                        target="_blank">{{ $reply->c_video }}</a>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>


        {{-- Reply Form --}}
        <div class="card">
            <div class="card-header" style="color: #000">Reply to {{ $complaint->complaint_number }}</div>
            <div class="card-body">
                <form method="POST" action="{{ route('complaints.reply', $complaint->complaint_id) }}"
                    enctype="multipart/form-data">
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label">समस्या/समाधान में प्रगति</label>
                            <textarea name="cmp_reply" class="form-control" rows="6" required></textarea>
                        </div>

                        <div class="col-md-2">
                            <label class="form-label">Before Photo</label>
                            <input type="file" name="cb_photo[]" class="form-control" multiple accept="image/*">
                            <label class="form-label mt-3">After Photo</label>
                            <input type="file" name="ca_photo[]" class="form-control" multiple accept="image/*">
                        </div>

                        <div class="col-md-2">
                            <label class="form-label">YouTube Link</label>
                            <input type="url" name="c_video" class="form-control">
                            <label class="form-label mt-3">Complaint Status: <span class="tx-danger"
                                    style="font-size: 11px; color: red;">*</span></label>
                            <select name="cmp_status" class="form-control" required>
                                <option value="">Select</option>
                                <option value="1" {{ $complaint->status == 1 ? 'selected' : '' }}>Opened</option>
                                <option value="2" {{ $complaint->status == 2 ? 'selected' : '' }}>Processing</option>
                                <option value="3" {{ $complaint->status == 3 ? 'selected' : '' }}>On Hold</option>
                                <option value="4" {{ $complaint->status == 4 ? 'selected' : '' }}>Closed</option>
                            </select>
                        </div>

                        <div class="col-12 mt-3">
                            <button type="submit" class="btn btn-primary">Post Reply</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
