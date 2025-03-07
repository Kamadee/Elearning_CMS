@extends('adminlte::page')

@section('title', __('role.role_management'))

@section('content_header')
<h1>{{__('role.role_management')}}</h1>
@stop

@section('content')
@include('role.form', ['role' => null, 'permissionList' => $permissionList])
@include('common.loadingSpinner')
@stop

@section('js')
<script>
  $(document).ready(function() {

    $('.btn-submit-role').on('click', function(e) {
      e.preventDefault()
      const checkboxChecked = $('#form-role').find('.permission-selection')
      const dataChecked = []
      checkboxChecked.each(function(index) {
        if ($(this).is(':checked')) {
          const permission = $(this).data('grouppermission')
          const value = $(this).val()
          dataChecked.push({
            group: permission,
            permission: value
          })
        }
      });
      const roleName = $('#role-name').val()
      const roleDescription = $('#role-description').val()
      $('.permission-selected').val(JSON.stringify(dataChecked))
      $('#form-role').submit()
    })
  })
</script>
@stop