@extends('adminlte::page')

@section('title', __('admin.profile_management'))

@section('content_header')
<h1>{{__('admin.profile_management')}}</h1>
@stop

@section('content')
@include('auth.form_detail', ['user' => $user, 'roleList' => $roleList])
@include('common.loadingSpinner')
@stop

@section('css')
<style>
  .select2-container--default .select2-selection--multiple {
    border: 1px solid #6f42c1;
    /* Đường viền */
    background-color: #f3f4f6;
    /* Màu nền */
  }

  .select2-purple .select2-selection {
    background-color: #f3f4f6;
    /* Màu nền */
    border: 1px solid #6f42c1;
    /* Màu viền */
    color: #333;
    /* Màu chữ */
  }

  .select2-purple .select2-selection--multiple {
    background-color: #f3f4f6;
    /* Màu nền cho multiple */
  }

  .select2-purple .select2-selection__choice {
    background-color: #6f42c1;
    /* Màu nền cho lựa chọn */
    color: white;
    /* Màu chữ cho lựa chọn */
  }
</style>
@stop

@section('js')
<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>

<script>
  $(document).ready(function() {
    $('#tag-filter').select2({
      placeholder: "{{ __('room.filter_tag_placeholder') }}",
      dropdownCssClass: "select2-purple"
    });
  });
</script>
@stop