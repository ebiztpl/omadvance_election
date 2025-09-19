@php
    $pageTitle = 'सदस्यता फाॅर्म';
    $breadcrumbs = [
        'एडमिन' => '#',
        'सदस्यता फाॅर्म' => '#',
    ];
@endphp

@extends('layouts.app')
@section('title', 'Membership form')

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
                <form method="POST" action="{{ route('membership.store') }}" enctype="multipart/form-data">
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
                                    <input type="text" class="form-control" name="member_id_post" id="member_id_post">
                                </div>

                                <input type="hidden" id="reference_id" name="reference_id" />
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
                                <input type="text" class="form-control" name="name" id="name" required>
                            </div>


                            <div class="col-md-2 mb-2">
                                <label for="first_name" class="form-label label-heading">पिता/पति का नाम <span
                                        class="error">*</span></label>
                                <input type="text" class="form-control" name="father_name" id="father_name" required>
                            </div>

                            <div class="col-md-2 mb-2">
                                <label for="caste" class="form-label label-heading">जाति <span
                                        class="error">*</span></label>
                                <select name="jati" class="form-control" required>
                                    <option value="">--जाति चुनें--</option>
                                    @foreach ($jatis as $jati)
                                        <option value="{{ $jati->jati_name }}">{{ $jati->jati_name }}
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
                                        <option value="{{ $category->category }}">{{ $category->category }}
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
                                        <option value="{{ $religion->religion_name }}">{{ $religion->religion_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-2 mb-2">
                                <label for="dob" class="form-label label-heading">जन्म दिनांक <span
                                        class="error">*</span></label>
                                <input type="date" id="date" name="date" class="form-control" required>
                            </div>

                            <div class="col-md-2 mb-2">
                                <label for="age" class="form-label label-heading">आयु <span
                                        class="error">*</span></label>
                                <input type="text" name="age" id="age" class="form-control" required>
                            </div>

                            <div class="col-md-2 mb-2">
                                <label for="gender" class="form-label label-heading">लिंग <span
                                        class="error">*</span></label>
                                <select name="gender" id="gender" class="form-control" required>
                                    <option value="">--चुनें--</option>
                                    <option value="पुरुष">पुरुष</option>
                                    <option value="स्त्री">स्त्री</option>
                                    <option value="अन्य">अन्य</option>
                                </select>
                            </div>

                            <div class="col-md-2 mb-2">
                                <label for="mobile_1" class="form-label label-heading">मोबाइल 1 <span
                                        class="error">*</span></label>
                                <span id="msg"></span>
                                <input type="number" name="mobile_1" class="form-control" id="mobile_1" required
                                    autocomplete="">
                                <div class="invalid-feedback text-danger" id="mobile_1-error" style="display:none;">
                                </div>
                            </div>

                            <div class="col-md-2 mb-2">
                                <div class="form-check custom-control form-control-lg custom-checkbox">
                                    <input type="checkbox" class="form-check-input custom-control-input"
                                        id="mobile_1_whatsapp" name="mobile_1_whatsapp" value="1">
                                    <label class="custom-control-label form-check-label" for="mobile_1_whatsapp">
                                        व्हाट्सएप नं.?
                                    </label>
                                </div>
                            </div>



                            <div class="col-md-2 mb-2">
                                <label for="mobile_2" class="form-label label-heading">मोबाइल 2</label>
                                <input type="number" name="mobile_2" class="form-control" id="mobile_2"
                                    pattern="[1-9]{1}[0-9]{9}" minlength="10" maxlength="10">
                            </div>


                            {{-- <div class="col-md-3">
                                <label for="" class="form-label label-heading">क्या ये व्हाट्सएप नंबर है? </label>
                                <input type="checkbox" name="mobile_2_whataspp" id="mobile_2_whataspp"
                                    class="form-control" value="2" style="width: 50px;display: initial;">
                            </div> --}}

                            <div class="col-md-2 mb-2">
                                <div class="form-check custom-control form-control-lg custom-checkbox">
                                    <input type="checkbox" class="form-check-input custom-control-input"
                                        id="mobile_2_whatsapp" name="mobile_2_whatsapp" value="1">
                                    <label class="custom-control-label form-check-label" for="mobile_2_whatsapp">
                                        व्हाट्सएप नं.?
                                    </label>
                                </div>
                            </div>

                            <div class="col-md-2 mb-2">
                                <label for="email" class="form-label label-heading">ईमेल आईडी <span
                                        class="error">*</span></label>
                                <input type="email" name="email" class="form-control" id="email" required>
                            </div>

                            <div class="col-md-2 mb-2">
                                <label for="education" class="form-label label-heading ">शैक्षणिक योग्यता <span
                                        class="error">*</span></label>
                                <select name="education" id="education" class="form-control" required>
                                    <option value="">--चुनें--</option>
                                    @foreach ($educations as $education)
                                        <option value="{{ $education->education_name }}">{{ $education->education_name }}
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
                                        <option value="{{ $business->business_name }}">{{ $business->business_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-2 mb-2">
                                <label class="form-label label-heading required">बी.जे.एस सदस्यता </label>
                                <select name="membership" class="form-control" id="membership" required>
                                    <option value="">--चुनें--</option>
                                    <option value="समर्पित कार्यकर्ता">समर्पित कार्यकर्ता</option>
                                    <option value="सक्रिय कार्यकर्ता">सक्रिय कार्यकर्ता</option>
                                    <option value="साधारण कार्यकर्ता">साधारण कार्यकर्ता</option>
                                </select>
                            </div>

                            <div class="col-md-2 mb-2" style="display: none;">
                                <label for="position" class="form-label label-heading">व्यवसायिक पद </label>
                                <div class="form-select">
                                    <input type="text" name="position" id="position" class="form-control">
                                </div>
                            </div>

                            <div class="col-md-2 mb-2">
                                <label class="form-label label-heading ">राजनीतिक सक्रियता <span
                                        class="error">*</span></label>
                                <select name="party_name" id="party_name" class="form-control" required>
                                    <option value="">--चुनें--</option>
                                    @foreach ($politics as $politic)
                                        <option value="{{ $politic->name }}">{{ $politic->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-2 mb-2">
                                <label class="form-label label-heading">पद वर्तमान/भूतपूर्व </label>
                                <input type="text" name="present_post" class="form-control" id="present_post"
                                    placeholder="">
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
                                        @foreach ($interests as $interest)
                                            <option value="{{ $interest->interest_name }}">{{ $interest->interest_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-2 mb-2">
                                    <label for="voter_id" class="form-label label-heading">मतदान आई.डी.
                                    </label>
                                    <input type="text" name="voter_id" id="voter_id" class="form-control"
                                        placeholder="">
                                </div>

                                <div class="col-md-2 mb-2">
                                    <label for="total_member" class="form-label label-heading">परिवार में कुल सदस्य <span
                                            class="error">*</span>
                                    </label>
                                    <input type="text" name="total_member" id="total_member" class="form-control"
                                        placeholder="" required>
                                </div>
                                <div class="col-md-2 mb-2">
                                    <label for="total_voter" class="form-label label-heading ">परिवार में कुल मतदाता <span
                                            class="error">*</span>
                                    </label>
                                    <input type="text" name="total_voter" id="total_voter" class="form-control"
                                        placeholder="" required>
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
                                        required>
                                </div>

                                <div class="col-md-2 mb-2">
                                    <label for="member_mobile_1" class="form-label label-heading">परिवार सदस्य मोबाइल
                                        <span class="error">*</span></label>
                                    <input type="number" name="member_mobile_1" class="form-control"
                                        id="member_mobile_1" pattern="[1-9]{1}[0-9]{9}" minlength="10" maxlength="10"
                                        required>
                                </div>

                                {{-- <div class="col-md-2 mb-2">
                                    <label for="friend_name_1" class="form-label label-heading">मित्र नाम</label>
                                    <input type="text" name="friend_name_1" class="form-control" id="friend_name_1">
                                </div>

                                <div class="col-md-2 mb-2">
                                    <label for="friend_mobile_1" class="form-label label-heading">मित्र मोबाइल </label>
                                    <input type="number" name="friend_mobile_1" class="form-control"
                                        id="friend_mobile_1" pattern="[1-9]{1}[0-9]{9}" minlength="10" maxlength="10">
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
                                        value="">
                                </div>

                                <div class="col-md-2 mb-2">
                                    <label class="form-label label-heading">कार</label>
                                    <input type="text" class="form-control" name="vehicle1" id="vehicle1"
                                        value="">
                                </div>

                                <div class="col-md-2 mb-2">
                                    <label class="form-label label-heading">ट्रेक्टर</label>
                                    <input type="text" class="form-control" name="vehicle2" id="vehicle2"
                                        value="">
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
                                        <option value="{{ $division->division_id }}">{{ $division->division_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-2 mb-2">
                                <label for="district" class="form-label label-heading required">जिले का नाम <span
                                        class="error">*</span></label>
                                <select name="district" id="district" required class="form-control">
                                </select>
                            </div>

                            <div class="col-md-2 mb-2">
                                <label for="loksabha" class="form-label label-heading required">लोकसभा <span
                                        class="error">*</span></label>
                                <select name="loksabha" id="loksabha" required class="form-control">
                                </select>
                            </div>

                            <div class="col-md-2 mb-2">
                                <label for="vidhansabha" class="form-label label-heading required">विधानसभा नाम/क्रमांक
                                    <span class="error">*</span>
                                </label>
                                <select name="vidhansabha" id="vidhansabha" required class="form-control">
                                </select>
                            </div>

                            <div class="col-md-2 mb-2">
                                <label for="mandal" class="form-label label-heading">मंडल का नाम <span
                                        class="error">*</span></label>
                                <select name="mandal" id="mandal" disabled class="form-control">
                                </select>
                            </div>

                            <div class="col-md-2 mb-2">
                                <label for="mandal_type" class="form-label label-heading">मंडल का प्रकार </label>
                                <select name="mandal_type" id="mandal_type" class="form-control">
                                    <option value=''>--चुनें--</option>
                                    <option value="1">ग्रामीण मंडल</option>
                                    <option value="2">नगर मंडल</option>
                                </select>
                            </div>

                            <div class="col-md-2 mb-2">
                                <label for="nagar" class="form-label label-heading">कमांड एरिया <span
                                        class="error">*</span></label>
                                <select name="nagar" id="nagar" disabled class="form-control">
                                </select>
                            </div>


                            <div class="col-md-2 mb-2">
                                <label for="matdan_kendra_name" class="form-label label-heading">मतदान केंद्र/क्रमांक
                                    <span class="error">*</span>
                                </label>
                                <select name="matdan_kendra_name" class="form-control" id="matdan_kendra_name" disabled>
                                </select>
                                <input type="hidden" name="matdan_kendra_no" id="matdan_kendra_no" value="">
                            </div>


                            <div class="col-md-2 mb-2">
                                <label for="area" class="form-label label-heading">निवासी ग्राम/वार्ड चौपाल <span
                                        class="error">*</span></label>
                                <select name="area_name" class="form-control" id="area_name" disabled>
                                </select>
                            </div>

                            <div class="col-md-3 mb-2">
                                <label for="permanent_address" class="form-label label-heading required">स्थाई पता <span
                                        class="error">*</span></label>
                                <textarea type="textarea" class="form-control" name="permanent_address" id="permanent_address" rows="2"
                                    required=""></textarea>
                            </div>


                            <div class="col-md-3 mb-2">
                                <label class="form-label label-heading d-flex justify-content-between align-items-center">
                                    अस्थाई पता
                                    <span class="d-flex align-items-center">
                                        स्थाई पता के समान&nbsp;
                                        <input type="checkbox" name="permanent_address_check"
                                            id="permanent_address_check">
                                    </span>
                                </label>
                                <textarea class="form-control" name="temp_address" id="temp_address" rows="2"></textarea>
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
                                <label for="voter_front" class="form-label label-heading">वोटर आई.डी. आगे का फोटो <span
                                        class="error">*</span></label>
                                <input type="file" accept="image/*" name="voter_front" id="voter_front"
                                    class="form-control file" required>
                                <div class="invalid-feedback text-danger" id="voter_front-error" style="display:none;">
                                </div>
                                <img id="voter_front_photo" src="#" alt="" width="210"
                                    style="padding-top:10px;" />
                            </div>

                            <div class="col-md-4 mb-2">
                                <label for="voter_back" class="form-label label-heading">वोटर आई.डी. पीछे का फोटो <span
                                        class="error">*</span></label>
                                <input type="file" accept="image/*" name="voter_back" id="voter_back"
                                    class="form-control file" required>
                                <div class="invalid-feedback text-danger" id="voter_back-error" style="display:none;">
                                </div>

                                <img id="voter_back_photo" src="#" alt="" width="210"
                                    style="padding-top:10px;" />
                            </div>

                            <div class="col-md-4 mb-2">
                                <label for="photo" class="form-label label-heading required">संकल्प कर्ता का फोटो <span
                                        class="error">*</span>
                                </label>
                                <input type="file" accept="image/*" class="form-control file" id="photo"
                                    name="file" required />
                                <div class="invalid-feedback text-danger" id="photo-error" style="display:none;"></div>
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
                                <textarea name="reason_join" id="reason_join" placeholder="" rows="3" class="form-control"> </textarea>
                            </div>

                            <div class="col-lg-12">
                                <lable class="form-label label-heading required"></label>
                                    <input type="checkbox" id="final_check"
                                        style="width: 14px;display: initial;height:14px;" class="form-control" required>
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
                            सबमिट करें
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


                function validateImage(inputId, errorId) {
                    const fileInput = $(inputId);
                    const errorDiv = $(errorId);
                    const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png'];
                    const maxSize = 2 * 1024 * 1024; // 2MB

                    fileInput.on('change', function() {
                        const file = this.files[0];

                        if (file) {
                            if (!allowedTypes.includes(file.type)) {
                                errorDiv.text('इमेज केवल (JPG, PNG) अपलोड करें।').show();
                                fileInput.addClass('is-invalid');
                                this.value = '';
                            } else if (file.size > maxSize) {
                                errorDiv.text('फाइल का आकार 2MB से अधिक नहीं होना चाहिए।').show();
                                fileInput.addClass('is-invalid');
                                this.value = '';
                            } else {
                                errorDiv.hide();
                                fileInput.removeClass('is-invalid');
                            }
                        } else {
                            errorDiv.hide();
                            fileInput.removeClass('is-invalid');
                        }
                    });
                }

                validateImage('#photo', '#photo-error');
                validateImage('#voter_front', '#voter_front-error');
                validateImage('#voter_back', '#voter_back-error');


                $('#mobile_1').on('input', function() {
                    const value = $(this).val();
                    const errorDiv = $('#mobile_1-error');
                    const mobilePattern = /^[1-9][0-9]{9}$/;

                    if (!mobilePattern.test(value)) {
                        errorDiv.text('मोबाइल नंबर 10 अंकों का होना चाहिए।')
                            .show();
                        $(this).addClass('is-invalid');
                    } else {
                        errorDiv.hide();
                        $(this).removeClass('is-invalid');
                    }
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
                    const pollingNo = selected.data('polling-no');
                    const pollingName = selected.data('polling-name');

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
