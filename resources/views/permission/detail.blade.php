@extends('adminlte::page')

@section('title', __('permission.permission_management'))

@section('content_header')
<h1>{{__('permission.permission_management')}}</h1>
@stop

@section('content')
@include('permission.form', ['permission' => $permission])
@include('common.loadingSpinner')
@stop

@section('js')
<script>
  $('#form-permission').validate({
    rules: {
      permissionName: {
        required: true,
        maxlength: 255,
      },
      permissionDescription: {
        maxlength: 255,
      }
    },
  });
</script>
@stop