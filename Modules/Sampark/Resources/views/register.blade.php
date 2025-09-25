@extends('layouts.login_layout')


@section('content')
    <div class="container h-100 d-flex justify-content-center align-items-center" style="min-height: 100vh;">
        <div class="col-md-6">
            <div class="authincation-content">
                <div class="row no-gutters">
                    <div class="col-xl-12">
                        <div class="auth-form">
                            <h4 class="text-center mb-4">Register</h4>
                            <div id="ajax-message"></div>
                            <form id="registerForm" method="POST" action="{{ route('sampark.register') }}">
                                @csrf

                                {{-- Name --}}
                                <div class="form-group">
                                    <label for="name"><strong>Name</strong></label>
                                    <input type="text" name="name" id="name" class="form-control"
                                        placeholder="Enter your name" required>
                                </div>

                                {{-- Email --}}
                                <div class="form-group">
                                    <label for="email"><strong>Email</strong></label>
                                    <input type="email" name="email" id="email" class="form-control"
                                        placeholder="Enter your email" autocomplete="off" required>
                                </div>

                                {{-- Password --}}
                                <div class="form-group">
                                    <label for="password"><strong>Password</strong></label>
                                    <input type="password" name="password" id="password" class="form-control"
                                        placeholder="Enter a password" autocomplete="new-password" required>
                                </div>

                                {{-- Submit Button --}}
                                <div class="form-group text-center">
                                    <button type="submit" class="btn btn-primary btn-block">Register</button>
                                </div>

                                {{-- Errors --}}
                                @if (isset($errors) && $errors->any())
                                    <div class="alert alert-danger mt-2">
                                        <ul class="mb-0">
                                            @foreach ($errors->all() as $error)
                                                <li>{{ $error }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            $(document).ready(function() {
                $('#registerForm').on('submit', function(e) {
                    e.preventDefault();

                    $("#loader-wrapper").show();

                    $.ajax({
                        url: $(this).attr('action'),
                        type: 'POST',
                        data: $(this).serialize(),
                        success: function(response) {
                            $("#loader-wrapper").hide();
                            $('#ajax-message').html('<div class="alert alert-success">' + response
                                .message + '</div>');
                            setTimeout(function() {
                                window.location.href = response.redirect;
                            }, 1000);
                        },
                        error: function(xhr) {
                            $("#loader-wrapper").hide();
                            let res = xhr.responseJSON;
                            if (res && res.errors) {
                                let errorHtml = '<div class="alert alert-danger"><ul>';
                                $.each(res.errors, function(key, value) {
                                    errorHtml += '<li>' + value[0] + '</li>';
                                });
                                errorHtml += '</ul></div>';
                                $('#ajax-message').html(errorHtml);
                            } else {
                                $('#ajax-message').html(
                                    '<div class="alert alert-danger">Registration failed. Please try again.</div>'
                                    );
                            }
                        }
                    });
                });
            });
        </script>
    @endpush
@endsection
