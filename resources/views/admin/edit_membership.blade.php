@php
    $pageTitle = 'अपडेट सदस्यता फाॅर्म';
    $breadcrumbs = [
        'एडमिन' => '#',
        'अपडेट सदस्यता फाॅर्म' => '#',
    ];
@endphp

@extends('layouts.app')
@section('title', 'Edit Membership form')

@section('content')
    <div class="container">
        @foreach (['success', 'update_msg', 'delete_msg'] as $msg)
            @if (session($msg))
                <div class="alert alert-{{ $msg == 'delete_msg' ? 'danger' : 'success' }} alert-dismissible fade show">
                    {{ session($msg) }}
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                </div>
            @endif
        @endforeach


        @if ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <h2 class="mb-4 text-center">सदस्यता फॉर्म</h2>
        <div class="row page-titles mx-0">
            <div class="col-md-12 col-sm-12 col-xs-12">
                <form method="POST" action="{{ route('membership.update', $registration->registration_id) }}"
                    enctype="multipart/form-data">
                    @csrf
                    <fieldset>
                        <div
                            class="step-header bg-dark text-white p-3 rounded d-flex justify-content-between align-items-center mb-3">
                            <h5 class="mb-0 text-white">भाग (अ)</h5>
                            <span class="step-number badge bg-light text-dark fs-6">Step 1 / 2</span>
                        </div>

                        <div class="row mb-2">
                            <div class="col-md-6">
                                <div class="text">
                                    <label>आपको बीजेएस संगठन से जोड़ने वाले साथी का मोबाइल नं./संकल्प पत्र क्र. (अगर कोई
                                        है तो)</label>
                                    <input type="text" class="form-control" name="member_id_post" id="member_id_post"
                                        value="{{ old('member_id_post', $registration->member_id_post ?? '') }}">
                                </div>

                                <input type="hidden" id="reference_id" name="reference_id"
                                    value="{{ old('reference_id', $registration->reference_id ?? '') }}" />
                            </div>

                            <div class="col-md-6" style="display:none;">
                                <div class="text">
                                    <label>जोड़ने वाले साथी का नाम </label>
                                    <input type="text" class="form-control" name="member_post_name"
                                        id="member_post_name">
                                </div>
                            </div>

                            <div class="col-lg-3" style="display:none;">
                                <div class="text">
                                    <label>साथी का मोबाइल </label>
                                    <input type="text" disabled name="member_post_mobile" id="member_post_mobile">
                                </div>
                            </div>

                            <div class="col-lg-2">
                                <img src="" id="ref_photo" alt="" width="120">
                            </div>
                        </div>


                        <div class="row">
                            <div class="col-md-2 mb-2">
                                <label for="first_name" class="form-label label-heading">संकल्प पत्रकर्ता नाम <span
                                        class="error">*</span></label>
                                <input type="text" class="form-control" name="name" id="name" required
                                    value="{{ old('name', $registration->name ?? '') }}">
                            </div>


                            <div class="col-md-2 mb-2">
                                <label for="first_name" class="form-label label-heading">पिता/पति का नाम <span
                                        class="error">*</span></label>
                                <input type="text" class="form-control" name="father_name" id="father_name"
                                    value="{{ old('father_name', $registration->father_name ?? '') }}" required>
                            </div>

                            <div class="col-md-2 mb-2">
                                <label for="caste" class="form-label label-heading">जाति <span
                                        class="error">*</span></label>
                                <select name="jati" class="form-control" required>
                                    <option value="">--जाति चुनें--</option>
                                    @foreach ($jatis as $jati)
                                        <option value="{{ $jati->jati_name }}"
                                            {{ old('jati', $registration->jati ?? '') == $jati->jati_name ? 'selected' : '' }}>
                                            {{ $jati->jati_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-2 mb-2">
                                <label for="caste" class="form-label label-heading">श्रेणी <span
                                        class="error">*</span></label>
                                <select name="caste" id="caste" class="form-control" required>
                                    <option value="">--चुनें--</option>
                                    @foreach ($categories as $category)
                                        <option value="{{ $category->category }}"
                                            {{ old('caste', $registration->caste ?? '') == $category->category ? 'selected' : '' }}>
                                            {{ $category->category }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-2 mb-2">
                                <label for="religion" class="form-label label-heading">धर्म <span
                                        class="error">*</span></label>
                                <select name="religion" id="religion" class="form-control" required>
                                    <option value="">--चुनें--</option>
                                    @foreach ($religions as $religion)
                                        <option value="{{ $religion->religion_name }}"
                                            {{ old('religion', $registration->religion ?? '') == $religion->religion_name ? 'selected' : '' }}>
                                            {{ $religion->religion_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-2 mb-2">
                                <label for="dob" class="form-label label-heading">जन्म दिनांक <span
                                        class="error">*</span></label>
                                <input type="date" id="date" name="date" class="form-control"
                                    value="{{ old('date', $registration->dob ?? '') }}" required>
                            </div>

                            <div class="col-md-2 mb-2">
                                <label for="age" class="form-label label-heading">आयु <span
                                        class="error">*</span></label>
                                <input type="text" name="age" id="age" class="form-control"
                                    value="{{ old('age', $registration->age ?? '') }}" required>
                            </div>

                            <div class="col-md-2 mb-2">
                                <label for="gender" class="form-label label-heading">लिंग <span
                                        class="error">*</span></label>
                                <select name="gender" id="gender" class="form-control" required>
                                    <option value="">--चुनें--</option>
                                    <option value="पुरुष"
                                        {{ old('gender', $registration->gender ?? '') == 'पुरुष' ? 'selected' : '' }}>पुरुष
                                    </option>
                                    <option value="स्त्री"
                                        {{ old('gender', $registration->gender ?? '') == 'स्त्री' ? 'selected' : '' }}>स्त्री
                                    </option>
                                    <option value="अन्य"
                                        {{ old('gender', $registration->gender ?? '') == 'अन्य' ? 'selected' : '' }}>अन्य
                                    </option>
                                </select>
                            </div>

                            <div class="col-md-2 mb-2">
                                <label for="mobile_1" class="form-label label-heading">मोबाइल 1 <span
                                        class="error">*</span></label>
                                <span id="msg"></span>
                                <input type="number" name="mobile_1" class="form-control" id="mobile_1"
                                    pattern="[1-9]{1}[0-9]{9}" minlength="10" maxlength="10"
                                    value="{{ old('mobile_1', $registration->mobile1 ?? '') }}" required autocomplete="">
                            </div>

                            <div class="col-md-2 mb-2">
                                <div class="form-check custom-control form-control-lg custom-checkbox">
                                    <input type="checkbox" class="form-check-input custom-control-input"
                                        id="mobile_1_whataspp" name="mobile_1_whatsapp" value="1"
                                        {{ old('mobile_1_whatsapp', $registration->mobile1_whatsapp ?? 0) == 1 ? 'checked' : '' }}>
                                    <label class="custom-control-label form-check-label" for="mobile_1_whataspp">
                                        व्हाट्सएप नं.?</label>
                                </div>
                            </div>

                            <div class="col-md-2 mb-2">
                                <label for="mobile_2" class="form-label label-heading">मोबाइल 2</label>
                                <input type="number" name="mobile_2" class="form-control" id="mobile_2"
                                    pattern="[1-9]{1}[0-9]{9}" minlength="10" maxlength="10"
                                    value="{{ old('mobile_2', $registration->mobile2 ?? '') }}">
                            </div>


                            {{-- <div class="col-md-3">
                                <label for="" class="form-label label-heading">क्या ये व्हाट्सएप नंबर है? </label>
                                <input type="checkbox" name="mobile_2_whataspp" id="mobile_2_whataspp"
                                    class="form-control" value="2" style="width: 50px;display: initial;">
                            </div> --}}

                            <div class="col-md-2 mb-2">
                                <div class="form-check custom-control form-control-lg custom-checkbox">
                                    <input type="checkbox" class="form-check-input custom-control-input"
                                        id="mobile_2_whataspp" name="mobile_2_whatsapp" value="1"
                                        {{ old('mobile_2_whatsapp', $registration->mobile2_whatsapp ?? 0) == 1 ? 'checked' : '' }}>
                                    <label class="custom-control-label form-check-label" for="mobile_2_whataspp">
                                        व्हाट्सएप नं.?</label>
                                </div>
                            </div>

                            <div class="col-md-2 mb-2">
                                <label for="email" class="form-label label-heading">ईमेल आईडी <span
                                        class="error">*</span></label>
                                <input type="email" value="{{ old('email', $registration->email ?? '') }}" name="email"
                                    class="form-control" id="email" required>
                            </div>

                            <div class="col-md-2 mb-2">
                                <label for="education" class="form-label label-heading ">शैक्षणिक योग्यता <span
                                        class="error">*</span></label>
                                <select name="education" id="education" class="form-control" required>
                                    <option value="">--चुनें--</option>
                                    @foreach ($educations as $education)
                                        <option value="{{ $education->education_name }}"
                                            {{ old('education', $registration->education ?? '') == $education->education_name ? 'selected' : '' }}>
                                            {{ $education->education_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-2 mb-2">
                                <label for="business" class="form-label label-heading required">व्यवसाय <span
                                        class="error">*</span></label>
                                <select name="business" id="business" class="form-control" required>
                                    <option value="">--चुनें--</option>
                                    @foreach ($businesses as $business)
                                        <option value="{{ $business->business_name }}"
                                            {{ old('business', $registration->business ?? '') == $business->business_name ? 'selected' : '' }}>
                                            {{ $business->business_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-2 mb-2">
                                <label class="form-label label-heading required">बी.जे.एस सदस्यता </label>
                                <select name="membership" class="form-control" id="membership" required>
                                    <option value="">--चुनें--</option>
                                    <option value="समर्पित कार्यकर्ता"
                                        {{ old('membership', $registration->membership ?? '') == 'समर्पित कार्यकर्ता' ? 'selected' : '' }}>
                                        समर्पित कार्यकर्ता</option>
                                    <option value="सक्रिय कार्यकर्ता"
                                        {{ old('membership', $registration->membership ?? '') == 'सक्रिय कार्यकर्ता' ? 'selected' : '' }}>
                                        सक्रिय कार्यकर्ता</option>
                                    <option value="साधारण कार्यकर्ता"
                                        {{ old('membership', $registration->membership ?? '') == 'साधारण कार्यकर्ता' ? 'selected' : '' }}>
                                        साधारण कार्यकर्ता</option>
                                </select>
                            </div>

                            <div class="col-md-2 mb-2" style="display: none;">
                                <label for="position" class="form-label label-heading">व्यवसायिक पद </label>
                                <div class="form-select">
                                    <input type="text" name="position" id="position" class="form-control"
                                        value="{{ old('position', $registration->position ?? '') }}">
                                </div>
                            </div>

                            <div class="col-md-2 mb-2">
                                <label class="form-label label-heading ">राजनीतिक सक्रियता <span
                                        class="error">*</span></label>
                                <select name="party_name" id="party_name" class="form-control" required>
                                    <option value="">--चुनें--</option>
                                    @foreach ($politics as $politic)
                                        <option value="{{ $politic->name }}"
                                            {{ old('party_name', $registration->step4->party_name ?? '') == $politic->name ? 'selected' : '' }}>
                                            {{ $politic->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-2 mb-2">
                                <label class="form-label label-heading">पद वर्तमान/भूतपूर्व </label>
                                <input type="text" name="present_post" class="form-control" id="present_post"
                                    value="{{ old('present_post', $registration->step4->present_post ?? '') }}" placeholder="">
                            </div>
                        </div>

                        <fieldset>
                            <div
                                class="step-header bg-dark text-white p-3 rounded d-flex justify-content-between align-items-center mb-3 mt-3">
                                <h5 class="mb-0 text-white">परिवार के सदस्य/मित्र/पड़ोसी और रुचि</h5>
                            </div>

                            <div class="row mt-1">
                                <div class="col-md-2 mb-2">
                                    <label for="interestSelect" class="form-label label-heading">रुचि चुनें</label>
                                    <select name="interest[]" id="interestSelect" class="form-control" multiple>
                                        @php
                                            $selectedInterests = [];
                                            if ($registration->step3 && $registration->step3->intrest) {
                                                $selectedInterests = explode(',', $registration->step3->intrest);
                                            }
                                        @endphp

                                        @foreach ($interests as $interest)
                                            <option value="{{ $interest->interest_name }}"
                                                {{ in_array($interest->interest_name, old('interest', $selectedInterests ?? [])) ? 'selected' : '' }}>
                                                {{ $interest->interest_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-2 mb-2">
                                    <label for="voter_id" class="form-label label-heading">मतदान आई.डी.
                                    </label>
                                    <input type="text" name="voter_id" id="voter_id" class="form-control"
                                        value="{{ old('voter_id', $registration->voter_id ?? '') }}" placeholder="">
                                </div>

                                <div class="col-md-2 mb-2">
                                    <label for="total_member" class="form-label label-heading">परिवार में कुल सदस्य <span
                                            class="error">*</span>
                                    </label>
                                    <input type="text" name="total_member" id="total_member" class="form-control"
                                        placeholder=""
                                        value="{{ old('total_member', $registration->step3->total_member ?? '') }}" required>
                                </div>
                                <div class="col-md-2 mb-2">
                                    <label for="total_voter" class="form-label label-heading ">परिवार में कुल मतदाता <span
                                            class="error">*</span>
                                    </label>
                                    <input type="text" name="total_voter" id="total_voter"
                                        value="{{ old('total_voter', $registration->step3->total_voter ?? '') }}"
                                        class="form-control" placeholder="" required>
                                </div>

                                <div class="col-md-2 mb-2" style="display:none;">
                                    <label for="member_job" class="form-label label-heading">शासकीय/अशासकीय सेवा में सदस्य
                                    </label>
                                    <input type="text" name="member_job" id="member_job" class="form-control"
                                        placeholder="">
                                </div>

                                 <div class="col-md-2 mb-2">
                                    <label for="member_name_1" class="form-label label-heading">परिवार सदस्य नाम <span
                                            class="error">*</span></label>

                                    <input type="text" name="member_name_1" class="form-control" id="member_name_1"
                                        required value="{{ old('member_name_1', $registration->step3->member_name_1 ?? '') }}">
                                </div>

                                <div class="col-md-2 mb-2">
                                    <label for="member_mobile_1" class="form-label label-heading">परिवार सदस्य मोबाइल
                                        <span class="error">*</span></label>
                                    <input type="number" name="member_mobile_1" class="form-control"
                                        id="member_mobile_1" pattern="[1-9]{1}[0-9]{9}" minlength="10" maxlength="10"
                                        required
                                        value="{{ old('member_mobile_1', $registration->step3->member_mobile_1 ?? '') }}">
                                </div>

                                {{-- <div class="col-md-2 mb-2">
                                    <label for="friend_name_1" class="form-label label-heading">मित्र नाम</label>
                                    <input type="text" name="friend_name_1" class="form-control" id="friend_name_1"
                                        value="{{ old('friend_name_1', $registration->step3->friend_name_1 ?? '') }}">
                                </div>

                                <div class="col-md-2 mb-2">
                                    <label for="friend_mobile_1" class="form-label label-heading">मित्र मोबाइल </label>
                                    <input type="text" name="friend_mobile_1" class="form-control"
                                        id="friend_mobile_1" pattern="[1-9]{1}[0-9]{9}" maxlength="10"
                                        value="{{ old('friend_mobile_1', $registration->step3->friend_mobile_1 ?? '') }}">
                                </div> --}}
                            </div>
                        </fieldset>

                        {{-- <fieldset>
                            <div
                                class="step-header bg-dark text-white p-3 rounded d-flex justify-content-between align-items-center mb-3 mt-3">
                                <h5 class="mb-0 text-white">परिवार के सदस्य/मित्र/पड़ोसी और रुचि</h5>
                            </div>

                            <div class="row mt-2">
                                
                            </div>
                        </fieldset> --}}

                        <fieldset>
                            <div
                                class="step-header bg-dark text-white p-3 rounded d-flex justify-content-between align-items-center mb-3 mt-3">
                                <h5 class="mb-0 text-white">घर में वाहनो की संख्या ?</h5>
                            </div>

                            <div class="row mt-2">
                                <div class="col-md-2 mb-2">
                                    <label class="form-label label-heading">मोटरसाइकिल</label>
                                    <input type="text" name="vehicle3" class="form-control" id="vehicle3"
                                        value="{{ old('vehicle3', $registration->step3->vehicle3 ?? '') }}">
                                </div>

                                <div class="col-md-2 mb-2">
                                    <label class="form-label label-heading">कार</label>
                                    <input type="text" class="form-control" name="vehicle1" id="vehicle1"
                                        value="{{ old('vehicle1', $registration->step3->vehicle1 ?? '') }}">
                                </div>

                                <div class="col-md-2 mb-2">
                                    <label class="form-label label-heading">ट्रेक्टर</label>
                                    <input type="text" class="form-control" name="vehicle2" id="vehicle2"
                                        value="{{ old('vehicle2', $registration->step3->vehicle2 ?? '') }}">
                                </div>
                            </div>
                        </fieldset>

                        <fieldset style="display: none;">
                            <legend>
                                <span class="step-heading" style="border-bottom: solid; width: 100%;">मित्र / पड़ोसी
                                </span>

                            </legend>

                            <div class="row">
                                <div class="col-lg-6 col-md-6 col-12">
                                    <label for="friend_name_1" class="form-label label-heading">नाम</label>
                                    <div class="form-group">
                                        <input type="text" class="form-control" name="friend_name_1"
                                            id="friend_name_1">
                                    </div>
                                </div>
                                <div class="col-lg-6 col-md-6 col-12">
                                    <label for="friend_mobile_1" class="form-label label-heading">मोबाइल </label>
                                    <div class="form-group">
                                        <input type="number" class="form-control" name="friend_mobile_1"
                                            id="friend_mobile_1" pattern="[1-9]{1}[0-9]{9}" minlength="10"
                                            maxlength="10">
                                    </div>
                                </div>

                            </div>

                        </fieldset>
                    </fieldset>

                    <fieldset>
                        <div
                            class="step-header bg-dark text-white p-3 rounded d-flex justify-content-between align-items-center mt-3 mb-3">
                            <h5 class="mb-0 text-white">भाग (बी)</h5>
                            <span class="step-number badge bg-light text-dark fs-6">Step 2 / 2</span>
                        </div>

                        <div class="row mt-2">
                            <div class="col-md-2 mb-2">
                                <label for="division_name" class="form-label label-heading required">संभाग का नाम <span
                                        class="error">*</span></label>
                                <select name="division_name" class="form-control" required>
                                    <option value="">--संभाग चुनें--</option>
                                    @foreach ($divisions as $division)
                                        <option value="{{ $division->division_id }}"
                                            {{ $registration->step2 && $registration->step2->division_id == $division->division_id ? 'selected' : '' }}>
                                            {{ $division->division_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-2 mb-2">
                                <label for="district" class="form-label label-heading required">जिले का नाम <span
                                        class="error">*</span></label>
                                <select name="district" id="district" required class="form-control">
                                    @if ($registration->step2 && $registration->step2->districtRelation)
                                        <option value="{{ $registration->step2->district }}">
                                            {{ $registration->step2->districtRelation->district_name ?? 'N/A' }}
                                        </option>
                                    @endif
                                </select>
                            </div>



                            <div class="col-md-2 mb-2">
                                <label for="loksabha" class="form-label label-heading required">लोकसभा <span
                                        class="error">*</span></label>
                                <select name="loksabha" id="loksabha" required class="form-control">
                                    @php
                                        $loksabha = $registration->step2
                                            ? $registration->step2->loksabhaRelation()
                                            : null;
                                    @endphp
                                    @if ($loksabha)
                                        <option value="{{ $registration->step2->loksabha }}" selected>
                                            {{ $loksabha->loksabha ?? 'N/A' }}
                                        </option>
                                    @else
                                        <option value="{{ $registration->step2->loksabha ?? '' }}" selected>
                                            {{ $registration->step2->loksabha ?? 'N/A' }}
                                        </option>
                                    @endif
                                </select>
                            </div>


                            <div class="col-md-2 mb-2">
                                <label for="vidhansabha" class="form-label label-heading required">विधानसभा नाम/क्रमांक
                                    <span class="error">*</span></label>
                                <select name="vidhansabha" id="vidhansabha" required class="form-control">
                                    @if ($registration->step2 && $registration->step2->vidhansabhaRelation)
                                        <option value="{{ $registration->step2->vidhansabha }}">
                                            {{ $registration->step2->vidhansabhaRelation->vidhansabha ?? 'N/A' }}
                                        </option>
                                    @endif
                                </select>
                            </div>

                            <div class="col-md-2 mb-2">
                                <label for="mandal" class="form-label label-heading">मंडल का नाम <span
                                        class="error">*</span></label>
                                <select name="mandal" id="mandal" class="form-control">
                                    @if ($registration->step2 && $registration->step2->mandalRelation)
                                        <option value="{{ $registration->step2->mandal }}" selected>
                                            {{ $registration->step2->mandalRelation->mandal_name ?? 'N/A' }}
                                        </option>
                                    @endif
                                </select>
                            </div>

                            <div class="col-md-2 mb-2">
                                <label for="mandal_type" class="form-label label-heading">मंडल का प्रकार </label>
                                <select name="mandal_type" id="mandal_type" class="form-control">
                                    <option value=''>--चुनें--</option>
                                    <option value="1"
                                        {{ old('mandal_type', $registration->step2->mandal_type ?? '') == 1 ? 'selected' : '' }}>
                                        ग्रामीण मंडल</option>
                                    <option value="2"
                                        {{ old('mandal_type', $registration->step2->mandal_type ?? '') == 2 ? 'selected' : '' }}>
                                        नगर मंडल</option>
                                </select>
                            </div>

                            <div class="col-md-2 mb-2">
                                <label for="nagar" class="form-label label-heading">कमांड एरिया <span
                                        class="error">*</span></label>
                                <select name="nagar" id="nagar" class="form-control">
                                    @if ($registration->step2 && $registration->step2->nagarRelation)
                                        <option value="{{ $registration->step2->nagar }}">
                                            {{ $registration->step2->nagarRelation->nagar_name ?? 'N/A' }}
                                        </option>
                                    @endif
                                </select>
                            </div>


                            <div class="col-md-2 mb-2">
                                <label for="matdan_kendra_name" class="form-label label-heading">मतदान केंद्र/क्रमांक
                                    <span class="error">*</span></label>
                                <select name="matdan_kendra_name" class="form-control" id="matdan_kendra_name">
                                    @if ($registration->step2 && $registration->step2->polling)
                                        <option value="{{ $registration->step2->polling->gram_polling_id }}"
                                            data-polling-no="{{ $registration->step2->matdan_kendra_no }}" selected>
                                            {{ $registration->step2->matdan_kendra_no  ?? ''}} -
                                            {{ $registration->step2->polling->polling_name  ?? ''}}
                                        </option>
                                    @endif
                                </select>

                                <input type="hidden" name="matdan_kendra_no" id="matdan_kendra_no"
                                    value="{{ $registration->step2->matdan_kendra_no ?? 0 }}">
                            </div>



                            <div class="col-md-2 mb-2">
                                <label for="area" class="form-label label-heading">निवासी ग्राम/वार्ड चौपाल <span
                                        class="error">*</span></label>
                                <select name="area_name" class="form-control" id="area_name">
                                    @if ($registration->step2 && $registration->step2->areaRelation)
                                        <option value="{{ $registration->step2->area_id }}">
                                            {{ $registration->step2->areaRelation->area_name ?? 'N/A' }}
                                        </option>
                                    @endif
                                </select>
                            </div>

                            <div class="col-md-3 mb-2">
                                <label for="permanent_address" class="form-label label-heading required">स्थाई पता <span
                                        class="error">*</span></label>
                                <textarea type="textarea" class="form-control" name="permanent_address" id="permanent_address" rows="2"
                                    required="">{{ old('permanent_address', $registration->step3->permanent_address ?? '') }}</textarea>
                            </div>


                            {{-- <div class="col-md-3 mb-2">
                                <label class="form-label label-heading d-flex justify-content-between align-items-center">
                                    अस्थाई पता
                                    <span class="d-flex align-items-center">
                                        स्थाई पता के समान&nbsp;
                                        <input type="checkbox" name="permanent_address_check"
                                            id="permanent_address_check"
                                            {{ old('permanent_address_check', $registration->step3->temp_address == $registration->step3->permanent_address ? 'checked' : '') }}>
                                    </span>
                                </label>
                                <textarea class="form-control" name="temp_address" id="temp_address" rows="2">{{ old('temp_address', $registration->step3->temp_address ?? '') }}</textarea>
                            </div> --}}

                              <div class="col-md-3 mb-2">
                                <label class="form-label label-heading d-flex justify-content-between align-items-center">
                                    अस्थाई पता
                                    <span class="d-flex align-items-center">
                                        स्थाई पता के समान&nbsp;
                                        <input type="checkbox" name="permanent_address_check"
                                            id="permanent_address_check"
                                            {{ old('permanent_address_check', optional($registration->step3)->temp_address == optional($registration->step3)->permanent_address ? 'checked' : '') }}>
                                    </span>
                                </label>
                                <textarea class="form-control" name="temp_address" id="temp_address" rows="2">{{ old('temp_address', optional($registration->step3)->temp_address ?? '') }}</textarea>
                            </div>

                            <div class="col-md-2 mb-2" style="display: none;">
                                <div class="form-group">
                                    <label for="matdan_kendra_name" class="form-label label-heading">पिनकोड नंबर</label>
                                    <input type="text" name="pincode" class="form-control" />
                                </div>
                            </div>

                            <div class="col-md-2 mb-2" style="display: none;">
                                <label for="member_job" class="form-label label-heading">परिवार की समग्र आई.डी. नंबर
                                </label>
                                <div class="form-group">

                                    <input type="text" name="samagra_id" id="samagra_id" placeholder=""
                                        class="form-control">
                                </div>
                            </div>

                            <div class="col-md-2 mb-2" style="display: none;">
                                <div class="form-group">
                                    <label for="voter_number" class="form-label label-heading">वोटर आई.डी. नंबर</label>
                                    <input type="text" name="voter_number" class="file" id="voter_number"
                                        class="form-control">
                                </div>
                            </div>

                            <div class="col-md-4 mb-2">
                                <label for="voter_front" class="form-label label-heading d-block">वोटर आई.डी. आगे का फोटो
                                    <span class="error">*</span></label>

                                @if (!empty($registration->step2) && !empty($registration->step2->voter_front))
                                    <img id="voter_front_photo"
                                        src="{{ asset('assets/upload/step2/' . $registration->step2->voter_front) }}"
                                        alt="Voter Front" width="200" height="180" class="img-thumbnail2">
                                @else
                                    <img id="voter_front_photo"  src="{{ asset('assets/no-image.png') }}"  alt="No Voter Front Image" width="200" height="180"
                                         class="img-thumbnail2">
                                @endif
                                <input type="file" name="voter_front" id="voter_front"
                                    class="form-control file mt-2">
                            </div>

                            <div class="col-md-4 mb-2">
                                <label for="voter_back" class="form-label label-heading d-block">वोटर आई.डी. पीछे का फोटो
                                    <span class="error">*</span></label>

                                @if (!empty($registration->step2) && !empty($registration->step2->voter_back))
                                    <img id="voter_back_photo"
                                        src="{{ asset('assets/upload/step2/' . $registration->step2->voter_back) }}"
                                        alt="Voter Back" width="200" height="180" class="img-thumbnail2">
                                @else
                                    <img id="voter_back_photo" src="{{ asset('assets/no-image.png') }}"  alt="No Voter Back Image" width="200" height="180" class="img-thumbnail2">
                                @endif
                                <input type="file" name="voter_back" id="voter_back" class="form-control file mt-2">
                            </div>

                            <div class="col-md-4 mb-2">
                                <label for="photo" class="form-label label-heading d-block required">संकल्प कर्ता का
                                    फोटो <span class="error">*</span>
                                </label>

                                @if (!empty($registration->photo))
                                    <img id="photo_preview" src="{{ asset('assets/upload/' . $registration->photo) }}"
                                        alt="Member Photo" width="200" height="180" class="img-thumbnail2">
                                @else
                                    <img id="photo_preview" src="{{ asset('assets/no-image.png') }}"  alt="No Member Image" width="200" height="180" class="img-thumbnail2">
                                @endif
                                <input type="file" accept="" class="form-control file mt-2" id="photo"
                                    name="file" />
                            </div>

                            {{-- <div class="col-lg-3 col-md-3 col-12">
                                <div style="background-image:url('img/back_side.png'); height: 574px; width: 266px;"
                                    id="preview_photo_back"><img id="preview_photo" src="#" alt=""
                                        style="margin-top: 144px; margin-left: 65px; width: 132px; height: 201px;" />
                                </div>
                            </div> --}}

                            <div class="col-lg-12 col-md-12 col-12 mb-4">
                                <label class="form-label label-heading">सदस्यता का कारण/उदेश्य : आप बीजेएस के सदस्य क्यों
                                    बन रहे हैं
                                </label>
                                <textarea name="reason_join" id="reason_join" placeholder="" rows="3" class="form-control"> {{ old('reason_join', $registration->step4->reason_join ?? '') }}</textarea>
                            </div>

                            <div class="col-lg-12">
                                <lable class="form-label label-heading required"></label>
                                    <input type="checkbox" id="final_check"
                                        style="width: 14px;display: initial;height:14px;" class="form-control" checked
                                        required>
                                    अंतरात्मा को साक्षी मानकर मैं संकल्प लेता हूं कि भारतीय जनसेवा संगठन के माध्यम से बिना
                                    जाति, लिंग, धर्म, समाज का भेद किये गरीब शोषित पीड़ित उपेक्षित आखरी व्यक्ति के जीवन
                                    उत्थान के लिए समर्पण भाव से कार्य करुंगा तथा इसमें बाधक किसी भी प्रकार के शोषण
                                    भ्रष्टाचार अनाचार अत्याचार का संगठित बिरोध करते हुए एकात्म मानव विकास की दिशा में
                                    समर्पित भाव से कार्यरत रहूंगा !
                            </div>
                        </div>
                    </fieldset>




                    <div class="form-group mt-3">
                        <button type="submit" class="btn btn-primary">
                            अपडेट करें
                        </button>
                    </div>

                </form>
            </div>
        </div>
    </div>


    @push('scripts')
        <script>
            $(document).ready(function() {
                $('#interestSelect').select2({
                    placeholder: "रुचि चुनें",
                    allowClear: true,
                    width: '100%'
                });
            });

            $(document).ready(function() {
                $('select[name="division_name"]').on('change', function() {
                    var divisionId = $(this).val();

                    if (divisionId) {
                        $.ajax({
                            url: '{{ route('get.districts') }}',
                            type: 'POST',
                            data: {
                                division_id: divisionId,
                                _token: '{{ csrf_token() }}'
                            },
                            success: function(data) {
                                let districtSelect = $('select[name="district"]');
                                districtSelect.empty().append(
                                    '<option value="">--ज़िला चुनें--</option>');
                                $.each(data, function(key, district) {
                                    districtSelect.append('<option value="' + district
                                        .district_id + '">' + district.district_name +
                                        '</option>');
                                });

                                // Reset loksabha and vidhansabha
                                $('#loksabha').html(
                                    '<option value="">--लोकसभा चुनें--</option>');
                                $('#vidhansabha').html(
                                    '<option value="">--विधानसभा चुनें--</option>');
                            },
                            error: function() {
                                alert('Error loading districts');
                            }
                        });
                    } else {
                        $('select[name="district"]').html('<option value="">--ज़िला चुनें--</option>');
                        $('#loksabha').html('<option value="">--लोकसभा चुनें--</option>');
                        $('#vidhansabha').html('<option value="">--विधानसभा चुनें--</option>');
                    }
                });

                $('select[name="district"]').on('change', function() {
                    var districtId = $(this).val();

                    if (districtId) {
                        $.ajax({
                            url: '{{ route('get.vidhansabhaD') }}',
                            type: 'POST',
                            data: {
                                district_id: districtId,
                                _token: '{{ csrf_token() }}'
                            },
                            success: function(data) {
                                $('#vidhansabha').empty().append(
                                    '<option value="">--विधानसभा चुनें--</option>');
                                $('#loksabha').empty().append(
                                    '<option value="">--लोकसभा चुनें--</option>');

                                const loksabhaSet = new Set();

                                $.each(data, function(index, item) {
                                    $('#vidhansabha').append('<option value="' + item
                                        .vidhansabha_id + '">' + item.vidhansabha +
                                        '</option>');
                                    if (item.loksabha) {
                                        loksabhaSet.add(item.loksabha);
                                    }
                                });

                                loksabhaSet.forEach(function(lok) {
                                    $('#loksabha').append('<option value="' + lok + '">' +
                                        lok + '</option>');
                                });
                            },
                            error: function() {
                                alert('Error fetching vidhansabha/loksabha');
                            }
                        });
                    } else {
                        $('#vidhansabha').html('<option value="">--विधानसभा चुनें--</option>');
                        $('#loksabha').html('<option value="">--लोकसभा चुनें--</option>');
                    }
                });


                $('#vidhansabha').on('change', function() {
                    const vidhansabhaId = $(this).val();

                    if (vidhansabhaId) {
                        $.ajax({
                            url: "/admin/get-mandal/" + vidhansabhaId,
                            type: "GET",
                            success: function(mandals) {
                                const $mandal = $('#mandal');
                                $mandal.empty();
                                $mandal.append('<option value="">--मंडल चुनें--</option>');

                                mandals.forEach(function(option) {
                                    $mandal.append(option);
                                });

                                $mandal.prop('disabled', false);
                            },
                            error: function() {
                                alert('Error fetching Mandal');
                            }
                        });
                    } else {
                        $('#mandal').empty().append('<option value="">--मंडल चुनें--</option>').prop(
                            'disabled', true);
                    }
                });

                $('#mandal').on('change', function() {
                    const mandalId = $(this).val();

                    if (mandalId) {
                        $.ajax({
                            url: "/admin/get-nagar/" + mandalId,
                            type: "GET",
                            success: function(nagars) {
                                const $nagar = $('#nagar');
                                $nagar.empty();
                                $nagar.append('<option value="">--कमांड एरिया चुनें--</option>');

                                nagars.forEach(function(nagar) {
                                    $nagar.append(nagar);
                                });

                                $nagar.prop('disabled', false);
                            },
                            error: function() {
                                alert('Error fetching command area');
                            }
                        });
                    } else {
                        $('#nagar').empty().append('<option value="">--कमांड एरिया चुनें--</option>').prop(
                            'disabled', true);
                    }
                });


                $('#nagar').on('change', function() {
                    const nagarId = $(this).val();

                    if (nagarId) {
                        $.ajax({
                            url: "/admin/get-polling/" + nagarId,
                            type: "GET",
                            success: function(pollings) {
                                const $polling = $('#matdan_kendra_name');
                                $polling.empty();
                                $polling.append('<option value="">--मतदान केंद्र चुनें--</option>');

                                pollings.forEach(function(polling) {
                                    let label = (polling.polling_no ? polling.polling_no +
                                        ' - ' : '') + polling.polling_name;
                                    $polling.append(` <option value="${polling.gram_polling_id}" 
                            data-polling-no="${polling.polling_no || ''}" 
                            data-polling-name="${polling.polling_name}">
                        ${polling.polling_no ? polling.polling_no + ' - ' : ''}${polling.polling_name}
                    </option>`);
                                });

                                $polling.prop('disabled', false);
                            },
                            error: function() {
                                alert('Error fetching polling center');
                            }
                        });
                    } else {
                        $('#matdan_kendra_name')
                            .empty()
                            .append('<option value="">--मतदान केंद्र चुनें--</option>')
                            .prop('disabled', true);
                    }
                });


                $('#matdan_kendra_name').on('change', function() {
                    const pollingId = $(this).val();

                    if (pollingId) {
                        $.ajax({
                            url: "/admin/get-area/" + pollingId,
                            method: "GET",
                            success: function(areas) {
                                const $area = $('#area_name');
                                $area.empty();
                                $area.append('<option value="">--ग्राम/वार्ड चुनें--</option>');

                                areas.forEach(function(area) {
                                    $area.append(
                                        `<option value="${area.area_id}">${area.area_name}</option>`
                                    );
                                });

                                $area.prop('disabled', false);
                            },
                            error: function(xhr) {
                                alert("Error Fetching Area");
                            }
                        });
                    } else {
                        $('#area_name')
                            .empty()
                            .append('<option value="">--ग्राम/वार्ड चुनें--</option>')
                            .prop('disabled', true);
                    }
                });

                $('#matdan_kendra_name').on('change', function() {
                    const selected = $(this).find('option:selected');
                    const pollingNo = selected.data('polling-no') || 0;
                    const pollingName = selected.data('polling-name') || '';

                    $('#matdan_kendra_no').val(pollingNo);
                    console.log('Polling No:', pollingNo, 'Polling Name:', pollingName);
                });



                $('#permanent_address_check').on('change', function() {
                    if ($(this).is(':checked')) {
                        let permAddress = $('#permanent_address').val();
                        $('#temp_address').val(permAddress).prop('readonly', true);
                    } else {
                        $('#temp_address').val('').prop('readonly', false);
                    }
                });

                $('#permanent_address').on('input', function() {
                    if ($('#permanent_address_check').is(':checked')) {
                        $('#temp_address').val($(this).val());
                    }
                });

            });
        </script>
    @endpush
@endsection
