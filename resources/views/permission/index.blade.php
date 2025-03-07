@extends('adminlte::page')

@section('title', __('permission.permission_management'))

@section('content_header')
<h1>{{__('permission.permission_management')}}</h1>
@stop

@section('css')
@stop

@section('content')
<div class="row">
  <div class="col-12">
    <div class="card">
      <div class="card-header">
        <div class="row">
          @if(\App\Helpers\Helper::checkPermission('permission.create'))
          <a class="ml-auto" href="{{ route('permission.create') }}">
            <button class="btn btn-success">{{ __('permission.create_permission') }}</button>
          </a>
          @endif
        </div>
      </div>
      <div class="card-body">
        <div class="dataTables_wrapper dt-bootstrap4">
          <table class="table table-bordered" id="permission-table">
            <thead>
              <tr>
                <th>{{__('permission.id')}}</th>
                <th>{{__('permission.permission_name')}}</th>
                <th>{{__('permission.guard_name')}}</th>
                <th>{{__('permission.description')}}</th>
                <th>{{__('permission.created_at')}}</th>
                <th>{{__('permission.action')}}</th>
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
        'sortable': true,
      },
      {
        data: 'permission_name',
        name: 'permission_name',
        "searchable": true,
        'sortable': true,
      },
      {
        data: 'guard_name',
        name: 'guard_name',
        "searchable": false,
        'sortable': false,
      },
      {
        data: 'description',
        name: 'description',
        "searchable": true,
        'sortable': true,
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
    const table = $('#permission-table').DataTable({
      serverSide: true,
      fixedHeader: true,
      searchDelay: 800,
      ajax: '/permission/anyData',
      pageLength: 50,
      columns: columnDefs
    });

    function handleChangeFilter() {
      $("#permission-table").dataTable().fnDestroy();
      $('#permission-table').DataTable({
        serverSide: true,
        fixedHeader: true,
        searchDelay: 800,
        ajax: {
          "url": '/permission/anyData',
          "type": 'GET',
        },
        pageLength: 50,
        columns: columnDefs
      });
    }

    function deletePermission(id) {
      $.ajax({
        url: '/permission/delete/' + id,
        type: 'delete',
        data: {
          "_token": $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
          if (response.status) {
            handleChangeFilter()
            const msgDeleteSuccess = "<?php echo __('permission.message.delete_permission_success') ?>"
            Swal.fire(msgDeleteSuccess, '', 'success')
          } else {
            Swal.fire('fail!', response.message, '')
          }
        }
      });
    }

    $('#permission-table').on('click', '.btn-delete-permission', function(e) {
      e.preventDefault();
      const id = $(this).data('id')
      const name = $(this).data('name')
      const msgConfirmDelete = "<?php echo __('permission.message.delete_permission_confirm_js'); ?>" + ' ' + name + ' ?'
      Swal.fire({
        title: msgConfirmDelete,
        showDenyButton: false,
        showCancelButton: true,
        confirmButtonText: "<?php echo __('permission.btn_confirm'); ?>",
        cancelButtonText: "<?php echo __('permission.btn_cancel'); ?>",
      }).then((result) => {
        /* Read more about isConfirmed, isDenied below */
        if (result.isConfirmed) {
          deletePermission(id)
        } else if (result.isDenied) {
          Swal.fire('Changes are not saved', '', 'info')
        }
      })
    });
  });
</script>
@stop