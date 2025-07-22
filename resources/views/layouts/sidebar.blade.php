@php
    $role = session('logged_in_role');
@endphp
<style>
    .metismenu>li>a {
        color: white !important;
    }
</style>

<div class="quixnav">
    <div class="quixnav-scroll">
        <ul class="metismenu" id="menu">

            {{-- Admin Panel --}}
            @if ($role == 1)
                <li class="nav-label" style="color: #c0bebe">एडमिन टूल्स</li>
                <li><a href="{{ route('dashboard.index') }}"><i class="icon icon-arrow-right"></i><span
                            class="nav-text">सदस्यता फाॅर्म डेटा</span></a></li>

                <li><a href="{{ route('membership.create') }}"><i class="icon icon-arrow-right"></i><span
                            class="nav-text">सदस्यता फाॅर्म</span></a></li>

                <li><a href="{{ route('upload.index') }}"><i class="icon icon-arrow-right"></i><span
                            class="nav-text">मतदाता डेटा अपलोड</span></a></li>

                <li><a href="{{ route('viewvoter.index') }}"><i class="icon icon-arrow-right"></i><span
                            class="nav-text">मतदाता डेटा</span></a></li>

                <li><a href="{{ route('dashboard2.index') }}"><i class="icon icon-arrow-right"></i><span
                            class="nav-text">सदस्य द्वारा जोड़े गए सदस्य</span></a></li>

                <li><a href="{{ route('birthdays.index') }}"><i class="icon icon-arrow-right"></i><span
                            class="nav-text">सदस्य जन्मदिन</span></a></li>

                <li><a href="{{ route('complaints.index') }}"><i class="icon icon-arrow-right"></i><span
                            class="nav-text">समस्याएँ देखे</span></a></li>

                <li><a href="{{ route('responsibility.index') }}"><i class="icon icon-arrow-right"></i><span
                            class="nav-text">दायित्व नियुक्त करना</span></a></li>

                <li><a href="{{ route('view_responsibility.index') }}"><i class="icon icon-arrow-right"></i><span
                            class="nav-text">दायित्व कार्यकर्ता देखे</span></a></li>

                <li>
                    <a href="{{ route('user.create') }}">
                        <i class="icon icon-arrow-right"></i>
                        <span class="nav-text">मैनेजर/कार्यालय बनाएँ</span>
                    </a>
                </li>

                {{-- <li><a href="{{ route('generate.index') }}"><i class="icon icon-arrow-right"></i><span
                            class="nav-text">दायित्व कार्यकर्ता प्रिंट करे</span></a></li> --}}

                <li><a href="{{ route('change_password.index') }}"><i class="icon icon-arrow-right"></i><span
                            class="nav-text">पासवर्ड बदलें</span></a></li>
            @endif

            {{-- Manager Panel --}}
            @if ($role == 2)
                <li class="nav-label" style="color: #c0bebe">मैनेजर टूल्स</li>

                <li><a href="{{ route('division.index') }}"><i class=" icon icon-arrow-right"></i><span
                            class="nav-text">संभाग जोड़े</span></a></li>

                <li><a href="{{ route('city.master') }}"><i class="icon icon-arrow-right"></i><span
                            class=" nav-text">जिला जोड़े</span></a></li>

                <li><a href="{{ route('vidhansabha.index') }}"><i class="icon icon-arrow-right"></i><span
                            class="nav-text">विधानसभा/लोकसभा जोड़े</span></a></li>

                <li><a href="{{ route('mandal.index') }}"><i class="icon icon-arrow-right"></i><span
                            class="nav-text">मंडल जोड़े</span></a></li>

                <li><a href="{{ route('nagar.index') }}"><i class="icon icon-arrow-right"></i><span
                            class="nav-text">नगर केंद्र/ग्राम केंद्र जोड़े</span></a></li>

                <li><a href="{{ route('polling.index') }}"><i class="icon icon-arrow-right"></i><span
                            class="nav-text">मतदान केंद्र/क्रमांक जोड़े</span></a></li>

                <li><a href="{{ route('area.index') }}"><i class="icon icon-arrow-right"></i><span
                            class="nav-text">मतदान क्षेत्र जोड़े</span></a></li>

                <li><a href="{{ route('level.index') }}"><i class="icon icon-arrow-right"></i><span
                            class="nav-text">कार्य क्षेत्र</span></a></li>

                <li><a href="{{ route('positions.index') }}"><i class="icon icon-arrow-right"></i><span
                            class="nav-text">दायित्व</span></a></li>

                <li><a href="{{ route('jati.index') }}"><i class="icon icon-arrow-right"></i><span
                            class="nav-text">जाति मास्टर</span></a></li>

                <li><a href="{{ route('department.index') }}"><i class=" icon icon-arrow-right"></i><span
                            class="nav-text">विभाग जोड़े</span></a></li>

                            <li><a href="{{ route('designation.master') }}"><i class="icon icon-arrow-right"></i><span
                            class=" nav-text">पद जोड़े</span></a></li>

                             <li><a href="{{ route('adhikari.index') }}"><i class="icon icon-arrow-right"></i><span
                            class=" nav-text">अधिकारी जोड़ें</span></a></li>

                             <li><a href="{{ route('complaintSubject.master') }}"><i class="icon icon-arrow-right"></i><span
                            class=" nav-text">शिकायत का विषय जोड़े</span></a></li>

                            <li><a href="{{ route('complaintReply.index') }}"><i class="icon icon-arrow-right"></i><span
                            class=" nav-text">शिकायत का जवाब जोड़े</span></a></li>

                            

                <li><a href="{{ route('jati_polling.index') }}"><i class="icon icon-arrow-right"></i><span
                            class="nav-text">जातिगत मतदाता प्रविष्टि</span></a></li>

                <li><a href="{{ route('jatiwise.index') }}"><i class="icon icon-arrow-right"></i><span
                            class="nav-text">जातिगत मतदाता देखे</span></a></li>



                <li><a class="has-arrow" href="#" aria-expanded="false"><i class="icon icon-arrow-right"></i><span
                            class="nav-text">समस्याएँ देखे</span></a>
                    <ul aria-expanded="false">
                        <li><a href="{{ route('commander.complaints.view') }}">कमांडर समस्याएँ</a></li>
                        <li><a href="{{ route('operator.complaints.view') }}">कार्यालय समस्याएँ</a></li>
                    </ul>
                </li>

                <li><a href="{{ route('change_password.index') }}"><i class="icon icon-arrow-right"></i><span
                            class="nav-text">पासवर्ड बदलें</span></a></li>
            @endif



            {{-- User Panel --}}
            @if ($role == 3)
                <li class="nav-label" style="color: #c0bebe">कार्यालय टूल्स</li>
                <li><a href="{{ route('operator_complaint.index') }}"><i class="icon icon-arrow-right"></i><span
                            class="nav-text">समस्या पंजीयन करे</span></a></li>
                <li><a href="{{ route('operator_complaint.view') }}"><i class="icon icon-arrow-right"></i><span
                            class="nav-text">समस्याएँ देखे</span></a></li>
                {{-- <li><a href=""><i class="icon icon-arrow-right"></i><span class="nav-text">कार्यक्रम</span></a>
                </li>
                <li><a href="#"><i class="icon icon-arrow-right"></i><span class="nav-text">सुचना</span></a></li>
                <li><a href="#"><i class="icon icon-arrow-right"></i><span class="nav-text">मीडिया</span></a></li>
                <li><a href="#"><i class="icon icon-arrow-right"></i><span class="nav-text">फोटो गैलरी</span></a>
                </li>
                <li><a href="#"><i class="icon icon-arrow-right"></i><span class="nav-text">वीडियो
                            गैलरी</span></a></li> --}}
                <li><a href="{{ route('change_password.index') }}"><i class="icon icon-arrow-right"></i><span
                            class="nav-text">पासवर्ड बदलें</span></a></li>
            @endif


            {{-- Member Panel --}}
            @if (!in_array($role, [1, 2, 3]))
                <li class="nav-label" style="color: #c0bebe">फ़ील्ड टूल्स</li>
                <li><a href="{{ route('member.complaint') }}"><i class="icon icon-arrow-right"></i><span
                            class="nav-text">समस्या पंजीयन करे</span></a></li>

                <li><a href="{{ route('complaints.view') }}"><i class="icon icon-arrow-right"></i><span
                            class="nav-text">समस्याएँ देखे</span></a></li>
            @endif

        </ul>
    </div>
</div>
