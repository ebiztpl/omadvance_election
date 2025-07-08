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
        <h4 class="text-white m-3">यूजर
            पैनल</h4>
        @else
        <h4 class="text-white m-3">स्वागत</h4>
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
                    <div class="row align-items-center" style="margin: 0; padding: 10px 15px;">
                        <div class="col-sm-12">
                            <h4 style="margin: 0; font-size: 18px;">{{ $pageTitle }}</h4>
                        </div>
                    </div>
                    @endif
                </div>

                <div class="navbar-nav header-right" style="justify-content: center; align-items: center">
                    <div class="row page-titles" style="margin-bottom:0; margin-right: 8px">
                        <ol class="breadcrumb">
                            @foreach ($breadcrumbs as $label => $link)
                            @if ($loop->last)
                            <li class="breadcrumb-item active"><a href="#">{{ $label }}</a></li>
                            @else
                            <li class="breadcrumb-item"><a href="{{ $link }}">{{ $label }}</a></li>
                            @endif
                            @endforeach
                        </ol>
                    </div>


                    <div class="dropdown">
                        <a class="btn btn-danger text-black dropdown-toggle" href="#" role="button" data-toggle="dropdown">
                            {{ $name ?? 'User' }}
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