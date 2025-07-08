@php
$pageTitle = 'सदस्यता फाॅर्म';
$breadcrumbs = [
'एडमिन' => '#',
'सदस्यता फाॅर्म' => '#'
];
@endphp

@extends('layouts.app')
@section('title', 'Registered details')

@section('content')
<div class="container">
    <fieldset>
        <div class="step-header bg-dark text-white p-3 rounded d-flex justify-content-between align-items-center mb-3">
            <h5 class="mb-0 text-white">1. व्यक्तिगत जानकारी</h5>
            <span class="step-number badge bg-light text-dark fs-6">Step 1 / 4</span>
        </div>

        <div class="row mb-5">
            <div class="col-md-6">
                <label>आपको बी.जे.एस संगठन से जोड़ने वाले साथी का मोबाइल नंबर</label>
                <input type="text" disabled class="form-control"
                    value="{{ optional($registration->reference)->mobile1 }}">
            </div>
            <div class="col-md-6">
                <label>साथी का नाम</label>
                <input type="text" disabled class="form-control"
                    value="{{ optional($registration->reference)->name }}">
            </div>
        </div>
        <hr />

        <div class="row">
            <div class="col-md-3">
                <label>सदस्यता आईडी</label>
                <input type="text" disabled class="form-control" value="{{ $registration->member_id }}">
            </div>
            <div class="col-md-3">
                <label>सदस्यता स्तर</label>
                <select disabled class="form-control">
                    <option>--Select--</option>
                    <option {{ $registration->membership == 'समर्पित कार्यकर्ता' ? 'selected' : '' }}>समर्पित कार्यकर्ता</option>
                    <option {{ $registration->membership == 'सक्रिय कार्यकर्ता' ? 'selected' : '' }}>सक्रिय कार्यकर्ता</option>
                    <option {{ $registration->membership == 'साधारण कार्यकर्ता' ? 'selected' : '' }}>साधारण कार्यकर्ता</option>
                </select>
            </div>

            <div class="col-md-3">
                <label>आपका नाम</label>
                <input type="text" disabled class="form-control" value="{{ $registration->name }}">
            </div>
            <div class="col-md-3">
                <label>लिंग</label>
                <select disabled class="form-control">
                    <option>--Select--</option>
                    <option value="पुरुष" {{ $registration->gender == 'पुरुष' ? 'selected' : '' }}>पुरुष</option>
                    <option value="स्त्री" {{ $registration->gender == 'स्त्री' ? 'selected' : '' }}>स्त्री</option>
                    <option value="अन्य" {{ $registration->gender == 'अन्य' ? 'selected' : '' }}>अन्य</option>
                </select>
            </div>
        </div>

        <div class="row mt-3">

            <div class="col-md-3">
                <label>जन्म दिनांक</label>
                <input type="date" disabled class="form-control" value="{{ $registration->dob }}">
            </div>
            <div class="col-md-3">
                <label>आयु</label>
                <input type="text" disabled class="form-control" value="{{ $registration->age }}">
            </div>

            <div class="col-md-3">
                <label>धर्म</label>
                <select disabled class="form-control">
                    <option value="">Select</option>
                    @foreach(['ईसाई','मुसलमान','हिंदू','सिख','जैन','बौद्ध','यहूदी'] as $religion)
                    <option value="{{ $religion }}" {{ $registration->religion == $religion ? 'selected' : '' }}>{{ $religion }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label>श्रेणी</label>
                <select disabled class="form-control">
                    @foreach(['सामान्य','पिछड़ा वर्ग','अनुसूचित जाति','आदिवासी जाति','अन्य'] as $caste)
                    <option value="{{ $caste }}" {{ $registration->caste == $caste ? 'selected' : '' }}>{{ $caste }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="row mt-3">

            <div class="col-md-3">
                <label>जाति</label>
                <input type="text" disabled class="form-control" value="{{ $registration->jati }}">
            </div>
            <div class="col-md-3">
                <label>मोबाइल 1</label>
                <input type="text" disabled class="form-control" value="{{ $registration->mobile1 }}">
            </div>

            <div class="col-md-3">
                <label>WhatsApp (मोबाइल 1)</label><br>
                <input type="checkbox" disabled {{ $registration->mobile1_whatsapp == 1 ? 'checked' : '' }}>
            </div>
            <div class="col-md-3">
                <label>मोबाइल 2</label>
                <input type="text" disabled class="form-control" value="{{ $registration->mobile2 }}">
            </div>
        </div>

        <div class="row mt-3">

            <div class="col-md-3">
                <label>WhatsApp (मोबाइल 2)</label><br>
                <input type="checkbox" disabled {{ $registration->mobile1_whatsapp == 2 ? 'checked' : '' }}>
            </div>
            <div class="col-md-3">
                <label>शैक्षणिक योग्यता</label>
                <select disabled class="form-control">
                    @php
                    $qualifications = ['औपचारिक शिक्षा नहीं','प्राथमिक शिक्षा','माध्यमिक शिक्षा','10th','12th','स्नातक','स्नातकोत्तर','डॉक्टरेट या उच्चतर','डिप्लोमा','अन्य'];
                    @endphp
                    @foreach($qualifications as $q)
                    <option value="{{ $q }}" {{ $registration->education == $q ? 'selected' : '' }}>{{ $q }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-3">
                <label>व्यवसाय</label>
                <input type="text" disabled class="form-control" value="{{ $registration->business }}">
            </div>
            <div class="col-md-3">
                <label>व्यवसायिक पद</label>
                <input type="text" disabled class="form-control" value="{{ $registration->position }}">
            </div>
        </div>

        <div class="row mt-3">

            <div class="col-md-3">
                <label>पिता का नाम</label>
                <input type="text" disabled class="form-control" value="{{ $registration->father_name }}">
            </div>
            <div class="col-md-3">
                <label>ईमेल</label>
                <input type="email" disabled class="form-control" value="{{ $registration->email }}">
            </div>


            <div class="row mt-3">
                <div class="col-md-6">
                    <label>फोटो</label><br>
                    @if($registration->photo)
                    <img src="{{ asset('assets/upload/' . $registration->photo) }}" width="100">
                    @else
                    <span>No photo uploaded</span>
                    @endif

                    <form method="POST" action="{{ route('registration.uploadPhoto') }}" enctype="multipart/form-data" class="mt-3">
                        @csrf
                        <input type="hidden" name="id" value="{{ $registration->registration_id }}">

                        <div class="mb-3">
                            <label for="photo" class="form-label">फोटो बदले</label>
                            <input type="file" name="photo" id="photo" class="form-control w-100">
                        </div>

                        <button type="submit" class="btn btn-primary btn-lg w-100">Upload</button>
                    </form>
                </div>
            </div>
        </div>
    </fieldset>


    <br />
    <fieldset class="mt-5">
        <div class="step-header bg-dark text-white p-3 rounded d-flex justify-content-between align-items-center mb-3">
            <h5 class="mb-0 step-heading text-white">2. मतदान जानकारी</h5>
            <span class="step-number badge bg-light text-dark fs-6">Step 2 / 4</span>
        </div>
        <hr />

        <div class="row  mt-3">
            <div class="col-lg-3 col-md-6 col-12">
                <label for="division_name" class="form-label required">संभाग का नाम</label>
                <select name="division_name" id="division_name" class="form-control">
                    <option value="">--Select Division--</option>
                    @foreach($divisions as $row)
                    <option value="{{ $row->division_id }}" {{ old('division_id', $step2->division_id ?? '') == $row->division_id ? 'selected' : '' }}>
                        {{ $row->division_name }}
                    </option>
                    @endforeach
                </select>
            </div>

            <div class="col-lg-3 col-md-6 col-12">
                <label for="district" class="form-label required">जिले का नाम</label>
                <select disabled name="district" id="district" class="form-control">
                    <option value="">--Select--</option>
                    @foreach($districts as $row)
                    <option value="{{ $row->district_id }}" {{ (string) $step2->district === (string) $row->district_id ? 'selected' : '' }}>
                        {{ $row->district_name }}
                    </option>
                    @endforeach
                </select>
            </div>

            <div class="col-lg-3 col-md-6 col-12">
                <label for="vidhansabha" class="form-label">विधानसभा का नाम/क्रमांक</label>
                <input disabled type="text" class="form-control" name="vidhansabha" id="vidhansabha" value="{{ $vidhansabha }}">
            </div>

            <div class="col-lg-3 col-md-6 col-12">
                <label for="loksabha" class="form-label">लोकसभा</label>
                <input disabled type="text" class="form-control" name="loksabha" id="loksabha" value="{{ $step2['loksabha'] }}">
            </div>
        </div>

        <div class="row  mt-3">
            <div class="col-lg-3 col-md-6 col-12">
                <label for="mandal" class="form-label">मंडल का नाम</label>
                <input disabled type="text" class="form-control" name="mandal" id="mandal" value="{{ $mandal }}">
            </div>

            <div class="col-lg-3 col-md-6 col-12">
                <label for="nagar" class="form-label">नगर केंद्र/ग्राम केंद्र का नाम</label>
                <input disabled type="text" class="form-control" name="nagar" id="nagar" value="{{ $nagar }}">
            </div>

            <div class="col-lg-3 col-md-6 col-12">
                <label for="matdan_kendra_name" class="form-label">मतदान केंद्र का नाम</label>
                <input disabled type="text" class="form-control" name="matdan_kendra_name" id="matdan_kendra_name" value="{{ $polling }}">
            </div>

            <div class="col-lg-3 col-md-6 col-12">
                <label for="polling_area" class="form-label">मतदान क्षेत्र</label>
                <input disabled type="text" class="form-control" name="polling_area" id="polling_area" value="{{ $area }}">
            </div>
        </div>

        <div class="row  mt-3">
            <div class="col-lg-3 col-md-6 col-12">
                <label for="voter_number" class="form-label">वोटर आई.डी. नंबर</label>
                <input type="text" class="form-control" name="voter_number" id="voter_number" value="{{ $step2['voter_number'] }}">
            </div>

            <div class="col-lg-3 col-md-6 col-12">
                <label class="form-label d-block">वोटर आई.डी. आगे का फोटो</label>
                <img src="{{ asset('assets/upload/step2/' .$step2['voter_front']) }}" width="200" alt="Voter ID Front" class="img-thumbnail">
            </div>

            <div class="col-lg-3 col-md-6 col-12">
                <label class="form-label d-block">वोटर आई.डी. पीछे का फोटो</label>
                <img src="{{ asset('assets/upload/step2/' .$step2['voter_back']) }}" width="200" alt="Voter ID Back" class="img-thumbnail">
            </div>
        </div>
    </fieldset>
    <br />

    <fieldset class="mt-5">

        <div class="step-header bg-dark text-white p-3 rounded d-flex justify-content-between align-items-center mb-3">
            <h5 class="mb-0 step-heading text-white">3. परिवार</h5>
            <span class="step-number badge bg-light text-dark fs-6">Step 3 / 4</span>
        </div>

        <hr />

        <div class="row g-3">
            <div class="col-lg-4 col-md-6 col-12">
                <label class="form-label">परिवार में कुल सदस्य</label>
                <input disabled type="text" class="form-control" value="5">
            </div>
            <div class="col-lg-4 col-md-6 col-12">
                <label class="form-label">परिवार में कुल मतदाता</label>
                <input disabled type="text" class="form-control" value="3">
            </div>
            <div class="col-lg-4 col-md-12 col-12">
                <label class="form-label">शासकीय / अशासकीय सेवा में संलग्न सदस्य</label>
                <input disabled type="text" class="form-control" value="1">
            </div>
        </div>

        <h6 class="mt-4 border-bottom pb-1">परिवार के सदस्य</h6>
        <div class="row g-3">
            <div class="col-md-6 col-12">
                <label class="form-label">नाम</label>
                <input disabled type="text" class="form-control" value="राम कुमार">
            </div>
            <div class="col-md-6 col-12">
                <label class="form-label">मोबाइल</label>
                <input disabled type="number" class="form-control" value="9876543210">
            </div>
            <div class="col-md-6 col-12">
                <label class="form-label">नाम</label>
                <input disabled type="text" class="form-control" value="सीता देवी">
            </div>
            <div class="col-md-6 col-12">
                <label class="form-label">मोबाइल</label>
                <input disabled type="number" class="form-control" value="9876543211">
            </div>
        </div>

        <h6 class="mt-4 border-bottom pb-1">मित्र / पड़ोसी</h6>
        <div class="row g-3">
            <div class="col-md-6 col-12">
                <label class="form-label">नाम</label>
                <input disabled type="text" class="form-control" value="शिवराम">
            </div>
            <div class="col-md-6 col-12">
                <label class="form-label">मोबाइल</label>
                <input disabled type="number" class="form-control" value="9876543212">
            </div>
            <div class="col-md-6 col-12">
                <label class="form-label">नाम</label>
                <input disabled type="text" class="form-control" value="मीना">
            </div>
            <div class="col-md-6 col-12">
                <label class="form-label">मोबाइल</label>
                <input disabled type="number" class="form-control" value="9876543213">
            </div>
        </div>

        <h6 class="mt-4 border-bottom pb-1">रुचि</h6>
        <div class="row g-3">
            @php
            $interests = [
            'कृषि' => true,
            'समाजसेवा' => false,
            'राजनीति' => true,
            'पर्यावरण' => false,
            'शिक्षा' => true,
            'योग' => false,
            'स्वास्थ्य' => true,
            'स्वच्छता' => false,
            'साधना' => false,
            ];
            @endphp

            @foreach($interests as $label => $checked)
            <div class="col-lg-2 col-md-4 col-6">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" disabled {{ $checked ? 'checked' : '' }}>
                    <label class="form-check-label">{{ $label }}</label>
                </div>
            </div>
            @endforeach
        </div>

        <div class="row g-3 mt-4">
            <div class="col-md-6 col-12">
                <label class="form-label">स्थाई पता</label>
                <textarea disabled rows="5" class="form-control">ग्राम पंचायत, पोस्ट - XYZ, जिला - ABC</textarea>
            </div>
            <div class="col-md-6 col-12">
                <label class="form-label">अस्थाई पता</label>
                <textarea disabled rows="5" class="form-control">123 कॉलोनी रोड, नगर पालिका, ABC</textarea>
            </div>
        </div>

        <h6 class="mt-4 border-bottom pb-1">वाहन</h6>
        <div class="row g-3">
            <div class="col-lg-2 col-md-4 col-6">
                <label class="form-label">कार</label>
                <input disabled type="text" class="form-control" value="1">
            </div>
            <div class="col-lg-2 col-md-4 col-6">
                <label class="form-label">ट्रेक्टर</label>
                <input disabled type="text" class="form-control" value="0">
            </div>
            <div class="col-lg-2 col-md-4 col-6">
                <label class="form-label">मोटरसाइकिल</label>
                <input disabled type="text" class="form-control" value="2">
            </div>
        </div>
    </fieldset>
    <br />

    <fieldset class="mt-5">
        <div class="step-header bg-dark text-white p-3 rounded d-flex justify-content-between align-items-center mb-3">
            <h5 class="mb-0 step-heading text-white">4. राजनीति</h5>
            <span class="step-number badge bg-light text-dark fs-6">Step 4 / 4</span>
        </div>
        <hr />

        <div class="row">
            <div class="col-lg-3 col-md-6 col-12">
                <label for="political_activity" class="form-label d-block">राजनीतिक सक्रियता</label>
                <select id="political_activity" class="form-select w-100" disabled>
                    <option>--Select--</option>
                    <option value="भाजपा" selected>भाजपा</option>
                    <option value="कांग्रेस">कांग्रेस</option>
                    <option value="बीएसपी">बीएसपी</option>
                    <option value="निर्दलीय">निर्दलीय</option>
                </select>
            </div>

            <div class="col-lg-3 col-md-6 col-12">
                <label class="form-label">पद वर्तमान/भूतपूर्व</label>
                <input disabled type="text" class="form-control" value="वार्ड अध्यक्ष">
            </div>

            <div class="col-12 mt-3">
                <label class="form-label fw-bold">सदस्यता का कारण / उद्देश्य: आप बीजेएस के सदस्य क्यों बन रहे हैं</label>
                <input disabled type="text" class="form-control" value="समाज सेवा के उद्देश्य से">
            </div>
        </div>
    </fieldset>

    <br />
    <!-- </div> -->
</div>

@endsection