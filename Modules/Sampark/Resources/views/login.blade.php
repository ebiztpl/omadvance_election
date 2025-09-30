@extends('sampark::layouts.login_layout')
@section('title', 'Login')
@section('content')
    <div class="container h-100 d-flex justify-content-center align-items-center" style="min-height: 100vh;">
        <div class="col-md-6">
            <div class="authincation-content">
                <div class="row no-gutters">
                    <div class="col-xl-12">
                        <div class="auth-form">

                            <h4 class="text-center mb-4">साइन इन करें</h4>

                            @if (session('success'))
                                <div class="alert alert-success">{{ session('success') }}</div>
                            @endif

                            @if (session('error'))
                                <div class="alert alert-danger">{{ session('error') }}</div>
                            @endif

                            @if (isset($errors) && $errors->any())
                                <div class="alert alert-danger">
                                    <ul class="mb-0">
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif


                            <div id="ajax-message"></div>
                            <form id="loginForm" method="POST" action="{{ url('api/sampark/login') }}">
                                @csrf
                                <div class="form-group">
                                    <label><strong>यूज़रनेम</strong></label>
                                    <input type="text" name="name" class="form-control" placeholder="यूज़रनेम"
                                        autocomplete="off" required>
                                </div>

                                <div class="form-group">
                                    <label><strong>पासवर्ड</strong></label>
                                    <input type="password" name="password" class="form-control" autocomplete="new-password"
                                        placeholder="पासवर्ड" required>
                                </div>

                                <div class="form-group">
                                    <div class="g-recaptcha" style="margin-top: 6px; margin-bottom: 8px"
                                        data-sitekey={{ config('services.recaptcha.key') }}></div>
                                </div>

                                <div class="text-center">
                                    <button type="submit" class="btn btn-primary btn-block">Login</button>
                                </div>
                            </form>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            $('#loginForm').on('submit', function(e) {
                e.preventDefault();
                $("#loader-wrapper").show();

                $.ajax({
                    url: $(this).attr('action'),
                    type: "POST",
                    data: $(this).serialize(),
                    success: function(response) {
                        $("#loader-wrapper").hide();

                        if (response.success) {
                            sessionStorage.setItem('sampark_token', response.token);
                            window.location.href = '/sampark/dashboard';
                        } else {
                            $('#ajax-message').html('<div class="alert alert-danger">' + response.message +
                                '</div>');
                        }
                    },
                    error: function(xhr) {
                        $("#loader-wrapper").hide();
                        let res = xhr.responseJSON;
                        $('#ajax-message').html('<div class="alert alert-danger">' + (res?.message ??
                            'Login failed') + '</div>');
                    }
                });
            });
        </script>
    @endpush
@endsection
