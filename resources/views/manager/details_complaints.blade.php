@php
    $pageTitle = 'समस्याएँ देखे';
    $breadcrumbs = [
        'मैनेजर' => '#',
        'समस्याएँ देखे' => '#',
    ];
@endphp

@extends('layouts.app')
@section('title', 'View Complaints')

@section('content')
    <div class="container">

        <div id="success-alert" class="alert alert-success alert-dismissible fade show d-none" role="alert">
            <span id="success-message"></span>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>

        <div class="card mb-3">
            <div class="card-header d-flex justify-content-between">
                <h5>{{ $complaint->complaint_number }}</h5>
                <span style="color: gray;">स्थिति: {!! $complaint->statusText() !!}</span>
            </div>
            <div class="col-md-12 col-sm-12 col-xs-12">
                <form id="updateComplaintForm" method="POST" enctype="multipart/form-data"
                    action="{{ route('complaints.update', $complaint->complaint_id) }}">
                    @csrf

                    {{-- Type Selection --}}
                    <div class="form-group row justify-content-center">
                        <div class="col-md-12 d-flex justify-content-center" style="padding-top:15px;">
                            <div id="type_row" class="d-flex justify-content-center flex-wrap">
                                @php
                                    $types = [
                                        'शुभ सुचना' => ['text' => 'सूचनाकर्ता का नाम', 'heading' => 'शुभ सूचना'],
                                        'अशुभ सुचना' => ['text' => 'सूचनाकर्ता का नाम', 'heading' => 'अशुभ सूचना'],
                                        'समस्या' => ['text' => 'शिकायतकर्ता का नाम', 'heading' => 'समस्या'],
                                        'विकास' => ['text' => 'मांगकर्ता का नाम', 'heading' => 'विकास'],
                                    ];
                                @endphp
                                @foreach ($types as $type => $data)
                                    <label class="btn btn-success text-white m-1">
                                        <input type="radio" name="type" value="{{ $type }}" class="check"
                                            data-text="{{ $data['text'] }}" data-legend="{{ $data['heading'] }}"
                                            {{ $complaint->complaint_type == $type ? 'checked' : '' }}
                                            style="width: 25px; height: 25px;" required>
                                        <br>{{ $type }}
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <div id="form_container" style="color: #000;">
                        <fieldset class="scheduler-border mb-3">
                            <div class="form-group row">
                                <div class="col-md-4 mb-3 d-flex align-items-center">
                                    <label class="me-2 mr-2 mb-0" style="white-space: nowrap;">
                                        <span class="data-text">नाम</span> <span class="error">*</span>
                                    </label>
                                    <input type="text" class="form-control" name="txtname"
                                        value="{{ old('txtname', $complaint->name) }}" required>
                                </div>

                                <div class="col-md-4 mb-3 d-flex align-items-center">
                                    <label class="me-2 mr-2 mb-0" style="white-space: nowrap;">
                                        <span class="mobile-label">मोबाइल</span>
                                    </label>
                                    <input type="text" class="form-control" name="mobile"
                                        value="{{ old('mobile', $complaint->mobile_number) }}">
                                </div>

                                <div class="col-md-4 mb-3 d-flex align-items-center">
                                    <label class="me-2 mr-2 mb-0" style="white-space: nowrap;">
                                        मतदाता पहचान <span class="error">*</span>
                                    </label>
                                    <input type="text" class="form-control" name="voter"
                                        value="{{ old('voter', $complaint->voter_id) }}" required>
                                </div>



                                <div class="col-md-4 mb-3 d-flex align-items-center">
                                    <label class="me-2 mr-2 mb-0" style="white-space: nowrap;">संभाग का नाम <span
                                            class="error">*</span></label>
                                    <select class="form-control bg-light text-muted" name="division_id" id="division_id"
                                        disabled required>
                                        <option value="{{ $complaint->division_id }}">
                                            {{ $complaint->division->division_name ?? 'Current' }}
                                        </option>
                                    </select>
                                    <input type="hidden" name="division_id" value="2">
                                </div>

                                <div class="col-md-4 mb-3 d-flex align-items-center">
                                    <label class="me-2 mr-2 mb-0" style="white-space: nowrap;">जिले का नाम <span
                                            class="error">*</span></label>
                                    <select class="form-control bg-light text-muted" name="txtdistrict_name"
                                        id="district_name" disabled required>
                                        <option value="{{ $complaint->district_id }}">
                                            {{ $complaint->district->district_name ?? 'Current' }}
                                        </option>
                                    </select>
                                    <input type="hidden" name="txtdistrict_name" value="11">
                                </div>

                                <div class="col-md-4 mb-3 d-flex align-items-center">
                                    <label class="me-2 mr-2 mb-0" style="white-space: nowrap;">विधानसभा <span
                                            class="error">*</span></label>
                                    <select name="txtvidhansabha" class="form-control bg-light text-muted"
                                        id="txtvidhansabha" disabled required>
                                        <option value="{{ $complaint->vidhansabha_id }}">
                                            {{ $complaint->vidhansabha->vidhansabha ?? 'Current' }}
                                        </option>
                                    </select>
                                    <input type="hidden" name="txtvidhansabha" value="49">
                                </div>


                                <div class="col-md-4 mb-3 d-flex align-items-center">
                                    <label class="me-2 mr-2 mb-0" style="white-space: nowrap;">नगर/मंडल<span
                                            class="error">*</span></label>
                                    <select name="txtgram" class="form-control" id="txtgram" required>
                                        <option value="">--चुने--</option>
                                        @foreach ($nagars as $nagar)
                                            <option value="{{ $nagar->nagar_id }}"
                                                {{ $nagar->nagar_id == $complaint->gram_id ? 'selected' : '' }}>
                                                {{ $nagar->nagar_name }} - {{ $nagar->mandal->mandal_name ?? '' }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>


                                <div class="col-md-4 mb-3 d-flex align-items-center">
                                    <label class="me-2 mr-2 mb-0" style="white-space: nowrap;">मतदान
                                        केंद्र/ग्राम/वार्ड<span class="error">*</span></label>
                                    <select name="txtpolling" class="form-control" id="txtpolling" required>
                                        <option value="{{ $complaint->polling_id }}">
                                            {{ $complaint->polling->polling_name ?? '' }}
                                            ({{ $complaint->polling->polling_no ?? '' }}) -
                                            {{ $complaint->polling->area->area_name ?? '' }}
                                        </option>
                                    </select>
                                </div>

                                {{-- <div class="col-md-4 mb-3">
                                    <label>नगर/ग्राम <span class="error">*</span></label>
                                    <select name="txtgram" class="form-control" id="txtgram" required>
                                        <option value="{{ $complaint->gram_id }}">
                                            {{ $complaint->gram->nagar_name ?? 'Current' }}
                                        </option>
                                        <option value="">--चुने--</option>
                                        @foreach ($nagars as $nagar)
                                            <option value="{{ $nagar->nagar_id }}"
                                                {{ $nagar->nagar_id == $complaint->gram_id ? 'selected' : '' }}>
                                                {{ $nagar->nagar_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label>मंडल <span class="error">*</span></label>
                                    <select name="txtmandal" class="form-control" id="txtmandal" required>
                                        <option value="{{ $complaint->mandal_id }}">
                                            {{ $complaint->mandal->mandal_name ?? 'Current' }}
                                        </option>
                                    </select>
                                </div> --}}

                                {{-- <div class="col-md-4 mb-3">
                                    <label>विधानसभा <span class="error">*</span></label>
                                    <select name="txtvidhansabha" class="form-control" id="txtvidhansabha" required>
                                        <option value="{{ $complaint->vidhansabha_id }}">
                                            {{ $complaint->vidhansabha->vidhansabha ?? 'Current' }}
                                        </option>
                                    </select>
                                </div>


                                <div class="col-md-4 mb-3">
                                    <label>जिले का नाम <span class="error">*</span></label>
                                    <select class="form-control" name="txtdistrict_name" id="district_name" required>
                                        <option value="{{ $complaint->district_id }}">
                                            {{ $complaint->district->district_name ?? 'Current' }}
                                        </option>
                                    </select>
                                </div>


                                <div class="col-md-4 mb-3">
                                    <label>संभाग का नाम <span class="error">*</span></label>
                                    <select class="form-control" name="division_id" id="division_id" required>
                                        <option value="{{ $complaint->division_id }}">
                                            {{ $complaint->division->division_name ?? 'Current' }}
                                        </option>
                                    </select>
                                </div> --}}

                                {{-- <div class="col-md-4 mb-3">
                                    <label>मतदान केंद्र <span class="error">*</span></label>
                                    <select name="txtpolling" class="form-control" id="txtpolling" required>
                                        <option value="{{ $complaint->polling_id }}">
                                            {{ $complaint->polling->polling_name ?? '' }} -
                                            {{ $complaint->polling->polling_no ?? '' }}
                                        </option>
                                    </select>
                                </div> --}}

                                {{-- <div class="col-md-4 mb-3">
                                    <label>ग्राम/वार्ड चौपाल <span class="error">*</span></label>
                                    <select name="txtarea" class="form-control" id="txtarea" required>
                                        <option value="{{ $complaint->area_id }}">
                                            {{ $complaint->area->area_name ?? 'Current' }}
                                        </option>
                                    </select>
                                </div> --}}

                                <div class="col-md-4 mb-3 d-flex align-items-center">
                                    <label class="me-2 mr-2 mb-0" style="white-space: nowrap;">
                                        @if ($complaint->type == 1)
                                            कमांडर द्वारा भेजा गया वीडियो
                                        @else
                                            कार्यालय द्वारा भेजी गई फ़ाइल
                                        @endif
                                    </label>

                                    @if ($complaint->issue_attachment)
                                        <a href="{{ asset('assets/upload/complaints/' . $complaint->issue_attachment) }}"
                                            class="btn btn-sm btn-info" target="_blank">अटैचमेंट खोलें</a>
                                    @else
                                        <button class="btn btn-sm btn-secondary" disabled>कोई अटैचमेंट नहीं है</button>
                                    @endif
                                </div>
                            </div>

                            {{-- Detail Section --}}
                            <fieldset class="scheduler-border fieldset-bordered">
                                <legend class="scheduler-border dynamic-legend">विवरण</legend>

                                {{-- Department --}}
                                <div class="form-group row department_row">
                                    <div class="col-md-4 d-flex align-items-center">
                                        <label for="department-select" class="me-2 mr-2 mb-0"
                                            style="white-space: nowrap;">विभाग <span class="error">*</span></label>
                                        <select name="department" id="department-select" class="form-control">
                                            <option value="">--चुने--</option>
                                            @foreach ($departments as $department)
                                                <option value="{{ $department->department_name }}"
                                                    {{ old('department', $complaint->complaint_department ?? '') == $department->department_name ? 'selected' : '' }}>
                                                    {{ $department->department_name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="col-md-4 d-flex align-items-center">
                                        <label for="post-select" class="me-2 mr-2 mb-0" style="white-space: nowrap;">पद
                                            <span class="error">*</span></label>
                                        <select name="post" class="form-control" id="post-select" required>
                                            @if (!empty($complaint->complaint_designation))
                                                <option value="{{ $complaint->complaint_designation }}" selected>
                                                    {{ $complaint->complaint_designation }}
                                                </option>
                                            @else
                                                <option value="">--चुने--</option>
                                            @endif
                                        </select>
                                    </div>

                                    <div class="col-md-4 d-flex align-items-center">
                                        <label for="subject-select" class="me-2 mr-2 mb-0"
                                            style="white-space: nowrap;">विषय
                                            <span class="error">*</span></label>
                                        <select placeholder="हिंदी में टाइप करने के लिए कृपया हिंदी कीबोर्ड चालू करें"
                                            name="CharCounter" id="subject-select" class="form-control" required>
                                            @if (!empty($complaint->issue_title))
                                                <option value="{{ $complaint->issue_title }}" selected>
                                                    {{ $complaint->issue_title }}
                                                </option>
                                            @else
                                                <option value="">--चुने--</option>
                                            @endif
                                            <option value="अन्य">अन्य</option>
                                        </select>
                                    </div>
                                </div>

                                {{-- Dates --}}
                                <div class="form-group row date_row">
                                    <div class="col-md-3 d-flex align-items-center">
                                        <label for="from_date" class="me-2 mr-2 mb-0" style="white-space: nowrap;">सूचना
                                            दिनांक</label>
                                        <input type="date" class="form-control" name="from_date"
                                            value="{{ old('from_date', $complaint->news_date) }}">
                                    </div>

                                    <div class="col-md-3 d-flex align-items-center">
                                        <label for="program_date" class="me-2 mr-2 mb-0"
                                            style="white-space: nowrap;">कार्यक्रम दिनांक</label>
                                        <input type="date" class="form-control" name="program_date"
                                            value="{{ old('program_date', $complaint->program_date) }}">
                                    </div>

                                    <div class="col-md-3 d-flex align-items-center">
                                        <label for="to_date" class="me-2 mr-2 mb-0"
                                            style="white-space: nowrap;">कार्यक्रम समय</label>
                                        <input type="time" class="form-control" name="to_date"
                                            value="{{ old('to_date', $complaint->news_time) }}">
                                    </div>
                                </div>

                                {{-- Subject, Description, File --}}
                                <div class="form-group row">
                                    {{-- <div class="col-md-12 mb-3">
                                        <label>विषय</label>
                                        <input type="text" class="form-control" name="CharCounter"
                                            value="{{ old('CharCounter', $complaint->issue_title) }}"
                                            placeholder="हिंदी में टाइप करने के लिए कृपया हिंदी कीबोर्ड चालू करें"
                                            maxlength="100" required>
                                    </div> --}}

                                    <div class="col-md-12 mb-3">
                                        <label>विवरण</label>
                                        <textarea class="form-control" name="NameText" rows="5"
                                            placeholder="हिंदी में टाइप करने के लिए कृपया हिंदी कीबोर्ड चालू करें" maxlength="2000" required>{{ old('NameText', $complaint->issue_description) }}</textarea>
                                    </div>

                                    {{-- <div class="col-md-6 mb-3">
                                        <label>फाइल संलग्न करें</label><br>
                                        @if ($complaint->issue_attachment)
                                            <a href="{{ asset('assets/upload/complaints/' . $complaint->issue_attachment) }}"
                                                class="btn btn-sm btn-info mb-2" target="_blank">View Current</a>
                                        @endif
                                        <input type="file" class="form-control" name="file_attach">
                                    </div> --}}
                                </div>
                            </fieldset>
                        </fieldset>

                        <input class="btn btn-primary" type="submit" value="अपडेट करें"
                            style="background-color:blue; color:#fff; width:100%; height:50px;">
                    </div>
                </form>
            </div>
        </div>

        {{-- Reply History --}}
        {{-- <div class="card container" style="color: #000;">
            <h5 class="my-3">Reply History for {{ $complaint->complaint_number }}</h5>

            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead class="thead-dark">
                        <tr>
                            <th>समस्या/समाधान</th>
                            <th>दिनांक</th>
                            <th>प्रतिक्रिया देने वाला</th>
                            <th>निर्धारित उत्तर</th>
                            <th>पूर्व स्थिति की तस्वीर</th>
                            <th>बाद की तस्वीर</th>
                            <th>यूट्यूब लिंक</th>
                        </tr>
                    </thead>
                    <tbody style="color: #000;">
                        @forelse ($complaint->replies as $reply)
                            @php
                                $replyFromName =
                                    $reply->reply_from == 1 ? $reply->complaint->user->name ?? 'User' : 'BJS Team';
                            @endphp
                            <tr>
                                <td>{{ $reply->complaint_reply }}</td>
                                <td>{{ $reply->reply_date ? \Carbon\Carbon::parse($reply->reply_date)->format('d-m-Y h:i A') : 'N/A' }}
                                </td>
                                <td>{{ $replyFromName }}</td>
                                <td>{{ $reply->predefinedReply->reply ?? '-' }}</td>
                                <td>
                                    @if (!empty($reply->cb_photo))
                                        <a href="{{ asset($reply->cb_photo) }}" class="btn btn-sm btn-primary"
                                            target="_blank">खोलें</a>
                                    @else
                                        —
                                    @endif
                                </td>
                                <td>
                                    @if (!empty($reply->ca_photo))
                                        <a href="{{ asset($reply->ca_photo) }}" class="btn btn-sm btn-primary"
                                            target="_blank">खोलें</a>
                                    @else
                                        —
                                    @endif
                                </td>
                                <td>
                                    @if (!empty($reply->c_video))
                                        <a href="{{ $reply->c_video }}" class="btn btn-sm btn-primary"
                                            target="_blank">लिंक</a>
                                    @else
                                        —
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center">कोई जवाब उपलब्ध नहीं है।</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div> --}}


        <div class="card container" style="color: #000;">
            <h5 class="my-3">Reply History for {{ $complaint->complaint_number }}</h5>

            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead class="thead-dark">
                        <tr>
                            <th>निर्धारित उत्तर</th>
                            <th>स्थिति</th>
                            <th>दिनांक</th>
                            <th>भेजा गया</th>
                            <th>विवरण देखें</th>
                        </tr>
                    </thead>
                    <tbody style="color: #000;">
                        @forelse ($complaint->replies as $reply)
                            <tr>
                                <td>{{ $reply->predefinedReply->reply ?? '-' }}</td>
                                <td>{!! $complaint->statusTextPlain() !!}</td>
                                <td>{{ $reply->reply_date ? \Carbon\Carbon::parse($reply->reply_date)->format('d-m-Y h:i A') : 'N/A' }}
                                </td>
                                <td> {{ $reply->forwardedToManager?->admin_name ?? '' }}</td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-info view-details-btn"
                                        data-id="{{ $reply->complaint_id }}" data-toggle="modal"
                                        data-target="#detailsModal">
                                        देखें
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center">कोई जवाब उपलब्ध नहीं है।</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="modal fade" id="detailsModal" tabindex="-1" role="dialog" aria-labelledby="detailsModalLabel"
            aria-hidden="true">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">शिकायत विवरण</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body" id="modal-body-content">
                        <div class="container-fluid">

                            <div class="border p-2 rounded mb-4 bg-light">
                                <h5 class="fs-5 mb-3">समस्या / समाधान</h5>
                                <p class="mb-0">{{ $reply->complaint_reply }}</p>
                            </div>

                            <div class="border p-2 rounded mb-4">
                                <h5 class="fs-5 mb-3">उत्तर विवरण</h5>
                                <div class="table-responsive">
                                    <table style="color: black" class="table table-bordered text-center align-middle">
                                        <thead class="table-light">
                                            <tr>
                                                <th>पूर्वनिर्धारित उत्तर</th>
                                                <th>स्थिति</th>
                                                <th>उत्तर की तिथि</th>
                                                <th>जिसे भेजा गया</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td>{{ $reply->predefinedReply->reply ?? '—' }}</td>
                                                <td>{!! $complaint->statusTextPlain() !!}</td>
                                                <td>{{ $reply->reply_date ? \Carbon\Carbon::parse($reply->reply_date)->format('d-m-Y h:i A') : '—' }}
                                                </td>
                                                <td>{{ $reply->forwardedToManager?->admin_name ?? '—' }}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <div class="border p-3 rounded mb-3 bg-light">
                                <h5 class="fs-5 mb-4 text-center fw-bold">अटैचमेंट्स</h5>

                                <div class="d-flex flex-wrap justify-content-center">
                                    <div class="card p-3 mr-2 border-0 shadow rounded text-center"
                                        style="width: 200px; background-color: #ffffff;">
                                        <div class="fw-semibold mb-2">पूर्व स्थिति की तस्वीर</div>
                                        @if (!empty($reply->cb_photo))
                                            <a href="{{ asset($reply->cb_photo) }}" target="_blank"
                                                class="btn btn-sm btn-outline-primary">खोलें</a>
                                        @else
                                            <button class="btn btn-sm btn-secondary" disabled>अटैचमेंट नहीं है</button>
                                        @endif
                                    </div>

                                    <div class="card mr-2 p-3 border-0 shadow rounded text-center"
                                        style="width: 200px; background-color: #ffffff;">
                                        <div class="fw-semibold mb-2">बाद की तस्वीर</div>
                                        @if (!empty($reply->ca_photo))
                                            <a href="{{ asset($reply->ca_photo) }}" target="_blank"
                                                class="btn btn-sm btn-outline-primary">खोलें</a>
                                        @else
                                            <button class="btn btn-sm btn-secondary" disabled>अटैचमेंट नहीं है</button>
                                        @endif
                                    </div>

                                    <div class="card mr-2 p-3 border-0 shadow rounded text-center"
                                        style="width: 200px; background-color: #ffffff;">
                                        <div class="fw-semibold mb-2">यूट्यूब लिंक</div>
                                        @if (!empty($reply->c_video))
                                            <a href="{{ $reply->c_video }}" target="_blank"
                                                class="btn btn-sm btn-outline-primary">लिंक</a>
                                        @else
                                            <button class="btn btn-sm btn-secondary" disabled>अटैचमेंट नहीं है</button>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Reply Form --}}
        <div class="card">
            <div class="card-header" style="color: #000">Reply to {{ $complaint->complaint_number }}</div>
            <div class="card-body">
                <form id="replyForm" method="POST"
                    action="{{ route('complaint_reply.reply', $complaint->complaint_id) }}"
                    enctype="multipart/form-data">
                    @csrf

                    <div class="row mb-3">
                        <div class="col-md-3">
                            <label class="form-label ">शिकायत की स्थिति: <span class="tx-danger"
                                    style="color: red;">*</span></label>
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

                        <div class="col-md-3">
                            <label class="form-label">पूर्व निर्धारित उत्तर चुनें:</label>
                            <select name="selected_reply" id="selected_reply" class="form-control">
                                <option value="">--चयन करें--</option>
                                @foreach ($replyOptions as $option)
                                    <option value="{{ $option->reply_id }}">{{ $option->reply }}</option>
                                @endforeach
                                <option value="अन्य">अन्य</option>
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">अधिकारी चुनें (आगे भेजे)</label>
                            <select name="forwarded_to" id="managers" class="form-control">
                                <option value="">--चयन करें--</option>
                                @foreach ($managers as $manager)
                                    <option value="{{ $manager->admin_id }}">{{ $manager->admin_name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-12">
                            <label class="form-label">समस्या/समाधान में प्रगति</label>
                            <textarea name="cmp_reply" placeholder="हिंदी में टाइप करने के लिए कृपया हिंदी कीबोर्ड चालू करें"
                                class="form-control" rows="6" required></textarea>
                        </div>
                    </div>

                    <div class="row mt-3">
                        <div class="col-md-3">
                            <label class="form-label">पूर्व स्थिति की तस्वीर</label>
                            <input type="file" name="cb_photo[]" class="form-control" multiple accept="image/*">
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">बाद की तस्वीर</label>
                            <input type="file" name="ca_photo[]" class="form-control" multiple accept="image/*">
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">यूट्यूब लिंक</label>
                            <input type="url" name="c_video" class="form-control">
                        </div>
                    </div>


                    {{-- <div class="col-md-2">
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
                    </div> --}}

                    <div class="col-12 mt-3">
                        <button type="submit" class="btn btn-primary">शिकायत दर्ज करें</button>
                    </div>

                </form>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            $(document).ready(function() {
                $('#updateComplaintForm').on('submit', function(e) {
                    e.preventDefault();

                    $("#loader-wrapper").show();
                    var form = $(this)[0];
                    var formData = new FormData(form);

                    $.ajax({
                        url: $(form).attr('action'),
                        method: 'POST',
                        data: formData,
                        processData: false,
                        contentType: false,
                        success: function(response) {
                            $("#loader-wrapper").hide();
                            $('#success-message').text(response.message);

                            $('#success-alert').removeClass('d-none');

                            window.scrollTo({
                                top: 0,
                                behavior: 'smooth'
                            });


                            setTimeout(function() {
                                $('#success-alert').addClass('d-none');
                            }, 5000);
                        },
                        error: function(xhr) {
                            $("#loader-wrapper").hide();
                            alert('अपडेट करते समय एक त्रुटि हुई।');
                        }
                    });
                });


                $('#replyForm').on('submit', function(e) {
                    e.preventDefault();

                    $("#loader-wrapper").show();
                    var form = $(this)[0];
                    var formData = new FormData(form);

                    $.ajax({
                        url: $(form).attr('action'),
                        method: 'POST',
                        data: formData,
                        processData: false,
                        contentType: false,
                        success: function(response) {
                            $("#loader-wrapper").hide();
                            $('#success-message').text(response.message);

                            $('#success-alert').removeClass('d-none');

                            window.scrollTo({
                                top: 0,
                                behavior: 'smooth'
                            });

                            $('#replyForm')[0].reset();

                            setTimeout(function() {
                                $('#success-alert').addClass('d-none');
                            }, 5000);
                        },
                        error: function(xhr) {
                            $("#loader-wrapper").hide();
                            alert('अपडेट करते समय एक त्रुटि हुई।');
                        }
                    });
                });



                $(".check").change(function() {

                    $("#form_container").slideDown();

                    var text = $(this).data('text');
                    $(".data-text").html(text);

                    var heading = $(this).data('heading');
                    $(".data-heading").html(heading);

                    var legend = $(this).data('legend');
                    $(".data-legend").html(legend);
                    $(".dynamic-legend").html("विवरण - " +
                        legend);


                    var type = $(this).val();

                    if (type === "समस्या") {
                        $(".mobile-label").html("शिकायतकर्ता का मोबाइल");
                    } else if (type === "विकास") {
                        $(".mobile-label").html("मांगकर्ता का मोबाइल");
                    } else {
                        $(".mobile-label").html("सूचनाकर्ता का मोबाइल");
                    }

                    // Toggle department/date rows
                    if (type === "शुभ सुचना" || type === "अशुभ सुचना") {
                        $(".department_row").hide();
                        $("select[name='department']").removeAttr("required");
                        $(".date_row").show();
                    } else {
                        $(".department_row").show();
                        $("select[name='department']").attr("required", "required");
                        $(".date_row").hide();
                    }
                });

                if ($(".check:checked").length > 0) {
                    $(".check:checked").trigger("change");
                }

                $('#department-select').on('change', function() {
                    const departmentName = $(this).val();
                    const $postSelect = $('#post-select');

                    $postSelect.html('<option value="">लोड हो रहा है...</option>');

                    if (departmentName) {
                        $.ajax({
                            url: '/get-designations/' + encodeURIComponent(departmentName),
                            method: 'GET',
                            success: function(data) {
                                let options = '<option value="">--चुने--</option>';
                                data.forEach(function(designation) {
                                    options +=
                                        `<option value="${designation.designation_name}">${designation.designation_name}</option>`;
                                });
                                $postSelect.html(options);
                            },
                            error: function() {
                                $postSelect.html('<option value="">लोड करने में त्रुटि</option>');
                            }
                        });
                    } else {
                        $postSelect.html('<option value="">--चुने--</option>');
                    }
                });


                $('#department-select').on('change', function() {
                    const departmentName = $(this).val();
                    const $subjectSelect = $('#subject-select');
                    $subjectSelect.html('<option value="">लोड हो रहा है...</option>');

                    if (departmentName) {
                        $.ajax({
                            url: '/manager/get-subjects-department/' + encodeURIComponent(
                                departmentName),
                            method: 'GET',
                            success: function(data) {
                                let options = '<option value="">--चुने--</option>';
                                data.forEach(function(subject) {
                                    options +=
                                        `<option value="${subject.subject}">${subject.subject}</option>`;
                                });
                                $subjectSelect.html(options);
                            },
                            error: function() {
                                $subjectSelect.html(
                                    '<option value="">लोड करने में त्रुटि</option>');
                            }
                        });
                    } else {
                        $subjectSelect.html('<option value="">--चुने--</option>');
                    }
                });

                $('#txtgram').change(function() {
                    const nagarId = $(this).val();
                    if (!nagarId) return;

                    $('#txtpolling').html('<option value="">लोड हो रहा है...</option>');

                    $.get('/manager/get-pollings-gram/' + nagarId, function(data) {
                        let options = '<option value="">--चुने--</option>';
                        data.forEach(function(item) {
                            options += `<option value="${item.id}">${item.label}</option>`;
                        });
                        $('#txtpolling').html(options);
                    });
                });



                const complaint = {
                    division: "{{ $complaint->division_id ?? '' }}",
                    district: "{{ $complaint->district_id ?? '' }}",
                    vidhansabha: "{{ $complaint->vidhansabha_id ?? '' }}",
                    mandal: "{{ $complaint->mandal_id ?? '' }}",
                    nagar: "{{ $complaint->gram_id ?? '' }}",
                    polling: "{{ $complaint->polling_id ?? '' }}",
                    area: "{{ $complaint->area_id ?? '' }}"
                };

                function prefillFields() {
                    $('#division_id').val(complaint.division).trigger('change');

                    $.get(`/manager/get-districts/${complaint.division}`, function(data) {
                        $('#district_name').html(data).val(complaint.district).trigger('change');

                        $.get(`/manager/get-vidhansabha/${complaint.district}`, function(data) {
                            $('#txtvidhansabha').html(data).val(complaint.vidhansabha).trigger(
                                'change');

                            $.get(`/manager/get-mandal/${complaint.vidhansabha}`, function(data) {
                                $('#txtmandal').html(data).val(complaint.mandal).trigger(
                                    'change');

                                $.get(`/manager/get-nagar/${complaint.mandal}`, function(data) {
                                    $('#txtgram').append(data).val(complaint.nagar);

                                    $.get(`/manager/get-polling/${complaint.mandal}`,
                                        function(
                                            data) {
                                            $('#txtpolling').html(data).val(
                                                complaint.polling);

                                            $.get(`/manager/get-area/${complaint.polling}`,
                                                function(data) {
                                                    $('#txtarea').html(data)
                                                        .val(complaint
                                                            .area);
                                                });
                                        });
                                });
                            });
                        });
                    });
                }
            });
        </script>
    @endpush


@endsection
