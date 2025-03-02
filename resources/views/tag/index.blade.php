@extends('adminlte::page')

@section('title', __('tag.tag_management'))

@section('content_header')
<h1>{{__('tag.tag_management')}}</h1>
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
            @if(\App\Helpers\Helper::checkPermission('tag.create'))
            <a href="{{ route('tag.create') }}" class="btn btn-primary">
              <i class="fas fa-plus"></i> {{__('tag.create_tag')}}
            </a>
            @endif
          </div>
        </div>
      </div>
      <div class="card-body">
        <div class="dataTables_wrapper dt-bootstrap4">
          <table class="table table-bordered" id="tag-table">
            <thead>
              <tr>
                <th>{{__('tag.id')}}</th>
                <th>{{__('tag.tag_name')}}</th>
                <th>{{__('tag.count')}}</th>
                <th>{{__('tag.action')}}</th>
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
        data: 'tag_name',
        name: 'tag_name',
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
    const table = $('#tag-table').DataTable({
      serverSide: true,
      fixedHeader: true,
      searchDelay: 800,
      ajax: {
        url: '/tag/anyData',
        dataSrc: 'data'
      },
      pageLength: 50,
      columns: columnDefs
    });

    function handleChangeFilter() {
      $("#tag-table").dataTable().fnDestroy();
      $('#tag-table').DataTable({
        serverSide: true,
        fixedHeader: true,
        searchDelay: 800,
        ajax: {
          "url": '/tag/anyData',
          "type": 'GET',
        },
        pageLength: 50,
        columns: columnDefs
      });
    }

    function handleDelTag(tagId) {
      $.ajax({
        url: '/tag/delete/' + tagId,
        type: 'delete',
        data: {
          "_token": $('meta[name="csrf-token"]').attr('content'),
          "id": tagId
        },
        success: function(response) {
          if (response.status) {
            handleChangeFilter()
            const msgDeleteSuccess = "<?php echo __('tag.message.delete_tag_success') ?>"
            Swal.fire(msgDeleteSuccess, '', 'success')
          } else {
            Swal.fire('fail!', response.message, '')
          }
        }
      });
    }

    $('#tag-table').on('click', '.btn-delete-tag', function(e) {
      console.log('okkk')
      e.preventDefault();
      const id = $(this).data('id')
      const name = $(this).data('name')
      console.log(name)
      const msgConfirmDelete = "<?php echo __('tag.message.delete_tag_confirm_js'); ?>" + ' ' + name + ' ?'
      Swal.fire({
        title: msgConfirmDelete,
        showDenyButton: false,
        showCancelButton: true,
        confirmButtonText: "<?php echo __('tag.btn_confirm'); ?>",
        cancelButtonText: "<?php echo __('tag.btn_cancel'); ?>",
      }).then((result) => {
        if (result.isConfirmed) {
          handleDelTag(id)
        } else if (result.isDenied) {
          Swal.fire('Changes are not saved', '', 'info')
        }
      })
    });
  });
</script>
@stop