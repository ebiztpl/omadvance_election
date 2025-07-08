@extends('layouts.login_layout')

@section('content')
<div class="container h-100 d-flex justify-content-center align-items-center" style="min-height: 100vh;">
    <div class="col-md-6">
        <div class="authincation-content">
            <div class="row no-gutters">
                <div class="col-xl-12">
                    <div class="auth-form">
                        <h4 class="text-center mb-4">Sign in your account</h4>

                        @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        @endif

                        @if($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                        @endif
                        <div class="form-group mt-3">
                            <label><strong>Select Role</strong></label><br>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="user_role" id="role_admin" value="admin" checked>
                                <label class="form-check-label" for="role_admin">Admin</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="user_role" id="role_manager" value="manager">
                                <label class="form-check-label" for="role_manager">Manager</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="user_role" id="role_user" value="user">
                                <label class="form-check-label" for="role_user">User</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="user_role" id="role_member" value="member">
                                <label class="form-check-label" for="role_member">Member (OTP)</label>
                            </div>
                        </div>


                        <form method="POST" action="/login" id="normal-login-form">
                            @csrf
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

                        <form method="POST" action="/verify_otp" id="otp-login-form" style="display: none;">
                            @csrf
                            <div class="form-group">
                                <label><strong>Phone / Email</strong></label>
                                <input type="text" name="mobile" id="mobile" class="form-control" placeholder="Enter Registered Mobile">
                            </div>

                            <div class="text-center mb-3">
                                <button type="button" id="send-otp-btn" class="btn btn-primary btn-block">Send OTP</button>
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
                        </form>

                        <div id="otp-feedback" class="mb-3"></div>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        const roleRadios = document.querySelectorAll('input[name="user_role"]');
        const normalForm = document.getElementById('normal-login-form');
        const otpForm = document.getElementById('otp-login-form');

        const contactInput = document.getElementById('mobile');
        const sendOtpBtn = document.getElementById('send-otp-btn');
        const otpSection = document.getElementById('otp-section');

        // Form toggle
        function toggleForms(role) {
            if (role === 'member') {
                normalForm.style.display = 'none';
                otpForm.style.display = 'block';
            } else {
                normalForm.style.display = 'block';
                otpForm.style.display = 'none';
            }
        }

        roleRadios.forEach(radio => {
            radio.addEventListener('change', function() {
                toggleForms(this.value);
            });
        });

        const selected = document.querySelector('input[name="user_role"]:checked');
        if (selected) toggleForms(selected.value);

        let countdown = 60;
        let timer;

        sendOtpBtn.addEventListener("click", function() {
            const contactValue = contactInput.value.trim();
            if (!contactValue) {
                return;
            }

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
                    if (data.status === "success") {
                        contactInput.readOnly = true;
                        otpSection.style.display = "block";
                        sendOtpBtn.disabled = true;
                        startCountdown();
                    } else {
                        const feedback = document.getElementById('otp-feedback');
                        feedback.innerHTML = `<div class="alert alert-${data.status === 'otp_success' ? 'success' : 'danger'} alert-dismissible fade show" role="alert">${data.message || (data.status === 'otp_success' ? 'ओटीपी सफलतापूर्वक भेजा गया!' : 'ओटीपी भेजने में विफल!')}<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        </div>`;
                    }
                })
                .catch(err => {
                    console.error(err);
                });

        });

        setTimeout(() => {
            const alertBox = document.querySelector('#otp-feedback .alert');
            if (alertBox) {
                alertBox.classList.remove('show');
                alertBox.classList.add('fade');
            }
        }, 6000);

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
    });
</script>

@endsection