@php
    $name = session('logged_in_user');
    $role = session('logged_in_role');
@endphp

<div class="nav-header">
    <a class="brand-logo">
        @if ($role == 1)
            <h4 class="text-white m-3">एडमिन
                पैनल</h4>
        @elseif ($role == 2)
            <h4 class="text-white m-3">
                मैनेजर
                पैनल</h4>
        @elseif ($role == 3)
            <h4 class="text-white m-3">कार्यालय
                पैनल</h4>
        @else
            <h4 class="text-white m-3">फ़ील्ड
                पैनल</h4>
        @endif
    </a>

    <div class="nav-control">
        <div class="hamburger">
            <span class="line"></span>
            <span class="line"></span>
            <span class="line"></span>
        </div>
    </div>
</div>

<div class="header">
    <div class="header-content">
        <nav class="navbar navbar-expand">
            <div class="collapse navbar-collapse justify-content-between">
                <div class="header-left">
                    @if (isset($pageTitle) && isset($breadcrumbs))
                        <div class="row align-items-center ssd" style="margin: 0; padding: 10px 15px;">
                            <div class="col-sm-12">
                                <h4 style="margin: 0; font-size: 18px;">{{ $pageTitle }}</h4>
                            </div>
                        </div>
                    @endif
                </div>

                <div class="navbar-nav header-right" style="justify-content: center; align-items: center">
                    {{-- <div class="row page-titles page-titles2" style="margin-bottom:0; margin-right: 8px">
                        <ol class="breadcrumb">
                            @foreach ($breadcrumbs as $label => $link)
                                @if ($loop->last)
                                    <li class="breadcrumb-item active"><a href="#">{{ $label }}</a></li>
                                @else
                                    <li class="breadcrumb-item"><a href="{{ $link }}">{{ $label }}</a>
                                    </li>
                                @endif
                            @endforeach
                        </ol>
                    </div> --}}



                    @php
                        $role = session('admin_role');
                        $registrationId = session('registration_id');
                        $nagarName = null;

                        if ($role === 'फ़ील्ड' && $registrationId) {
                            $position = \DB::table('assign_position')
                                ->where('member_id', $registrationId)
                                ->latest('post_date')
                                ->first();

                            if ($position) {
                                $nagar = \DB::table('nagar_master')->where('nagar_id', $position->refrence_id)->first();

                                if ($nagar) {
                                    $nagarName = $nagar->nagar_name;
                                }
                            }
                        }
                    @endphp

                    <div class="d-flex justify-content-center align-items-center w-100 commhh"
                        style="position: absolute; left: 0; right: 0;">
                        @if ($nagarName)
                            <h5 class="text-center" style="margin: 0; font-weight: bold; color: red; font-size: 24px;">
                                कमाण्ड ऐरिया:
                                {{ $nagarName }}</h5>
                        @endif
                    </div>


                    @php
                        $role = session('admin_role');
                        $registrationId = session('registration_id');

                        $memberName = null;
                        if ($role === 'फ़ील्ड' && $registrationId) {
                            $memberName = \App\Models\RegistrationForm::where(
                                'registration_id',
                                $registrationId,
                            )->value('name');
                        }
                    @endphp
                    <div class="dropdown">
                        <a class="btn btn-danger text-black dropdown-toggle" href="#" role="button"
                            data-toggle="dropdown">
                            {{ $role === 'फ़ील्ड' ? $memberName ?? 'फ़ील्ड' : $name ?? 'कार्यालय' }}
                        </a>
                        <div class="dropdown-menu dropdown-menu-right">
                            <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                                @csrf
                            </form>
                            <a href="#" class="dropdown-item"
                                onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                Logout
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </nav>
    </div>
</div>
