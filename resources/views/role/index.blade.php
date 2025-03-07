@extends('adminlte::page')

@section('title', __('role.role_management'))

@section('content_header')
<h1>{{__('role.role_management')}}</h1>
@stop

@section('css')
@stop

@section('content')
<div class="row">
  <div class="col-12">
    <div class="card">
      <div class="card-header">
        <div class="row">
          @if(\App\Helpers\Helper::checkPermission('role.create'))
          <a class="ml-auto" href="{{ route('role.create') }}">
            <button class="btn btn-success">{{ __('role.create_role') }}</button>
          </a>
          @endif
        </div>
      </div>
      <div class="card-body">
        <div class="dataTables_wrapper dt-bootstrap4">
          <table class="table table-bordered" id="role-table">
            <thead>
              <tr>
                <th>{{__('role.id')}}</th>
                <th>{{__('role.role_name')}}</th>
                <th>{{__('role.description')}}</th>
                <th>{{__('role.created_at')}}</th>
                <th>{{__('role.action')}}</th>
              </tr>
            </thead>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>
@include('common.loadingSpinner')
@stop

@section('js')
<script>
  $(function() {
    $('.select2').select2()
    const columnDefs = [{
        data: 'id',
        name: 'id',
        "searchable": true,
        'sortable': false,
      },
      {
        data: 'role_name',
        name: 'role_name',
        "searchable": true,
        'sortable': true,
      },
      {
        data: 'description',
        name: 'description',
        "searchable": false,
        'sortable': false,
      },
      {
        data: 'created_at',
        name: 'created_at',
        "searchable": true,
        'sortable': false,
      },
      {
        data: 'action',
        name: 'action',
        'sortable': false,
        "searchable": false
      }
    ]
    const table = $('#role-table').DataTable({
      serverSide: true,
      fixedHeader: true,
      searchDelay: 800,
      ajax: '/role/anyData',
      pageLength: 50,
      columns: columnDefs
    });

    function handleChangeFilter() {
      $("#role-table").dataTable().fnDestroy();
      $('#role-table').DataTable({
        serverSide: true,
        fixedHeader: true,
        searchDelay: 800,
        ajax: {
          "url": '/role/anyData',
          "type": 'GET',
        },
        pageLength: 50,
        columns: columnDefs
      });
    }

    function deleteRole(roleId) {
      $.ajax({
        url: '/role/delete/' + roleId,
        type: 'delete',
        data: {
          "_token": $('meta[name="csrf-token"]').attr('content'),
          "id": roleId
        },
        success: function(response) {
          if (response.status) {
            handleChangeFilter()
            const msgDeleteSuccess = "<?php echo __('role.message.delete_role_success') ?>"
            Swal.fire(msgDeleteSuccess, '', 'success')
          } else {
            Swal.fire('fail!', response.message, '')
          }
        }
      });
    }

    $('#role-table').on('click', '.btn-delete-role', function(e) {
      e.preventDefault();
      const id = $(this).data('id')
      const name = $(this).data('name')
      const msgConfirmDelete = "<?php echo __('role.message.delete_role_confirm_js'); ?>" + ' ' + name + ' ?'
      Swal.fire({
        title: msgConfirmDelete,
        showDenyButton: false,
        showCancelButton: true,
        confirmButtonText: "<?php echo __('role.btn_confirm'); ?>",
        cancelButtonText: "<?php echo __('role.btn_cancel'); ?>",
      }).then((result) => {
        /* Read more about isConfirmed, isDenied below */
        if (result.isConfirmed) {
          deleteRole(id)
        } else if (result.isDenied) {
          Swal.fire('Changes are not saved', '', 'info')
        }
      })
    });
  });
</script>
@stop