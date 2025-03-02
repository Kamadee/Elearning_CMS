@extends('adminlte::page')

@section('title', __('tag.tag_management'))

@section('content_header')
<h1>{{__('tag.tag_management')}}</h1>
@stop

@section('content')
@include('tag.form', ['tag' => $tag])

@include('common.loadingSpinner')
@stop
@section('css')
<link rel="stylesheet" href="{{ asset('css/receipt.add.css') }}">
@stop
@section('js')
<script type="text/javascript" src="{{ asset('plugins/ckeditor/ckeditor.js') }}"></script>
<script>
  window.onbeforeunload = function() {
    if (form.serialize() != original && !isClickedSubmit)
      return 'Are you sure you want to leave?'
  }
</script>
@stop