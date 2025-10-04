@php
    $name = session('logged_in_user');
    $role = session('logged_in_role');
@endphp

<div class="nav-header">


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

                    <form method="POST" action="{{ route('master.logout') }}">
                        @csrf
                        <button type="submit" class="btn btn-danger">लॉग आउट</button>
                    </form>
                </div>
            </div>
        </nav>
    </div>
</div>
