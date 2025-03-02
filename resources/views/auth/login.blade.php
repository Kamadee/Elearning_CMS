<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>{{__('common.system_name')}}</title>
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
  <link rel="stylesheet" href="{{ asset('/vendor/fontawesome-free/css/all.min.css') }}">
  <link rel="stylesheet" href="{{ asset('/vendor/adminlte/dist/css/adminlte.min.css') }}">
  <style>
    .login-page {
      background-image: url('dist/images/banner-login.png')
    }

    .login-box-msg-1 {
      font-size: 20px;
      color: #fff;
      text-align: center;
    }
  </style>
</head>

<body class="hold-transition login-page">
  <div class="login-box">
    <div class="login-logo">
      <a href="/">
        <img src="{{ asset('images/logo.svg') }}" />
      </a>
    </div>

    <div class="login-box-msg-1">
      <p>{{__('common.system_name')}}</p>
    </div>

    <div class="card">
      <div class="card-body login-card-body">
        <p class="login-box-msg">{{__('auth.login_title')}}</p>
        @if (Session::has('error'))
        <div class="alert alert-danger alert-dismissible" style="font-size: 14px;">
          <button type="button" class="close" data-dismiss="alert">&times;</button>
          {{ Session::get('error') }}
        </div>
        @endif
        <div class="alert alert-danger alert-dismissible collapse checkUserName" style="font-size: 14px;">
          <button type="button" class="close" data-dismiss="alert">&times;</button>
          {{__('message.required_username')}}
        </div>
        <div class="alert alert-danger alert-dismissible collapse checkPass" style="font-size: 14px;">
          <button type="button" class="close" data-dismiss="alert">&times;</button>
          {{__('message.required_pass')}}
        </div>
        <form action="{{ route('auth.postLogin') }}" method="post">
          {{ csrf_field() }}
          <div class="input-group mb-3">
            <input type="text" class="form-control" required name="username" placeholder="{{__('auth.user_id')}}">
            <div class="input-group-append">
              <div class="input-group-text">
                <span class="fas fa-users"></span>
              </div>
            </div>
          </div>
          <div class="input-group mb-3">
            <input type="password" class="form-control" required name="password" placeholder="{{__('auth.password')}}">
            <div class="input-group-append">
              <div class="input-group-text">
                <span class="fas fa-lock"></span>
              </div>
            </div>
          </div>
          <div class="social-auth-links text-center mb-3">
            <button type="submit" class="btn btn-block btn-primary postLoginForm">{{__('auth.login_button')}}</button>
          </div>
        </form>
      </div>
    </div>
  </div>
  <script src="{{ asset('/vendor/jquery/jquery.min.js') }}"></script>
  <script src="{{ asset('/vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
  <script src="{{ asset('/vendor/adminlte/dist/js/adminlte.min.js') }}"></script>
  <script src="{{ asset('/js/main.js') }}"></script>
  <script>
    $(document).ready(function() {
      console.log('aaa')
      $('.postLoginForm').click(function(e) {
        console.log();
        if ($("input[name=username]").val() == "") {
          $('.checkUserName').show();
          e.preventDefault();
        }

        if ($("input[name=password]").val() == "") {
          $('.checkPass').show();
          e.preventDefault();
        }
      });
    });
  </script>
</body>

</html>