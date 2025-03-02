@extends('adminlte::page')

@section('title', __('admin.admin_management'))

@section('content_header')
<h1>{{__('admin.admin_management')}}</h1>
@stop

@section('content')
@include('customer.form', ['customer' => $customer, 'customerStatus' => $customerStatus])
@include('common.loadingSpinner')
@stop

@section('js')
<script>
  easyNumberSeparator({
    selector: '.number-separator',
    separator: ',',
    resultInput: '#customer-money'
  })
  $('.select2').select2()
  $('#customerDetailForm').validate({
    rules: {
      firstName: {
        required: true,
        maxlength: 255,
      },
      lastName: {
        required: true,
        maxlength: 255,
      },
      email: {
        required: true,
        email: true,
        maxlength: 255,
      },
      phone: {
        required: true,
        number: true,
        minlength: 10,
        maxlength: 10
      },
      avatar3d: {
        required: true,
      },
      avatar2d: {
        required: true,
      },
      rank: {
        required: true,
        number: true,
        min: 0
      },
      moneyFormat: {
        required: true,
      },
      money: {
        required: true,
        number: true,
      }
    },
  });
</script>
@stop