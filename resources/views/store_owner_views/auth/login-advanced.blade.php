@extends('store_owner_views.auth.app')
@section('page_title', 'Login - GoMeat')
@section('styles')
<style>
    .authentication-wrapper.authentication-cover {
        align-items: flex-start;
    }

    .authentication-wrapper {
        display: flex;
        flex-basis: 100%;
        min-height: 100vh;
        width: 100%;
    }

    .authentication-wrapper.authentication-cover .authentication-inner {
        height: 100vh;
    }

    .authentication-wrapper .authentication-inner {
        width: 100%;
    }

    .light-style .authentication-wrapper .authentication-bg {
        background-color: #fff;
    }

    html:not([dir=rtl]) .me-3 {
        margin-right: 1rem !important;
    }

    .btn-label-facebook {
        color: #3b5998;
        border-color: rgba(0, 0, 0, 0);
        background: #e0e4ef;
    }

    .btn-icon {
        --bs-btn-active-border-color: transparent;
        padding: 0;
        width: calc(2.309375rem + calc(var(--bs-border-width) * 2));
        height: calc(2.309375rem + calc(var(--bs-border-width) * 2));
        display: inline-flex;
        flex-shrink: 0;
        justify-content: center;
        align-items: center;
    }

    .btn-label-google-plus {
        color: #dd4b39;
        border-color: rgba(0, 0, 0, 0);
        background: #fae2df;
    }

    .btn-label-twitter {
        color: #1da1f2;
        border-color: rgba(0, 0, 0, 0);
        background: #dbf0fd;
    }
</style>
@endsection
@section('content')
<!-- Content -->
<div class="authentication-wrapper authentication-cover">
    <div class="authentication-inner row m-0">
        <!-- /Left Text -->
        <div class="d-none d-lg-flex col-lg-7 col-xl-8 align-items-center p-5">
            <div class="w-100 d-flex justify-content-center">
                <img src="{{asset('public/assets/store_owner/img/pages/login-banner.png')}}" class="img-fluid" alt="Login image" width="700" data-app-dark-img="illustrations/boy-with-rocket-dark.png" data-app-light-img="illustrations/boy-with-rocket-light.png">
            </div>
        </div>
        <!-- /Left Text -->

        <!-- Login -->
        <div class="d-flex col-12 col-lg-5 col-xl-4 align-items-center authentication-bg p-sm-5 p-4">
            <div class="w-px-400 mx-auto">
                <!-- Logo -->
                <div class="app-brand justify-content-center">
                    <a href="index.html" class="app-brand-link gap-2">
                        <img src="{{asset('public/assets/store_owner/img/logo/login-logo.png')}}" class="img-fluid" width="250px" alt="">
                    </a>
                </div>
                <hr>
                <!-- /Logo -->
                <h4 class="mb-2">Welcome to GoMeat! 👋</h4>
                <p class="mb-4">Please sign-in to your account and start the adventure</p>

                <form id="formAuthentication" class="mb-3" action="{{route('vendor.auth.login')}}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label for="email" class="form-label">Email or Username</label>
                            <input type="text" class="form-control" id="email" name="email" placeholder="Enter your email or username" autofocus />
                        </div>
                        <div class="mb-3 form-password-toggle">
                            <div class="d-flex justify-content-between">
                                <label class="form-label" for="password">Password</label>
                                <!-- <a href="auth-forgot-password-basic.html">
                                    <small>Forgot Password?</small>
                                </a> -->
                            </div>
                            <div class="input-group input-group-merge">
                                <input type="password" id="password" class="form-control" name="password" placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;" aria-describedby="password" />
                                <span class="input-group-text cursor-pointer"><i class="bx bx-hide"></i></span>
                            </div>
                        </div>
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="remember-me" />
                                <label class="form-check-label" for="remember-me"> Remember Me </label>
                            </div>
                        </div>
                        <div class="mb-3">
                            <button class="btn btn-primary d-grid w-100" type="submit">Sign in</button>
                        </div>
                    </form>

                <p class="text-center">
                    <span>New on our platform?</span>
                    <a href="auth-register-cover.html">
                        <span>Create an account</span>
                    </a>
                </p>

                <div class="divider my-4">
                    <div class="divider-text">or</div>
                </div>

                <div class="d-flex justify-content-center">
                    <a href="javascript:;" class="btn btn-icon btn-label-facebook me-3">
                        <i class="tf-icons bx bxl-facebook"></i>
                    </a>

                    <a href="javascript:;" class="btn btn-icon btn-label-google-plus me-3">
                        <i class="tf-icons bx bxl-google-plus"></i>
                    </a>

                    <a href="javascript:;" class="btn btn-icon btn-label-twitter">
                        <i class="tf-icons bx bxl-twitter"></i>
                    </a>
                </div>
            </div>
        </div>
        <!-- /Login -->
    </div>
</div>
@endsection