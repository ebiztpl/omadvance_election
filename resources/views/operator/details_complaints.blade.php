@php
    $pageTitle = 'समस्याएँ देखे';
    $breadcrumbs = [
        'ऑपरेटर' => '#',
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
                <span style="color: gray;"> स्थिति: {!! $complaint->statusText() !!}</span>
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
                        'लिंग' => $complaint->registration->gender ?? '',
                        'धर्म' => $complaint->registration->religion ?? '',
                        'वर्ग/श्रेणी' => $complaint->registration->caste ?? '',
                        'जाति' => $complaint->registration->jati ?? '',
                        'शिक्षा' => $complaint->registration->education ?? '',
                        'व्यवसाय' => $complaint->registration->business ?? '',
                        'पद' => $complaint->registration->position ?? '',
                        'शिकायत का दिनांक' => $complaint->posted_date ?? '',
                    ];
                @endphp

                @foreach ($fields as $label => $value)
                    <div class="col-md-4" style="margin-top: 8px;">
                        <label class="form-label">{{ $label }}</label>
                        <input type="text" class="form-control" value="{{ $value }}" disabled>
                    </div>
                @endforeach

                <div class="col-md-4" style="margin-top: 8px;">
                    <label class="form-label">पूरा पता</label>
                    <textarea class="form-control" rows="4" disabled>{{ $complaint->address }}</textarea>
                </div>

                <div class="col-md-4 mt-5" style="justify-content: center; align-items:center">
                    <label class="form-label">फ़ाइल अटैचमेंट</label>
                    @if (!empty($complaint->issue_attachment))
                        <a href="{{ asset('assets/upload/complaints/' . $complaint->issue_attachment) }}"
                            class="btn btn-primary" target="_blank">अटैचमेंट खोलें</a>
                    @else
                         <button class="btn btn-sm btn-secondary" disabled>कोई अटैचमेंट नहीं है</button>
                    @endif
                </div>

                <div class="col-md-6" style="margin-top: 8px;">
                    <label class="form-label">समस्या का विषय</label>
                    <textarea class="form-control" rows="4" disabled>{{ $complaint->issue_title }}</textarea>
                </div>

                <div class="col-md-12" style="margin-top: 8px;">
                    <label class="form-label">समस्या</label>
                    <textarea class="form-control" rows="5" disabled>{{ $complaint->issue_description }}</textarea>
                </div>

                {{-- <div class="col-md-12" style="margin-top: 10px;">
                    <label class="form-label">Attachment</label>
                    @if (!empty($complaint->issue_attachment))
                        <a href="{{ asset('assets/upload/complaints/' . $complaint->issue_attachment) }}"
                            class="btn btn-primary" target="_blank">Open Attachment</a>
                    @else
                        <p>No attachment</p>
                    @endif
                </div> --}}

            </div>
        </div>

        {{-- Reply History --}}
        <div class="card container" style="color: #000; ">
            <h5 class="my-3">Reply History for {{ $complaint->complaint_number }}</h5>
            <div class="row">
                @foreach ($complaint->replies as $reply)
                    <div class="col-md-4 mb-4">
                        <div class="card h-100" style="border: 1px solid black">
                            <div class="card-body">
                                <p><strong>समस्या/समाधान:</strong> {{ $reply->complaint_reply }}</p>

                                <p><strong>दिनांक:</strong>
                                    {{ $reply->reply_date ? \Carbon\Carbon::parse($reply->reply_date)->format('d-m-Y') : 'N/A' }}
                                </p>
                                @php
                                    $replyFromName =
                                        $reply->reply_from == 1 ? $reply->complaint->user->name ?? 'User' : 'BJS Team';
                                @endphp

                                <p><strong>प्रतिक्रिया देने वाला:</strong> {{ $replyFromName }}</p>


                                @if (!empty($reply->cb_photo))
                                    <label class="form-label mr-3"><strong>पूर्व स्थिति की तस्वीर: </strong></label>
                                    <a href="{{ asset($reply->cb_photo) }}" class="btn btn-primary mb-3"
                                        target="_blank">अटैचमेंट खोलें</a>
                                @endif

                                @if (!empty($reply->ca_photo))
                                    <label class="form-label mr-4"><strong>बाद की तस्वीर: </strong></label>
                                    <a href="{{ asset($reply->ca_photo) }}" class="btn btn-primary"
                                        target="_blank">अटैचमेंट खोलें</a>
                                @endif

                                @if (!empty($reply->c_video))
                                    <label class="form-label mr-4"><strong>यूट्यूब लिंक: </strong></label>
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
                <form method="POST" action="{{ route('operator_complaint.reply', $complaint->complaint_id) }}"
                    enctype="multipart/form-data">
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label">समस्या/समाधान में प्रगति</label>
                            <textarea name="cmp_reply" id="cmp_reply" class="form-control" rows="6" required></textarea>
                        </div>

                        <div class="col-md-2">
                            <label class="form-label">पूर्व स्थिति की तस्वीर</label>
                            <input type="file" name="cb_photo[]" class="form-control" multiple accept="image/*">
                            <label class="form-label mt-3">बाद की तस्वीर</label>
                            <input type="file" name="ca_photo[]" class="form-control" multiple accept="image/*">
                        </div>

                        <div class="col-md-2">
                            <label class="form-label">यूट्यूब लिंक</label>
                            <input type="url" name="c_video" class="form-control">
                            <label class="form-label mt-3">शिकायत की स्थिति: <span class="tx-danger"
                                    style="font-size: 11px; color: red;">*</span></label>
                            <select name="cmp_status" class="form-control" required>
                                <option value="">--चुने--</option>
                                <option value="1" {{ $complaint->complaint_status == 1 ? 'selected' : '' }}>शिकायत
                                    दर्ज
                                </option>
                                <option value="2" {{ $complaint->complaint_status == 2 ? 'selected' : '' }}>प्रक्रिया
                                    में
                                </option>
                                <option value="3" {{ $complaint->complaint_status == 3 ? 'selected' : '' }}>स्थगित
                                </option>
                                <option value="4" {{ $complaint->complaint_status == 4 ? 'selected' : '' }}>पूर्ण
                                </option>
                                <option value="5" {{ $complaint->complaint_status == 5 ? 'selected' : '' }}>रद्द
                                </option>
                            </select>
                        </div>

                        <div class="col-12 mt-3">
                            <button type="submit" class="btn btn-primary">शिकायत दर्ज करें</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>



    @push('scripts')
        <script>
            document.addEventListener("DOMContentLoaded", function() {
                pramukhIME.enable({
                    language: 'hindi',
                    elements: ["cmp_reply"]
                });
            });
        </script>
    @endpush
@endsection
