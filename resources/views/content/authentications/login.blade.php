@extends('layouts/blankLayout')

@section('title', 'Login Basic - Pages')

@section('page-style')
<!-- Page -->
<link rel="stylesheet" href="{{asset('assets/vendor/css/pages/page-auth.css')}}">
@endsection

@section('content')
<div class="position-relative">
  <div class="authentication-wrapper authentication-basic container-p-y">
    <div class="authentication-inner py-4">

      <!-- Login -->
      <div class="card p-2 text-light" style="background-color: #FF0000;">
        <!-- Logo -->
        <div class="app-brand justify-content-center mt-5">
          <a href="{{url('/')}}" class="app-brand-link gap-2">
            <span class="app-brand-logo demo">@include('_partials.macros',["height"=>20,"withbg"=>'fill: #fff;'])</span>
            <span class="app-brand-text demo text-heading fw-semibold">Berry's Bakery</span>
          </a>
        </div>
        <!-- /Logo -->

        <div class="card-body mt-2">
          <h4 class="mb-2" style="color: white;">Welcome to Berry's Bakery! 👋</h4>
          <p class="mb-4" style="color: white;">Please sign-in to your account</p>

          {{-- Tampilkan pesan error --}}
          @if($errors->has('login_error'))
            <div class="alert alert-danger">
              {{ $errors->first('login_error') }}
            </div>
          @endif

          <form id="formAuthentication" class="mb-3" action="{{ route('login.submit') }}" method="POST">
            @csrf
            <div class="form-floating form-floating-outline mb-3">
              <input type="text" class="form-control" id="username" name="username" placeholder="Enter your username" autofocus>
              <label for="username">Username</label>
            </div>
            <br>
            <div class="mb-3">
              <div class="form-password-toggle">
                <div class="input-group input-group-merge">
                  <div class="form-floating form-floating-outline">
                    <input type="password" id="password" class="form-control" name="password" placeholder="Password" />
                    <label for="password">Password</label>
                  </div>
                  <span class="input-group-text cursor-pointer"><i class="mdi mdi-eye-off-outline"></i></span>
                </div>
              </div>
            </div>
            <br>
            <div class="mb-3">
              <button class="btn btn-primary d-grid w-100" type="submit">Sign in</button>
            </div>
          </form>
        </div>
      </div>
      <!-- /Login -->

      <img src="{{asset('assets/img/illustrations/donat.png')}}" alt="donut" class="authentication-image-object-left d-none d-lg-block">
      <img src="{{asset('assets/img/illustrations/auth-basic-mask-light.png')}}" class="authentication-image d-none d-lg-block" alt="triangle-bg">
      <img src="{{asset('assets/img/illustrations/roti.jpg')}}" alt="bread" class="authentication-image-object-right d-none d-lg-block">
    </div>
  </div>
</div>
@endsection
