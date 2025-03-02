@extends('adminlte::page')

@section('title', __('admin.admin_management'))

@section('content_header')
<h1>{{__('admin.admin_management')}}</h1>
@stop

@section('css')
@stop

@section('content')
<div class="row">
  <div class="col-12">
    <div class="card">
      <div class="card-header">
        <div class="row">
          @if(\App\Helpers\Helper::checkPermission('admin.create'))
          <a class="ml-auto" href="{{ route('admin.createAccount') }}">
            <button class="btn btn-success">{{ __('admin.create_admin') }}</button>
          </a>
          @endif
        </div>
      </div>
      <div class="card-body">
        <div class="dataTables_wrapper dt-bootstrap4">
          <table class="table table-bordered" id="admin-table">
            <thead>
              <tr>
                <th>{{__('admin.id')}}</th>
                <th>{{__('admin.username')}}</th>
                <th>{{__('admin.role_name')}}</th>
                <th>{{__('admin.role_description')}}</th>
                <th>{{__('admin.action')}}</th>
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
        data: 'username',
        name: 'username',
        "searchable": true,
        'sortable': true,
      },
      {
        data: 'role_name',
        name: 'role_name',
        "searchable": false,
        'sortable': false,
      },
      {
        data: 'description',
        name: 'description',
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
    const table = $('#admin-table').DataTable({
      serverSide: true,
      fixedHeader: true,
      searchDelay: 800,
      ajax: '/admin/anyData',
      pageLength: 50,
      columns: columnDefs
    });

    function handleChangeFilter() {
      $("#admin-table").dataTable().fnDestroy();
      $('#admin-table').DataTable({
        serverSide: true,
        fixedHeader: true,
        searchDelay: 800,
        ajax: {
          "url": '/admin/anyData',
          "type": 'GET',
        },
        pageLength: 50,
        columns: columnDefs
      });
    }

    function deleteUser(adminId) {
      $.ajax({
        url: '/admin/delete/' + adminId,
        type: 'delete',
        data: {
          "_token": $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
          if (response.status) {
            handleChangeFilter()
            const msgDeleteSuccess = "<?php echo __('admin.message.delete_account_success') ?>"
            Swal.fire(msgDeleteSuccess, '', 'success')
          } else {
            Swal.fire('fail!', response.message, '')
          }
        }
      });
    }

    $('#admin-table').on('click', '.btn-delete-user', function(e) {
      e.preventDefault();
      const id = $(this).data('id')
      const name = $(this).data('name')
      const msgConfirmDelete = "<?php echo __('admin.message.delete_account_confirm_js'); ?>" + ' ' + name + ' ?'
      Swal.fire({
        title: msgConfirmDelete,
        showDenyButton: false,
        showCancelButton: true,
        confirmButtonText: "<?php echo __('admin.btn_confirm'); ?>",
        cancelButtonText: "<?php echo __('admin.btn_cancel'); ?>",
      }).then((result) => {
        /* Read more about isConfirmed, isDenied below */
        if (result.isConfirmed) {
          deleteUser(id)
        } else if (result.isDenied) {
          Swal.fire('Changes are not saved', '', 'info')
        }
      })
    });
  });
</script>
@stop