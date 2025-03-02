@extends('adminlte::page')

@section('title', __('admin.admin_management'))

@section('content_header')
<h1>{{__('admin.admin_management')}}</h1>
@stop

@section('content')
@include('admin.form', ['user' => $user, 'roleList' => $roleList])
@include('common.loadingSpinner')
@stop

@section('css')
<link rel="stylesheet" href="{{ asset('css/receipt.add.css') }}">
@stop

@section('js')
<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
<script>
  $('#admin-role-filter').select2();

  $('#form-user').validate({
    rules: {
      username: {
        required: true,
        maxlength: 255,
      },
      password: {
        minlength: 5,
        maxlength: 255,
      },
      repassword: {
        minlength: 5,
        maxlength: 255,
        equalTo: "#password"
      }
    },
  });

  window.onbeforeunload = function() {
    if (form.serialize() != original && !isClickedSubmit)
      return 'Are you sure you want to leave?'
  }
</script>
@stop