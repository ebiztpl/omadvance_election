@extends('layouts.login_layout')

@section('content')
    <div class="container h-100 d-flex justify-content-center align-items-center" style="min-height: 100vh;">
        <div class="col-md-6">
            <div class="authincation-content">
                <div class="row no-gutters">
                    <div class="col-xl-12">
                        <div class="auth-form">
                            <h4 class="text-center mb-4">Sign in your account</h4>

                            {{-- Success --}}
                            @if (session('success'))
                                <div class="alert alert-success alert-dismissible fade show" role="alert">
                                    {{ session('success') }}
                                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                            @endif

                            {{-- Errors --}}
                            @if ($errors->any())
                                <div class="alert alert-danger">
                                    <ul class="mb-0">
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            {{-- Role Selection --}}
                            <div class="form-group mt-3">
                                <label><strong>Select Role</strong></label><br>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="user_role" id="role_admin"
                                        value="एडमिन" checked>
                                    <label class="form-check-label" for="role_admin">एडमिन</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="user_role" id="role_manager"
                                        value="मैनेजर">
                                    <label class="form-check-label" for="role_manager">मैनेजर</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="user_role" id="role_user"
                                        value="कार्यालय">
                                    <label class="form-check-label" for="role_operator">कार्यालय</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="user_role" id="role_member"
                                        value="फ़ील्ड">
                                    <label class="form-check-label" for="role_member">फ़ील्ड</label>
                                </div>
                            </div>

                            {{-- Normal Login Form --}}
                            <form method="POST" action="/login" id="normal-login-form">
                                @csrf
                                <input type="hidden" name="user_role" id="user_role_hidden" value="एडमिन">
                                {{-- ✅ Role sent here --}}
                                <div class="form-group">
                                    <label><strong>Username</strong></label>
                                    <input type="text" name="username" class="form-control">
                                </div>
                                <div class="form-group">
                                    <label><strong>Password</strong></label>
                                    <input type="password" name="password" class="form-control">
                                </div>
                                <div class="text-center">
                                    <button type="submit" class="btn btn-primary btn-block">Sign In</button>
                                </div>
                            </form>

                            {{-- OTP Login Form --}}
                            <form method="POST" action="/verify_otp" id="otp-login-form" style="display: none;">
                                @csrf
                                <div class="form-group">
                                    <label><strong>Registered Mobile</strong></label>
                                    <input type="text" name="mobile" id="mobile" class="form-control"
                                        placeholder="Enter Registered Mobile">
                                </div>
                                <div class="text-center mb-3">
                                    <button type="button" id="send-otp-btn" class="btn btn-primary btn-block">Send
                                        OTP</button>
                                </div>

                                <div id="otp-section" style="display: none;">
                                    <div class="form-group">
                                        <label><strong>OTP</strong></label>
                                        <input type="text" name="otp" class="form-control" placeholder="Enter OTP">
                                    </div>
                                    <div class="text-center">
                                        <button type="submit" class="btn btn-success btn-block">Verify OTP</button>
                                    </div>
                                </div>

                                <div id="otp-feedback" class="mt-3"></div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- JS --}}
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const roleRadios = document.querySelectorAll('input[name="user_role"]');
            const normalForm = document.getElementById('normal-login-form');
            const otpForm = document.getElementById('otp-login-form');
            const hiddenRoleInput = document.getElementById('user_role_hidden');

            const contactInput = document.getElementById('mobile');
            const sendOtpBtn = document.getElementById('send-otp-btn');
            const otpSection = document.getElementById('otp-section');

            // Form toggle based on role
            function toggleForms(role) {
                if (role === 'फ़ील्ड') {
                    normalForm.style.display = 'none';
                    otpForm.style.display = 'block';
                } else {
                    normalForm.style.display = 'block';
                    otpForm.style.display = 'none';
                }
            }

            // Listen to role changes
            roleRadios.forEach(radio => {
                radio.addEventListener('change', function() {
                    toggleForms(this.value);
                    hiddenRoleInput.value = this.value;
                });
            });

            // Set default on load
            const selected = document.querySelector('input[name="user_role"]:checked');
            if (selected) {
                toggleForms(selected.value);
                hiddenRoleInput.value = selected.value;
            }


            let countdown = 60;
            let timer;

            sendOtpBtn.addEventListener("click", function() {
                const contactValue = contactInput.value.trim();
                if (!contactValue) return;

                fetch("/send-otp", {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/json",
                            "X-CSRF-TOKEN": document.querySelector('input[name="_token"]').value
                        },
                        body: JSON.stringify({
                            mobile: contactValue
                        })
                    })
                    .then(res => res.json())
                    .then(data => {
                        const feedback = document.getElementById('otp-feedback');
                        const isSuccess = (data.status || '').toLowerCase() === 'success';

                        feedback.innerHTML = `
                <div class="alert alert-${isSuccess ? 'success' : 'danger'} alert-dismissible fade show" role="alert">
                    ${data.message || (isSuccess ? 'ओटीपी सफलतापूर्वक भेजा गया!' : 'ओटीपी भेजने में विफल!')}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>`;

                        if (isSuccess) {
                            contactInput.readOnly = true;
                            otpSection.style.display = "block";
                            sendOtpBtn.disabled = true;
                            startCountdown();
                        }
                    })
                    .catch(err => console.error(err));
            });

            function startCountdown() {
                sendOtpBtn.textContent = `Resend OTP (${countdown}s)`;
                timer = setInterval(() => {
                    countdown--;
                    sendOtpBtn.textContent = `Resend OTP (${countdown}s)`;
                    if (countdown <= 0) {
                        clearInterval(timer);
                        sendOtpBtn.disabled = false;
                        sendOtpBtn.textContent = "Resend OTP";
                        countdown = 60;
                    }
                }, 1000);
            }

            setTimeout(() => {
                const alertBox = document.querySelector('#otp-feedback .alert');
                if (alertBox) {
                    alertBox.classList.remove('show');
                    alertBox.classList.add('fade');
                }
            }, 6000);
        });
    </script>

@endsection
