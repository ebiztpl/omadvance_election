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
                                    <label>आपको बी.जे.एस संगठन से जोड़ने वाले साथी का मोबाइल नंबर/संकल्प पत्र क्र. (अगर कोई
                                        है तो)</label>
                                    <input type="text" class="form-control" name="member_id_post" id="member_id_post">
                                </div>

                                <input type="hidden" id="reference_id" name="reference_id" />
                            </div>

                            <div class="col-md-6">
                                <div class="text">
                                    <label>जोड़ने वाले साथी का नाम </label>
                                    <input type="text" class="form-control" disabled name="member_post_name"
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


                        <div class="row mt-2">
                            <div class="col-md-3">
                                <label for="first_name" class="form-label">संकल्प पत्रकर्ता का नाम</label>
                                <input type="text" class="form-control" name="name" id="name" required>
                            </div>


                            <div class="col-md-3">
                                <label for="first_name" class="form-label">पिता/पति का नाम</label>
                                <input type="text" class="form-control" name="father_name" id="father_name" required>
                            </div>

                            <div class="col-md-3">
                                <label for="caste" class="form-label">जाति </label>
                                <input type="text" class="form-control" name="jati" id="jati" required />
                            </div>

                            <div class="col-md-3">
                                <label for="caste" class="form-label">श्रेणी </label>
                                <select name="caste" id="caste" class="form-control" required>
                                    <option value="">--चुनें--</option>
                                    <option value="सामान्य">सामान्य</option>
                                    <option value="पिछड़ा वर्ग">पिछड़ा वर्ग</option>
                                    <option value="अनुसूचित जाति">अनुसूचित जाति</option>
                                    <option value="अनुसूचित जनजाति">अनुसूचित जनजाति</option>
                                    <option value="अन्य">अन्य</option>
                                </select>
                            </div>
                        </div>


                        <div class="row mt-2">
                            <div class="col-md-3">
                                <label for="religion" class="form-label">धर्म </label>
                                <select name="religion" id="religion" class="form-control" required>
                                    <option value="">--चुनें--</option>
                                    <option value="हिंदू">हिंदू</option>
                                    <option value="ईसाई">ईसाई</option>
                                    <option value="मुसलमान">मुसलमान</option>
                                    <option value="सिख">सिख</option>
                                    <option value="जैन">जैन</option>
                                    <option value="बौद्ध">बौद्ध</option>
                                    <option value="यहूदी">यहूदी</option>
                                    <option value="पारसी">पारसी</option>
                                    <option value="अन्य">अन्य</option>
                                </select>
                            </div>

                            <div class="col-md-3">
                                <label for="dob" class="form-label">जन्म दिनांक</label>
                                <input type="date" id="date" name="date" class="form-control" value="2026-12-31"
                                    required>
                            </div>

                            <div class="col-md-3">
                                <label for="age" class="form-label">आयु </label>
                                <input type="text" name="age" id="age" class="form-control" required>
                            </div>

                            <div class="col-md-3">
                                <label for="gender" class="form-label">लिंग</label>
                                <select name="gender" id="gender" class="form-control" required>
                                    <option value="">--चुनें--</option>
                                    <option value="पुरुष">पुरुष</option>
                                    <option value="स्त्री">स्त्री</option>
                                    <option value="अन्य">अन्य</option>
                                </select>
                            </div>
                        </div>


                        <div class="row mt-2">
                            <div class="col-md-3">
                                <label for="mobile_1" class="form-label">मोबाइल 1</label>
                                <span id="msg"></span>
                                <input type="number" name="mobile_1" class="form-control" id="mobile_1"
                                    pattern="[1-9]{1}[0-9]{9}" minlength="10" maxlength="10" required autocomplete="">
                            </div>

                            <div class="col-md-3">
                                <label for="" class="form-label">क्या ये व्हाट्सएप नंबर है? </label>
                                <input type="checkbox" name="mobile_1_whataspp" class="form-control"
                                    id="mobile_1_whataspp" value="1" style="width: 50px;display: initial;">
                            </div>


                            <div class="col-md-3">
                                <label for="mobile_2" class="form-label">मोबाइल 2</label>
                                <input type="number" name="mobile_2" class="form-control" id="mobile_2"
                                    pattern="[1-9]{1}[0-9]{9}" minlength="10" maxlength="10">
                            </div>


                            <div class="col-md-3">
                                <label for="" class="form-label">क्या ये व्हाट्सएप नंबर है? </label>
                                <input type="checkbox" name="mobile_2_whataspp" id="mobile_2_whataspp"
                                    class="form-control" value="2" style="width: 50px;display: initial;">
                            </div>
                        </div>


                        <div class="row mt-2">

                            <div class="col-md-3">
                                <label for="email" class="form-label">ईमेल आईडी </label>

                                <input type="email" name="email" class="form-control" id="email">


                            </div>

                            <div class="col-md-3">
                                <label for="education" class="form-label ">शैक्षणिक योग्यता </label>
                                <select name="education" id="education" class="form-control" required>
                                    <option value="">--चुनें--</option>
                                    <option value="साक्षर/निरक्षर">साक्षर/निरक्षर</option>
                                    <option value="प्राथमिक शिक्षा">प्राथमिक शिक्षा</option>
                                    <option value="माध्यमिक शिक्षा">माध्यमिक शिक्षा</option>
                                    <option value="10th">10th</option>
                                    <option value="12th">12th</option>
                                    <option value="स्नातक">स्नातक</option>
                                    <option value="स्नातकोत्तर">स्नातकोत्तर</option>
                                    <option value="डॉक्टरेट या उच्चतर">डॉक्टरेट या उच्चतर</option>
                                    <option value="डिप्लोमा">डिप्लोमा</option>
                                    <option value="अन्य">अन्य</option>
                                </select>

                            </div>

                            <div class="col-md-3">
                                <label for="business" class="form-label required">व्यवसाय </label>

                                <select name="business" id="business" class="form-control" required>
                                    <option value="">--चुनें--</option>
                                    <option vlaue="शासकीय नौकरी">शासकीय नौकरी</option>
                                    <option value="अशासकीय नौकरी">अशासकीय नौकरी</option>
                                    <option value="बिजनेस">बिजनेस</option>
                                    <option value="कृषि">कृषि</option>
                                    <option value="बेरोजगार">बेरोजगार</option>
                                    <option value="गृहिणी">गृहिणी</option>
                                    <option value="विद्यार्थी">विद्यार्थी</option>
                                    <option value="स्वरोजगार">स्वरोजगार</option>
                                    <option value="समाजसेवा">समाजसेवा</option>
                                    <option value="पत्रकार">पत्रकार</option>
                                    <option value="मजदूरी">मजदूरी</option>
                                    <option value="अन्य">अन्य</option>
                                </select>

                            </div>

                            <div class="col-md-3">

                                <label class="form-label required">बी.जे.एस सदस्यता</label>

                                <select name="membership" class="form-control" id="membership">
                                    <option value="">--Select--</option>
                                    <option>समर्पित कार्यकर्ता</option>
                                    <option>सक्रिय कार्यकर्ता</option>
                                    <option>साधारण कार्यकर्ता</option>
                                </select>


                            </div>




                            <div class="col-lg-3 col-md-3 col-12" style="display: none;">
                                <label for="position" class="form-label">व्यवसायिक पद </label>
                                <div class="form-select">
                                    <input type="text" name="position" id="position" class="form-control">

                                </div>
                            </div>






                        </div>

                        <div class="row mt-2">
                            <div class="col-md-3">

                                <label class="form-label ">राजनीतिक/सामाजिक सक्रियता </label>
                                <select name="party_name" id="party_name" class="form-control" required>
                                    <option value="">--Select--</option>
                                    <option value="भाजपा">भाजपा</option>
                                    <option value="कांग्रेस">कांग्रेस</option>
                                    <option value="बीएसपी">बीएसपी</option>
                                    <option value="आरएसएस">आरएसएस</option>
                                    <option value="बीजेएस">बीजेएस</option>
                                    <option value="कोई नहीं">कोई नहीं</option>
                                </select>

                            </div>

                            <div class="col-md-3">

                                <label class="form-label">पद वर्तमान/भूतपूर्व </label>
                                <input type="text" name="present_post" class="form-control" id="present_post"
                                    placeholder="">

                            </div>
                        </div>

                        <fieldset>
                            <div
                                class="step-header bg-dark text-white p-3 rounded d-flex justify-content-between align-items-center mb-3 mt-3">
                                <h5 class="mb-0 text-white">रुचि *</h5>
                            </div>

                            <div class="row mt-2">
                                <div class="col-md-2">
                                    <input type="checkbox" name="interest[]"
                                        style="width: auto;display: initial;height:18px;" value="कृषि"
                                        class="form-control"> कृषि
                                </div>

                                <div class="col-md-2">
                                    <input type="checkbox" name="interest[]"
                                        style="width: auto;display: initial;height:18px;" value="समाजसेवा"
                                        class="form-control"> समाजसेवा
                                </div>

                                <div class="col-md-2">
                                    <input type="checkbox" name="interest[]"
                                        style="width: auto;display: initial;height:18px;" value="राजनीति"
                                        class="form-control"> राजनीति
                                </div>

                                <div class="col-md-2">
                                    <input type="checkbox" name="interest[]"
                                        style="width: auto;display: initial;height:18px;" value="पर्यावरण"
                                        class="form-control"> पर्यावरण
                                </div>

                                <div class="col-md-2">
                                    <input type="checkbox" name="interest[]"
                                        style="width: auto;display: initial;height:18px;" value="शिक्षा"
                                        class="form-control"> शिक्षा
                                </div>

                                <div class="col-md-2">
                                    <input type="checkbox" name="interest[]"
                                        style="width: auto;display: initial;height:18px;" value="योग"
                                        class="form-control"> योग
                                </div>

                                <div class="col-md-2">
                                    <input type="checkbox" name="interest[]"
                                        style="width: auto;display: initial;height:18px;" value="स्वास्थ्य"
                                        class="form-control"> स्वास्थ्य
                                </div>


                                <div class="col-md-2">

                                    <input type="checkbox" name="interest[]"
                                        style="width: auto;display: initial;height:18px;" value="स्वच्छता"
                                        class="form-control"> स्वच्छता

                                </div>

                                <div class="col-lg-2 col-md-6 col-4" style="display:none;">
                                    <div class="form-group">
                                        <input type="checkbox" name="interest[]"
                                            style="width: auto;display: initial;height:18px;" value="साधना"
                                            class="form-control"> साधना
                                    </div>
                                </div>
                            </div>
                        </fieldset>

                        <div class="row mt-2">
                            <div class="col-md-3">
                                <label for="total_member" class="form-label">परिवार में कुल सदस्य </label>
                                <input type="text" name="total_member" id="total_member" class="form-control"
                                    placeholder="" required>
                            </div>
                            <div class="col-md-3">
                                <label for="total_voter" class="form-label ">परिवार में कुल मतदाता </label>
                                <input type="text" name="total_voter" id="total_voter" class="form-control"
                                    placeholder="" required>
                            </div>

                            <div class="col-md-3" style="display:none;">
                                <label for="member_job" class="form-label">शासकीय/अशासकीय सेवा में सदस्य </label>
                                <input type="text" name="member_job" id="member_job" class="form-control"
                                    placeholder="">
                            </div>
                        </div>


                        <fieldset>
                            <div
                                class="step-header bg-dark text-white p-3 rounded d-flex justify-content-between align-items-center mb-3 mt-3">
                                <h5 class="mb-0 text-white">परिवार के सदस्य/मित्र/पड़ोसी</h5>
                            </div>

                            <div class="row mt-2">
                                <div class="col-md-6">
                                    <label for="member_name_1" class="form-label">नाम</label>

                                    <input type="text" name="member_name_1" class="form-control" id="member_name_1"
                                        required>

                                </div>

                                <div class="col-md-6">
                                    <label for="member_mobile_1" class="form-label">मोबाइल </label>
                                    <input type="number" name="member_mobile_1" class="form-control"
                                        id="member_mobile_1" pattern="[1-9]{1}[0-9]{9}" minlength="10" maxlength="10"
                                        required>
                                </div>
                            </div>
                        </fieldset>

                        <fieldset>
                            <div
                                class="step-header bg-dark text-white p-3 rounded d-flex justify-content-between align-items-center mb-3 mt-3">
                                <h5 class="mb-0 text-white">घर में वाहनो की संख्या ?</h5>
                            </div>

                            <div class="row mt-2">
                                <div class="col-md-2">
                                    <label class="form-label">मोटरसाइकिल</label>
                                    <input type="text" name="vehicle3" class="form-control" id="vehicle3"
                                        value="">
                                </div>

                                <div class="col-md-2">
                                    <label class="form-label">कार</label>
                                    <input type="text" class="form-control" name="vehicle1" id="vehicle1"
                                        value="">
                                </div>

                                <div class="col-md-2">
                                    <label class="form-label">ट्रेक्टर</label>
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
                                    <label for="friend_name_1" class="form-label">नाम</label>
                                    <div class="form-group">
                                        <input type="text" class="form-control" name="friend_name_1"
                                            id="friend_name_1">
                                    </div>
                                </div>
                                <div class="col-lg-6 col-md-6 col-12">
                                    <label for="friend_mobile_1" class="form-label">मोबाइल </label>
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
                            class="step-header bg-dark text-white p-3 rounded d-flex justify-content-between align-items-center mt-2 mb-3">
                            <h5 class="mb-0 text-white">भाग (बी)</h5>
                            <span class="step-number badge bg-light text-dark fs-6">Step 2 / 2</span>
                        </div>

                        <div class="row mt-2">
                            <div class="col-md-3">
                                <label for="division_name" class="form-label required">संभाग का नाम</label>
                                <select name="division_name" class="form-control" required>
                                    <option value="">--Select Division--</option>
                                    @foreach ($divisions as $division)
                                        <option value="{{ $division->division_id }}">{{ $division->division_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-3">
                                <label for="district" class="form-label required">जिले का नाम</label>
                                <select name="district" id="district" required class="form-control">
                                </select>
                            </div>

                            <div class="col-md-3">
                                <label for="loksabha" class="form-label required">लोकसभा </label>
                                <select name="loksabha" id="loksabha" required class="form-control">
                                </select>
                            </div>

                            <div class="col-md-3">
                                <label for="vidhansabha" class="form-label required">विधानसभा का नाम/क्रमांक </label>
                                <select name="vidhansabha" id="vidhansabha" required class="form-control">
                                </select>
                            </div>
                        </div>

                        <div class="row mt-2">

                            <div class="col-md-3">
                                <label for="mandal" class="form-label">मंडल का नाम </label>
                                <select name="mandal" id="mandal" disabled class="form-control">
                                </select>
                            </div>

                            <div class="col-md-3">
                                <label for="mandal_type" class="form-label">मंडल का प्रकार</label>
                                <select name="mandal_type" id="mandal_type" class="form-control">
                                    <option value=''>--चुनें--</option>
                                    <option value="1">ग्रामीण मंडल</option>
                                    <option value="2">नगर मंडल</option>
                                </select>
                            </div>

                            <div class="col-md-3">
                                <label for="nagar" class="form-label">कमांड एरिया </label>
                                <select name="nagar" id="nagar" disabled class="form-control">
                                </select>
                            </div>


                            <div class="col-md-3">
                                <label for="matdan_kendra_name" class="form-label">मतदान केंद्र का नाम/क्रमांक
                                </label>
                                <select name="matdan_kendra_name" class="form-control" id="matdan_kendra_name" disabled>
                                </select>
                            </div>


                            <div class="col-md-3 mt-2">
                                <label for="area" class="form-label">निवासी ग्राम/वार्ड चौपाल का
                                    नाम</label>
                                <select name="area_name" class="form-control" id="area_name" disabled>

                                </select>
                            </div>
                        </div>



                        <div class="row mt-2">
                            <div class="col-lg-6 col-md-6 col-12">
                                <label for="permanent_address" class="form-label required">स्थाई पता</label>
                                <textarea type="textarea" class="form-control" name="permanent_address" id="permanent_address" rows="3"
                                    required=""></textarea>
                            </div>


                            <div class="col-lg-6 col-md-6 col-12">
                                <label class="form-label">अस्थाई पता <span style="float:right;">स्थाई पता के समान
                                        &nbsp;<input type="checkbox" name="permanent_address_check"
                                            id="permanent_address_check"
                                            style="width: auto;display: initial;height:18px;" /></span></label>
                                <textarea type="textarea" class="form-control" name="temp_address" id="temp_address" rows="3"></textarea>
                            </div>
                        </div>


                        <div class="row mt-2">
                            <div class="col-lg-4 col-md-4 col-12" style="display: none;">
                                <div class="form-group">
                                    <label for="matdan_kendra_name" class="form-label">पिनकोड नंबर</label>
                                    <input type="text" name="pincode" class="form-control" />
                                </div>
                            </div>

                            <div class="col-lg-4 col-md-4 col-12" style="display: none;">
                                <label for="member_job" class="form-label">परिवार की समग्र आई.डी. नंबर </label>
                                <div class="form-group">

                                    <input type="text" name="samagra_id" id="samagra_id" placeholder=""
                                        class="form-control">
                                </div>
                            </div>

                            <div class="col-lg-4 col-md-4 col-12" style="display: none;">
                                <div class="form-group">
                                    <label for="voter_number" class="form-label">वोटर आई.डी. नंबर</label>
                                    <input type="text" name="voter_number" class="file" id="voter_number"
                                        class="form-control">
                                </div>
                            </div>
                        </div>

                        <div class="row mt-2">
                            <div class="col-lg-4 col-md-4 col-12">
                                <label for="voter_front" class="form-label">वोटर आई.डी. आगे का फोटो</label>
                                <input type="file" name="voter_front" id="voter_front" class="form-control file">
                                <img id="voter_front_photo" src="#" alt="" width="210"
                                    style="padding-top:10px;" />
                            </div>

                            <div class="col-lg-4 col-md-4 col-12">
                                <label for="voter_back" class="form-label">वोटर आई.डी. पीछे का फोटो</label>
                                <input type="file" name="voter_back" id="voter_back" class="form-control file">
                                <img id="voter_back_photo" src="#" alt="" width="210"
                                    style="padding-top:10px;" />
                            </div>

                            <div class="col-lg-4 col-md-4 col-12">
                                <label for="photo" class="form-label required">संकल्प कर्ता का फोटो </label>
                                <input type="file" accept="" class="form-control file" id="photo"
                                    name="file" required />
                            </div>

                            {{-- <div class="col-lg-3 col-md-3 col-12">
                                <div style="background-image:url('img/back_side.png'); height: 574px; width: 266px;"
                                    id="preview_photo_back"><img id="preview_photo" src="#" alt=""
                                        style="margin-top: 144px; margin-left: 65px; width: 132px; height: 201px;" />
                                </div>
                            </div> --}}

                        </div>


                        <div class="row mt-2">
                            <div class="col-lg-12 col-md-12 col-12">
                                <label class="form-label">सदस्यता का कारण/उदेश्य : आप बीजेएस के सदस्य क्यों बन रहे हैं
                                </label>
                                <textarea name="reason_join" id="reason_join" placeholder="" rows="3" class="form-control"> </textarea>
                            </div>

                            <div class="col-lg-12">
                                <lable class="form-label required"></label>
                                    <input type="checkbox" id="final_check"
                                        style="width: 14px;display: initial;height:14px;" checked class="form-control">
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
                            Submit
                        </button>
                    </div>

                </form>
            </div>
        </div>
    </div>


    @push('scripts')
        <script>
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
                                    '<option value="">--Select District--</option>');
                                $.each(data, function(key, district) {
                                    districtSelect.append('<option value="' + district
                                        .district_id + '">' + district.district_name +
                                        '</option>');
                                });

                                // Reset loksabha and vidhansabha
                                $('#loksabha').html(
                                    '<option value="">--Select Loksabha--</option>');
                                $('#vidhansabha').html(
                                    '<option value="">--Select Vidhansabha--</option>');
                            },
                            error: function() {
                                alert('Error loading districts');
                            }
                        });
                    } else {
                        $('select[name="district"]').html('<option value="">--Select District--</option>');
                        $('#loksabha').html('<option value="">--Select Loksabha--</option>');
                        $('#vidhansabha').html('<option value="">--Select Vidhansabha--</option>');
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
                                    '<option value="">--Select Vidhansabha--</option>');
                                $('#loksabha').empty().append(
                                    '<option value="">--Select Loksabha--</option>');

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
                        $('#vidhansabha').html('<option value="">--Select Vidhansabha--</option>');
                        $('#loksabha').html('<option value="">--Select Loksabha--</option>');
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
                                $mandal.append('<option value="">--Select Mandal--</option>');

                                mandals.forEach(function(mandal) {
                                    $mandal.append('<option value="' + mandal.mandal_id +
                                        '">' + mandal.mandal_name + '</option>');
                                });

                                $mandal.prop('disabled', false);
                            },
                            error: function() {
                                alert('Error fetching Mandal');
                            }
                        });
                    } else {
                        $('#mandal').empty().append('<option value="">--Select Mandal--</option>').prop(
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
                                    $nagar.append('<option value="' + nagar.nagar_id +
                                        '">' + nagar.nagar_name + '</option>');
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
                                    $polling.append('<option value="' + polling
                                        .gram_polling_id +
                                        '">' + label + '</option>');
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



                $('#permanent_address_check').on('change', function() {
                    if ($(this).is(':checked')) {
                        let permAddress = $('#permanent_address').val();
                        $('#temp_address').val(permAddress).prop('disabled', true);
                    } else {
                        $('#temp_address').val('').prop('disabled', false);
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
