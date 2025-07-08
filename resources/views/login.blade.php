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

                          <form method="POST" action="/login">
                              @csrf
                              <div class=" form-group">
                                  <label><strong>Username</strong></label>
                                  <input type="text" name="username" class="form-control">
                              </div>
                              <div class="form-group">
                                  <label><strong>Password</strong></label>
                                  <input type="password" name="password" class="form-control">
                              </div>

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
                              </div>

                              <!-- <div class="form-row d-flex justify-content-between mt-4 mb-2">
                                  <div class="form-group">
                                      <div class="form-check ml-2">
                                          <input class="form-check-input" type="checkbox" id="basic_checkbox_1">
                                          <label class="form-check-label" for="basic_checkbox_1">Remember me</label>
                                      </div>
                                  </div>
                                  <div class="form-group">
                                      <a href="page-forgot-password.html">Forgot Password?</a>
                                  </div>
                              </div> -->
                              <div class="text-center">
                                  <button type="submit" class="btn btn-primary btn-block">Sign In</button>
                              </div>
                          </form>

                      </div>
                  </div>
              </div>
          </div>
      </div>
  </div>
  @endsection