@php
    $pageTitle = 'मतदाता विवरण';
    $breadcrumbs = [
        'एडमिन' => '#',
        'मतदाता विवरण' => '#',
    ];
@endphp

@extends('layouts.app')
@section('title', 'Voters Details')

@section('content')
    <div class="container">
        <div class="row page-titles mx-0">
            <div class="col-md-12 col-sm-12 col-xs-12">
                <fieldset>
                    <div
                        class="step-header bg-dark text-white p-3 rounded d-flex justify-content-between align-items-center mb-3">
                        <h5 class="mb-0 text-white">1. व्यक्तिगत जानकारी</h5>
                        <span class="step-number badge bg-light text-dark fs-6">Step 1 / 2</span>
                    </div>
                    <hr />

                    <div class="row">
                        <div class="col-md-2">
                            <label>मतदाता नाम</label>
                            <input type="text" disabled class="form-control" value="{{ $registration->name }}">
                        </div>

                        <div class="col-md-2">
                            <label>पिता/पति का नाम</label>
                            <input type="text" disabled class="form-control" value="{{ $registration->father_name }}">
                        </div>

                        <div class="col-md-2">
                            <label>मोबाइल 1</label>
                            <input type="text" disabled class="form-control" value="{{ $registration->mobile1 }}">
                        </div>

                        <div class="col-md-2">
                            <label>लिंग</label>
                            <select disabled class="form-control">
                                <option>--Select--</option>
                                <option value="पुरुष" {{ $registration->gender == 'पुरुष' ? 'selected' : '' }}>पुरुष
                                </option>
                                <option value="स्त्री" {{ $registration->gender == 'स्त्री' ? 'selected' : '' }}>स्त्री
                                </option>
                                <option value="अन्य" {{ $registration->gender == 'अन्य' ? 'selected' : '' }}>अन्य</option>
                            </select>
                        </div>

                        <div class="col-md-2">
                            <label>जन्म दिनांक</label>
                            <input type="date" disabled class="form-control" value="{{ $registration->dob }}">
                        </div>
                        <div class="col-md-2">
                            <label>आयु</label>
                            <input type="text" disabled class="form-control" value="{{ $registration->age }}">
                        </div>
                    </div>

                    <div class="row mt-2">
                        <div class="col-md-2">
                            <label>धर्म</label>
                            <select disabled class="form-control">
                                <option value="">Select</option>
                                @foreach (['ईसाई', 'मुसलमान', 'हिंदू', 'सिख', 'जैन', 'बौद्ध', 'यहूदी'] as $religion)
                                    <option value="{{ $religion }}"
                                        {{ $registration->religion == $religion ? 'selected' : '' }}>{{ $religion }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label>श्रेणी</label>
                            <select disabled class="form-control">
                                @foreach (['सामान्य', 'पिछड़ा वर्ग', 'अनुसूचित जाति', 'आदिवासी जाति', 'अन्य'] as $caste)
                                    <option value="{{ $caste }}"
                                        {{ $registration->caste == $caste ? 'selected' : '' }}>
                                        {{ $caste }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-2">
                            <label>जाति</label>
                            <input type="text" disabled class="form-control" value="{{ $registration->jati }}">
                        </div>

                        <div class="col-md-2">
                            <label>मोबाइल 2</label>
                            <input type="text" disabled class="form-control" value="{{ $registration->mobile2 }}">
                        </div>

                        <div class="col-md-2">
                            <label>शैक्षणिक योग्यता</label>
                            <select disabled class="form-control">
                                @php
                                    $qualifications = [
                                        'औपचारिक शिक्षा नहीं',
                                        'प्राथमिक शिक्षा',
                                        'माध्यमिक शिक्षा',
                                        '10th',
                                        '12th',
                                        'स्नातक',
                                        'स्नातकोत्तर',
                                        'डॉक्टरेट या उच्चतर',
                                        'डिप्लोमा',
                                        'अन्य',
                                    ];
                                @endphp
                                @foreach ($qualifications as $q)
                                    <option value="{{ $q }}"
                                        {{ $registration->education == $q ? 'selected' : '' }}>
                                        {{ $q }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-2">
                            <label>व्यवसाय</label>
                            <input type="text" disabled class="form-control" value="{{ $registration->business }}">
                        </div>
                    </div>

                    <div class="row mt-2">
                        <div class="col-md-2">
                            <label>फोटो</label><br>
                            @if ($registration->photo)
                                <img src="{{ asset('assets/upload/' . $registration->photo) }}" alt="Voter Photo"
                                    width="100">
                            @else
                                <span>कोई फ़ोटो अपलोड नहीं की गई</span>
                            @endif
                        </div>

                        <div class="col-md-2">
                            <div>
                                <label>मतदाता स्वभाव</label><br>
                                <span
                                    style="font-size: 1.25rem; font-weight: bold; background-color: #f0f00a; border: 1px solid #999; padding: 12px; border-radius: 5px;">
                                    {{ $registration->voter_nature }}
                                </span>
                            </div>
                        </div>
                    </div>
                </fieldset>

                <fieldset class="mt-3">
                    <div
                        class="step-header bg-dark text-white p-3 rounded d-flex justify-content-between align-items-center mb-3">
                        <h5 class="mb-0 step-heading text-white">2. मतदान जानकारी</h5>
                        <span class="step-number badge bg-light text-dark fs-6">Step 2 / 2</span>
                    </div>
                    <hr />

                    <div class="row mt-2">
                        <div class="col-lg-2 col-md-4 col-12">
                            <label for="division_name" class="form-label required">संभाग का नाम</label>
                            <select name="division_name" id="division_name" class="form-control">
                                <option value="">--Select Division--</option>
                                @foreach ($divisions as $row)
                                    <option value="{{ $row->division_id }}"
                                        {{ old('division_id', $step2->division_id ?? '') == $row->division_id ? 'selected' : '' }}>
                                        {{ $row->division_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-lg-2 col-md-4 col-12">
                            <label for="district" class="form-label required">जिले का नाम</label>
                            <select disabled name="district" id="district" class="form-control">
                                <option value="">--Select--</option>
                                @foreach ($districts as $row)
                                    <option value="{{ $row->district_id }}"
                                        {{ (string) $step2->district === (string) $row->district_id ? 'selected' : '' }}>
                                        {{ $row->district_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-lg-2 col-md-4 col-12">
                            <label for="vidhansabha" class="form-label">विधानसभा का नाम/क्रमांक</label>
                            <input disabled type="text" class="form-control" name="vidhansabha" id="vidhansabha"
                                value="{{ $vidhansabha }}">
                        </div>

                        <div class="col-lg-2 col-md-4 col-12">
                            <label for="loksabha" class="form-label">लोकसभा</label>
                            <input disabled type="text" class="form-control" name="loksabha" id="loksabha"
                                value="{{ $step2['loksabha'] }}">
                        </div>

                        <div class="col-lg-2 col-md-4 col-12">
                            <label for="mandal" class="form-label">मंडल का नाम</label>
                            <input disabled type="text" class="form-control" name="mandal" id="mandal"
                                value="{{ $mandal }}">
                        </div>

                        <div class="col-lg-2 col-md-4 col-12">
                            <label for="nagar" class="form-label">नगर/ग्राम केंद्र का नाम</label>
                            <input disabled type="text" class="form-control" name="nagar" id="nagar"
                                value="{{ $nagar }}">
                        </div>
                    </div>

                    <div class="row  mt-3">
                        <div class="col-lg-2 col-md-4 col-12">
                            <label for="matdan_kendra_name" class="form-label">मतदान केंद्र का नाम</label>
                            <input disabled type="text" class="form-control" name="matdan_kendra_name"
                                id="matdan_kendra_name" value="{{ $polling }}">
                        </div>

                        <div class="col-lg-2 col-md-4 col-12">
                            <label for="polling_area" class="form-label">मतदान क्षेत्र</label>
                            <input disabled type="text" class="form-control" name="polling_area" id="polling_area"
                                value="{{ $area }}">
                        </div>

                        <div class="col-lg-2 col-md-4 col-12">
                            <label for="voter_number" class="form-label">वोटर आई.डी. नंबर</label>
                            <input type="text" class="form-control" name="voter_number" id="voter_number"
                                value="{{ $step2['voter_number'] }}">
                        </div>

                        <div class="col-lg-2">
                            <label class="form-label">परिवार में कुल सदस्य</label>
                            <input disabled type="text" class="form-control" value="{{ $step2['total_member'] }}">
                        </div>
                        <div class="col-lg-2">
                            <label class="form-label">परिवार में कुल मतदाता</label>
                            <input disabled type="text" class="form-control" value="{{ $step2['total_voter'] }}">
                        </div>
                    </div>
                </fieldset>

                {{-- <fieldset class="mt-3">
                    <div
                        class="step-header bg-dark text-white p-3 rounded d-flex justify-content-between align-items-center mb-3">
                        <h5 class="mb-0 step-heading text-white">3. शिकायत का विवरण</h5>
                        <span class="step-number badge bg-light text-dark fs-6">Step 3 / 3</span>
                    </div>
                    <hr />
                    <div class="row mt-2">
                        <div class="col-md-4">
                            <h5 class="mb-3 mt-4">समस्या / विकास</h5>
                            <table class="table table-bordered">
                                <thead style="background-color: #ee807c; color: black">
                                    <tr>
                                        <th>आवेदक</th>
                                        <th>विषय</th>
                                        <th>तारीख</th>
                                        <th>स्थिति</th>
                                    </tr>
                                </thead>
                                <tbody style="color: black">
                                    @forelse ($samasyavikashComplaints as $complaint)
                                        <tr>
                                            <td>{{ $complaint->complaint_created_by }}</td>
                                            <td>{{ $complaint->issue_title }}</td>
                                            <td>{{ \Carbon\Carbon::parse($complaint->posted_date)->format('d-m-Y h:i A') }}
                                            </td>
                                            <td>{{ $complaint->complaint_status }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="text-center">कोई शिकायत नहीं मिली</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>

                        </div>


                        <div class="col-md-4">
                            <h5 class="mb-3 mt-4">उत्तर विवरण</h5>
                            <table class="table table-bordered ">
                                <thead style="background-color: #88fa7e; color: black">
                                    <tr>
                                        <th>भेजी गई</th>
                                        <th>उत्तर</th>
                                        <th>स्थिति</th>
                                        <th>तारीख</th>

                                    </tr>
                                </thead>
                                <tbody style="color: black">
                                    @forelse ($complaintReplies as $complaintId => $replies)
                                        @foreach ($replies as $reply)
                                            <tr>
                                                <td>{{ $reply->forwardedToManager->admin_name ?? '-' }}</td>
                                                <td>{{ $reply->selected_reply }}</td>
                                                <td>{{ $reply->complaint_status ?? '-' }}</td>
                                                <td>{{ \Carbon\Carbon::parse($reply->reply_date)->format('d-m-Y h:i A') }}
                                                </td>
                                            </tr>
                                        @endforeach
                                    @empty
                                        <tr>
                                            <td colspan="4" class="text-center">कोई उत्तर नहीं मिला</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>

                        </div>

                        <div class="col-md-4">
                            <h5 class="mb-3 mt-4">शुभ / अशुभ सूचना</h5>
                            <table class="table table-bordered">
                                <thead style="background-color: #89b0eb; color: black">
                                    <tr>
                                        <th>आवेदक</th>
                                        <th>विषय</th>
                                        <th>तारीख</th>
                                        <th>स्थिति</th>
                                    </tr>
                                </thead>
                                <tbody style="color: black">
                                    @forelse ($shubhAshubhComplaints as $entry)
                                        <tr>
                                            <td>{{ $entry->complaint_created_by }}</td>
                                            <td>{{ $entry->issue_title }}</td>
                                            <td>{{ \Carbon\Carbon::parse($entry->posted_date)->format('d-m-Y h:i A') }}
                                            </td>
                                            <td>{{ $entry->complaint_status }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="text-center">कोई सूचना उपलब्ध नहीं</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>

                        </div>
                    </div>
                </fieldset> --}}
            </div>
        </div>
    </div>

@endsection
