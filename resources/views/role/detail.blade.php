@extends('adminlte::page')

@section('title', __('role.role_management'))

@section('content_header')
<h1>{{__('role.role_management')}}</h1>
@stop

@section('content')
@include('role.form', ['role' => $role, 'permissionList' => $permissionList])
@include('common.loadingSpinner')
@stop

@section('js')
<script src="https://code.jquery.com/jquery-3.6.0.min.js" crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.3/jquery.validate.min.js"></script>
<script>
  $(document).ready(function() {
    const role = @json($role);


    $('.btn-submit-role').on('click', function(e) {
      e.preventDefault()
      console.log(111);

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