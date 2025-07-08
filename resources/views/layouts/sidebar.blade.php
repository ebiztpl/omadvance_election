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
            <li class="nav-label">एडमिन टूल्स</li>
            <li><a href="{{ route('dashboard.index') }}"><i class="icon icon-speedometer"></i><span class="nav-text">सदस्यता फाॅर्म डेटा</span></a></li>

            <li><a href="{{ route('dashboard2.index') }}"><i class="icon icon-people"></i><span class="nav-text">सदस्य द्वारा जोड़े गए सदस्य</span></a></li>

            <li><a href="{{ route('birthdays.index') }}"><i class="icon icon-present"></i><span class="nav-text">सदस्य जन्मदिन</span></a></li>

            <li><a href="{{ route('complaints.index') }}"><i class="icon icon-book-open"></i><span class="nav-text">समस्याएँ देखे</span></a></li>

            <li><a href="{{ route('responsibility.index') }}"><i class="icon icon-people"></i><span class="nav-text">दायित्व नियुक्त करना</span></a></li>

            <li><a href="{{route('view_responsibility.index') }}"><i class="icon icon-people"></i><span class="nav-text">दायित्व कार्यकर्ता देखे</span></a></li>

            <li><a href="{{ route('generate.index') }}"><i class="icon icon-printer"></i><span class="nav-text">दायित्व कार्यकर्ता प्रिंट करे</span></a></li>

            <li><a href="{{route('change_password.index') }}"><i class="icon icon-lock"></i><span class="nav-text">पासवर्ड बदलें</span></a></li>
            @endif

            {{-- Manager Panel --}}
            @if ($role == 2)
            <li class="nav-label">मैनेजर टूल्स</li>

            <li><a href="{{ route('division.index') }}"><i class=" icon icon-link"></i><span class="nav-text">संभाग जोड़े</span></a></li>

            <li><a href="{{ route('city.master') }}"><i class="icon icon-link"></i><span class=" nav-text">जिला जोड़े</span></a></li>

            <li><a href="{{ route('vidhansabha.index') }}"><i class="icon icon-link"></i><span class="nav-text">विधानसभा/लोकसभा जोड़े</span></a></li>

            <li><a href="{{ route('sansadiya.index') }}"><i class="icon icon-link"></i><span class="nav-text">संसदीय क्षेत्र</span></a></li>

            <li><a href="{{ route('mandal.index') }}"><i class="icon icon-link"></i><span class="nav-text">मंडल जोड़े</span></a></li>

            <li><a href="{{ route('nagar.index') }}"><i class="icon icon-link"></i><span class="nav-text">नगर केंद्र/ग्राम केंद्र जोड़े</span></a></li>

            <li><a href="{{ route('polling.index') }}"><i class="icon icon-link"></i><span class="nav-text">मतदान केंद्र/क्रमांक जोड़े</span></a></li>

            <li><a href="{{ route('area.index') }}"><i class="icon icon-link"></i><span class="nav-text">मतदान क्षेत्र जोड़े</span></a></li>

            <li><a href="{{ route('level.index') }}"><i class="icon icon-link"></i><span class="nav-text">कार्य क्षेत्र</span></a></li>

            <li><a href="{{ route('positions.index') }}"><i class="icon icon-link"></i><span class="nav-text">दायित्व</span></a></li>

            <li><a href="{{ route('jati.index') }}"><i class="icon icon-pencil"></i><span class="nav-text">जाति मास्टर</span></a></li>

            <li><a href="{{ route('jati_polling.index') }}"><i class="icon icon-pencil"></i><span class="nav-text">जातिगत मतदाता प्रविष्टि</span></a></li>

            <li><a href="{{ route('jatiwise.index') }}"><i class="icon icon-book-open"></i><span class="nav-text">जातिगत मतदाता देखे</span></a></li>

            <li><a href="{{route('change_password.index') }}"><i class="icon icon-lock"></i><span class="nav-text">पासवर्ड बदलें</span></a></li>
            @endif



            {{-- User Panel --}}
            @if ($role == 3)
            <li class="nav-label">यूजर टूल्स</li>
            <li><a href=""><i class="icon icon-calender"></i><span class="nav-text">कार्यक्रम</span></a></li>
            <li><a href="#"><i class="icon icon-info"></i><span class="nav-text">सुचना</span></a></li>
            <li><a href="#"><i class="icon icon-folder"></i><span class="nav-text">मीडिया</span></a></li>
            <li><a href="#"><i class="icon icon-picture"></i><span class="nav-text">फोटो गैलरी</span></a></li>
            <li><a href="#"><i class="icon icon-camrecorder"></i><span class="nav-text">वीडियो गैलरी</span></a></li>
            <li><a href="{{route('change_password.index') }}"><i class="icon icon-lock"></i><span class="nav-text">पासवर्ड बदलें</span></a></li>
            @endif



            {{-- Member Panel --}}
            @else
            <li class="nav-label">मेंबर टूल्स</li>
            <li><a href="{{ route('complaints.index') }}"><i class="icon icon-user"></i><span class="nav-text">समस्या पंजीयन करे</span></a></li>

            <li><a href="#"><i class="icon icon-user-following"></i><span class="nav-text">मेरी प्रोफ़ाइल</span></a></li>
            <li><a href=""><i class="icon icon-lock"></i><span class="nav-text">पासवर्ड बदलें</span></a></li>
            @endif



            {{-- Common Links --}}
            <!-- <li class="nav-label">Logout</li>
            <li><a href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();"><i class="icon icon-single-copy-06"></i><span class="nav-text">Logout</span></a></li>
            <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">@csrf</form> -->
        </ul>
    </div>
</div>