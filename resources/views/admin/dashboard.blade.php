@php
$pageTitle = 'सदस्यता फाॅर्म डेटा';
$breadcrumbs = [
'एडमिन' => '#',
'सदस्यता फाॅर्म डेटा' => '#'
];
@endphp

@extends('layouts.app')
@section('title', 'Admin Dashboard')

@section('content')
<div class="container">

    <div class="row page-titles mx-0">
        <div class="row">
            <div class="col-md-2">
                <div style="display: flex; align-items: center; gap: 10px;">
                    <label style="white-space: nowrap;">सदस्यता आई डी/मोबाइल</label>
                </div>
                <input type="text" id="main_mobile" class="form-control" />
            </div>

            <div class="col-md-2">
                <label>सदस्य का नाम</label>
                <input type="text" id="name" class="form-control">
            </div>

            <div class="col-md-2">
                <label>लिंग</label>
                <select id="gender" class="form-control select2">
                    <option value="">--चुनें--</option>
                    <option value="पुरुष">पुरुष</option>
                    <option value="स्त्री">स्त्री</option>
                    <option value="अन्य">अन्य</option>
                </select>
            </div>

            <div class="col-md-2">
                <label>श्रेणी</label>
                <select id="category" class="form-control select2">
                    <option value="">--चुनें--</option>
                    <option value="सामान्य">सामान्य</option>
                    <option value="पिछड़ा वर्ग">पिछड़ा वर्ग</option>
                    <option value="अनुसूचित जाति">अनुसूचित जाति</option>
                    <option value="अनुसूचित जनजाति">अनुसूचित जनजाति</option>
                    <option value="अन्य">अन्य</option>
                </select>
            </div>

            <div class="col-md-2">
                <label>व्यवसाय</label>
                <select id="business" class="form-control select2">
                    <option value="">--चुनें--</option>
                    <option value="शासकीय नौकरी">शासकीय नौकरी</option>
                    <option value="अशासकीय नौकरी">अशासकीय नौकरी</option>
                    <option value="बिजनेस">बिजनेस</option>
                    <option value="कृषि">कृषि</option>
                    <option value="बेरोजगार">बेरोजगार</option>
                    <option value="गृहिणी">गृहिणी</option>
                    <option value="विद्यार्थी">विद्यार्थी</option>
                    <option value="स्वरोजगार">स्वरोजगार</option>
                    <option value="समाजसेवा">समाजसेवा</option>
                    <option value="पत्रकार">पत्रकार</option>
                    <option value="अन्य">अन्य</option>
                </select>
            </div>

            <div class="col-md-2">
                <label>जिला</label>
                <select id="district" class="form-control select2">
                    <option value="">--चुनें--</option>
                    @foreach($districts as $district)
                    <option value="{{ $district->district_id }}">{{ $district->district_name }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="row mt-2">
            <div class="col-md-2">
                <label>विधानसभा का नाम<span class="required text-danger ml-1">*</span></label>
                <select id="txtvidhansabha" class="form-control select2">
                    <option value="">--चुनें--</option>
                </select>
            </div>

            <div class="col-md-2">
                <label>मंडल का नाम<span class="required text-danger ml-1">*</span></label>
                <select id="mandal" class="form-control">
                    <option value="">--चुनें--</option>
                </select>
            </div>

            <div class="col-md-2">
                <label>जाति<span class="required text-danger ml-1">*</span></label>
                <select id="txtjati" class="form-control select2">
                    <option value="">--चुनें--</option>
                    @foreach($jatis as $jati)
                    <option value="{{ $jati->jati }}">{{ $jati->jati }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-2">
                <label>धर्म<span class="required text-danger ml-1">*</span></label>
                <select id="religion" class="form-control select2">
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

            <div class="col-md-2">
                <div class="row">
                    <div class="col-md-6">
                        <label>आयु से</label>
                        <input type="text" id="from_age" class="form-control">
                    </div>
                    <div class="col-md-6">
                        <label>आयु तक</label>
                        <input type="text" id="to_age" class="form-control">
                    </div>
                </div>
            </div>

            <div class="col-md-2">
                <label>शैक्षणिक योग्यता<span class="required text-danger ml-1">*</span></label>
                <select id="education" class="form-control select2">
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
        </div>

        <div class="row mt-2">
            <div class="col-md-2">
                <label>राजनीतिक/सामाजिक सक्रियता<span class="required text-danger ml-1">*</span></label>
                <select id="party_name" class="form-control select2">
                    <option value="">--चुनें--</option>
                    <option value="भाजपा">भाजपा</option>
                    <option value="कांग्रेस">कांग्रेस</option>
                    <option value="बीएसपी">बीएसपी</option>
                    <option value="आरएसएस">आरएसएस</option>
                    <option value="बीजेएस">बीजेएस</option>
                    <option value="कोई नहीं">कोई नहीं</option>
                </select>
            </div>

            <div class="col-md-2">
                <label>बी.जे.एस सदस्यता<span class="required text-danger ml-1">*</span></label>
                <select id="membership" class="form-control select2">
                    <option value="">--चुनें--</option>
                    <option>समर्पित कार्यकर्ता</option>
                    <option>सक्रिय कार्यकर्ता</option>
                    <option>साधारण कार्यकर्ता</option>
                </select>
            </div>

            <div class="col-md-2">
                <label>रुचि<span class="required text-danger ml-1">*</span></label>
                <select id="interest_area" class="form-control select2">
                    <option value="">--चुनें--</option>
                    <option value="कृषि">कृषि</option>
                    <option value="समाजसेवा">समाजसेवा</option>
                    <option value="राजनीति">राजनीति</option>
                    <option value="पर्यावरण">पर्यावरण</option>
                    <option value="शिक्षा">शिक्षा</option>
                    <option value="योग">योग</option>
                    <option value="स्वास्थ्य">स्वास्थ्य</option>
                    <option value="स्वच्छता">स्वच्छता</option>
                    <option value="साधना">साधना</option>
                </select>
            </div>

            <div class="col-md-2">
                <label>परिवार में कुल सदस्य<span class="required text-danger ml-1">*</span></label>
                <select id="family_member" class="form-control select2">
                    <option value="">--चुनें--</option>
                    <option value="0 AND 5">0-5</option>
                    <option value="5 AND 10">5-10</option>
                    <option value="10 AND 20">10-20</option>
                </select>
            </div>

            <div class="col-md-2">
                <label>वाहन<span class="required text-danger ml-1">*</span></label>
                <select id="vehicle" class="form-control select2">
                    <option value="">--चुनें--</option>
                    <option value="vehicle1">मोटरसाइकिल</option>
                    <option value="vehicle2">कार</option>
                    <option value="vehicle3">ट्रेक्टर</option>
                </select>
            </div>

            <div class="col-md-2">
                <label>व्हाट्सएप नंबर<span class="required text-danger ml-1">*</span></label>
                <select id="whatsapp" class="form-control select2">
                    <option value="">--चुनें--</option>
                    <option value="1">Yes</option>
                    <option value="0">No</option>
                </select>
            </div>
        </div>

        <div class="row mt-2">
            <div class="col-md-12 mt-3" style="color:rgb(55, 64, 75)">
                <button class="btn btn-success mr-4" id="data-filter">Filter Data</button>
                Filter Data Count: <span id="total">0</span>
            </div>
        </div>
    </div>


    <div class="row">
        <div class="col-md-12">
            <div class="card" id="table_card" style="display: none;">
                <div class="card-body">

                    @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    @endif

                    @if(session('update_msg'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('update_msg') }}
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    @endif

                    @if(session('delete_msg'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        {{ session('delete_msg') }}
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    @endif

                    <form method="post" action="{{ route('dashboard.download') }}" class="mb-5">
                        @csrf
                        <input type="hidden" name="download_data_whr" id="download_data_whr" value="">
                        <button type="submit" name="download" class="btn btn-danger pull-right">
                            <i class="fa fa-download"></i> Download Filter Data
                        </button>
                    </form>

                    <div id="filtered_data" class="table-responsive">

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>



@push('scripts')
<script>
    $("#data-filter").click(function() {
        $("#loader-wrapper").show();

        let where = [];
        const getVal = id => $(`#${id}`).val();

        if (getVal("main_mobile")) where.push(`reg.member_id = '${getVal("main_mobile")}'`);
        if (getVal("name")) where.push(`reg.name = '${getVal("name")}'`);
        if (getVal("gender")) where.push(`reg.gender = '${getVal("gender")}'`);
        if (getVal("category")) where.push(`reg.caste = '${getVal("category")}'`);
        if (getVal("business")) where.push(`reg.business = '${getVal("business")}'`);
        if (getVal("district")) where.push(`st.district = '${getVal("district")}'`);
        if (getVal("txtvidhansabha")) where.push(`st.vidhansabha = '${getVal("txtvidhansabha")}'`);
        if (getVal("mandal")) where.push(`st.mandal = '${getVal("mandal")}'`);
        if (getVal("txtjati")) where.push(`reg.jati = '${getVal("txtjati")}'`);
        if (getVal("religion")) where.push(`reg.religion = '${getVal("religion")}'`);
        if (getVal("from_age") && getVal("to_age")) where.push(`reg.age BETWEEN ${getVal("from_age")} AND ${getVal("to_age")}`);
        if (getVal("education")) where.push(`reg.education = '${getVal("education")}'`);
        if (getVal("party_name")) where.push(`st4.party_name = '${getVal("party_name")}'`);
        if (getVal("membership")) where.push(`reg.membership = '${getVal("membership")}'`);
        if (getVal("interest_area")) where.push(`st3.intrest = '${getVal("interest_area")}'`);

        if (getVal("family_member")) {
            let range = getVal("family_member").split(" AND ");
            if (range.length === 2) {
                where.push(`st3.total_member BETWEEN ${range[0]} AND ${range[1]}`);
            }
        }

        if (getVal("vehicle")) where.push(`st3.${getVal("vehicle")} > 0`);
        if (getVal("whatsapp") !== "") where.push(`reg.mobile1_whatsapp = '${getVal("whatsapp")}'`);

        let whereStr = where.join(" AND ");
        $("#download_data_whr").val(whereStr);

        $.ajax({
            url: "{{ route('dashboard.filter') }}",
            type: "POST",
            data: {
                _token: "{{ csrf_token() }}",
                main_mobile: $("#main_mobile").val(),
                name: $("#name").val(),
                gender: $("#gender").val(),
                category: $("#category").val(),
                business: $("#business").val(),
                district: $("#district").val(),
                txtvidhansabha: $("#txtvidhansabha").val(),
                mandal: $("#mandal").val(),
                txtjati: $("#txtjati").val(),
                religion: $("#religion").val(),
                from_age: $("#from_age").val(),
                to_age: $("#to_age").val(),
                education: $("#education").val(),
                party_name: $("#party_name").val(),
                membership: $("#membership").val(),
                interest_area: $("#interest_area").val(),
                family_member: $("#family_member").val(),
                vehicle: $("#vehicle").val(),
                whatsapp: $("#whatsapp").val()
            },
            success: function(response) {
                if (response.count > 0) {
                    $("#filtered_data").html(response.html);
                    $('#example').DataTable({
                        destroy: true,
                        responsive: true
                    });
                    $("#table_card").show();
                } else {
                    $("#filtered_data").html('<div class="text-danger">No data found.</div>');
                    $("#table_card").show();
                }

                $("#total").text(response.count);
                $("#loader-wrapper").hide();
            },
            error: function() {
                $("#loader-wrapper").hide();
            }
        });
    });
</script>
@endpush
@endsection