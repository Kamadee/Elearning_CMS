@extends('adminlte::page')

@section('title', __('category.category_management'))

@section('content_header')
<h1>{{__('category.category_management')}}</h1>
@stop

@section('css')
<link rel="stylesheet" href="{{ asset('css/receipt.add.css') }}">
@stop

@section('content')
<div class="row">
  <div class="col-12">
    <div class="card">
      <div class="card-header">
        <div class="row mb-2">
          <div class="col">
            @if(\App\Helpers\Helper::checkPermission('category.create'))
            <a href="{{ route('category.create') }}" class="btn btn-primary">
              <i class="fas fa-plus"></i> {{__('category.create_category')}}
            </a>
            @endif
          </div>
        </div>
      </div>
      <div class="card-body">
        <div class="dataTables_wrapper dt-bootstrap4">
          <table class="table table-bordered" id="category-table">
            <thead>
              <tr>
                <th>{{__('category.id')}}</th>
                <th>{{__('category.category_name')}}</th>
                <th>{{__('category.count')}}</th>
                <th>{{__('category.action')}}</th>
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
    const columnDefs = [{
        data: 'id',
        name: 'id',
        orderable: false,
      },
      {
        data: 'category_name',
        name: 'category_name',
      },
      {
        data: 'posts_count',
        name: 'posts_count',
        searchable: false
      },
      {
        data: 'action',
        name: 'action',
        orderable: false,
        searchable: false
      }
    ]
    const table = $('#category-table').DataTable({
      serverSide: true,
      fixedHeader: true,
      searchDelay: 800,
      ajax: {
        url: '/category/anyData',
        dataSrc: 'data'
      },
      pageLength: 50,
      columns: columnDefs
    });

    function handleChangeFilter() {
      $("#category-table").dataTable().fnDestroy();
      $('#category-table').DataTable({
        serverSide: true,
        fixedHeader: true,
        searchDelay: 800,
        ajax: {
          "url": '/category/anyData',
          "type": 'GET',
        },
        pageLength: 50,
        columns: columnDefs
      });
    }

    function handleDelCategory(categoryId) {
      $.ajax({
        url: '/category/delete/' + categoryId,
        type: 'delete',
        data: {
          "_token": $('meta[name="csrf-token"]').attr('content'),
          "id": categoryId
        },
        success: function(response) {
          if (response.status) {
            handleChangeFilter()
            const msgDeleteSuccess = "<?php echo __('category.message.delete_category_success') ?>"
            Swal.fire(msgDeleteSuccess, '', 'success')
          } else {
            Swal.fire('fail!', response.message, '')
          }
        }
      });
    }

    $('#category-table').on('click', '.btn-delete-category', function(e) {
      console.log('okkk')
      e.preventDefault();
      const id = $(this).data('id')
      const name = $(this).data('name')
      console.log(name)
      const msgConfirmDelete = "<?php echo __('category.message.delete_category_confirm_js'); ?>" + ' ' + name + ' ?'
      Swal.fire({
        title: msgConfirmDelete,
        showDenyButton: false,
        showCancelButton: true,
        confirmButtonText: "<?php echo __('category.btn_confirm'); ?>",
        cancelButtonText: "<?php echo __('category.btn_cancel'); ?>",
      }).then((result) => {
        if (result.isConfirmed) {
          handleDelCategory(id)
        } else if (result.isDenied) {
          Swal.fire('Changes are not saved', '', 'info')
        }
      })
    });
  });
</script>
@stop