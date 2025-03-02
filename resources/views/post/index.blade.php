@extends('adminlte::page')

@section('title', __('post.post_management'))

@section('content_header')
<h1>{{__('post.post_management')}}</h1>
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
            @if(\App\Helpers\Helper::checkPermission('post.create'))
            <a href="{{ route('posts.create') }}" class="btn btn-primary">
              <i class="fas fa-plus"></i> {{__('post.create_post')}}
            </a>
            @endif
          </div>
        </div>
        <div class="row">
          <div class="col-6 form-group">
            <label>{{ __('post.filter_category') }}</label>
            <select id="post-category-filter" class="select2 form-control" multiple="multiple"
              data-placeholder="{{ __('post.filter_category_placeholder') }}" style="width: 100%;">
              @foreach($categoryList as $category)
              <option value="{{ $category->id }}">{{ $category->category_name }}</option>
              @endforeach
            </select>
          </div>

          <div class="col-6 form-group">
            <label>{{ __('post.filter_status') }}</label>
            <select id="post-status-filter" class="select2 form-control" multiple="multiple"
              data-placeholder="{{ __('post.filter_status_placeholder') }}" style="width: 100%;">
              @foreach($statusList as $id => $status)
              <option value="{{ $id }}">{{ __('post.status_list')[$id] }}</option>
              @endforeach
            </select>
          </div>
        </div>
      </div>
    </div>
    <div class="card-body">
      <div class="dataTables_wrapper dt-bootstrap4">
        <table class="table table-bordered" id="post-table">
          <thead>
            <tr>
              <th>{{__('post.id')}}</th>
              <th>{{__('post.title')}}</th>
              <th>{{__('post.description')}}</th>
              <th>{{__('post.thumbnail')}}</th>
              <th>{{__('post.post_category')}}</th>
              <th>{{__('post.status')}}</th>
              <th>{{__('post.created_at')}}</th>
              <th>{{__('post.updated_at')}}</th>
              <th>{{__('post.action')}}</th>
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
<!-- CSS jquery -->
<!-- <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet"> -->
<!-- <link href="https://cdnjs.cloudflare.com/ajax/libs/select2-bootstrap4-theme/1.0.0/select2-bootstrap4.min.css" rel="stylesheet"> -->
<!-- jQuery -->
<!-- <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script> -->
<!-- Select 2 -->
<!-- <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script> -->
<!-- DataTables -->
<!-- <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css"> -->
<!-- <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script> -->
<!-- Swal cdn -->
<!-- <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script> -->
<script>
  $(function() {
    $('.select2').select2()
    const columnsDef = [{
        data: 'id',
        name: 'id',
      },
      {
        data: 'title',
        name: 'title',
        'sortable': false
      },
      {
        data: 'description',
        name: 'description',
        'sortable': false
      },
      {
        data: 'postThumbnail',
        name: 'thumbnail',
        'sortable': false
      },
      {
        data: 'postCategory',
        name: 'category',
        'sortable': false
      },
      {
        data: 'postStatus',
        name: 'status'
      },
      {
        data: 'created_at',
        name: 'created_at'
      },
      {
        data: 'updated_at',
        name: 'updated_at',
      },
      {
        data: 'action',
        name: 'action',
        'sortable': false
      }
    ];

    const table = $('#post-table').DataTable({
      serverSide: true,
      fixedHeader: true,
      searchDelay: 800,
      ajax: '/posts/anyData',
      pageLength: 50,
      columns: columnsDef
    });

    $('#post-category-filter').on('change', function(e) {
      handlerFilter();
    });
    $('#post-status-filter').on('change', function(e) {
      handlerFilter();
    });

    function handlerFilter() {
      $("#post-table").dataTable().fnDestroy();
      const postCategories = $('#post-category-filter').val();
      const statusList = $('#post-status-filter').val();
      console.log('Filter data:', {
        postCategories,
        statusList
      }, typeof(postCategories), typeof(statusList));
      const dataFilter = {
        postCategories,
        statusList
      }
      $('#post-table').DataTable({
        serverSide: true,
        fixedHeader: true,
        searchDelay: 800,
        ajax: {
          url: '/posts/anyData',
          data: function(d) {
            d.postCategories = postCategories;
            d.statusList = statusList;
            console.log("Ajax request data:", d);
          },
          error: function(xhr) {
            console.error("AJAX error:", xhr.responseText);
          }
        },
        pageLength: 50,
        columns: columnsDef
      });
    }

    function handleDelPost(postId) {
      $.ajax({
        url: '/posts/delete/' + postId,
        type: 'delete',
        data: {
          "_token": $('meta[name="csrf-token"]').attr('content'),
          "id": postId
        },
        success: function(response) {
          if (response.status) {
            console.log(response)
            handlerFilter()
            const msgDeleteSuccess = "<?php echo __('post.message.delete_post_success') ?>"
            Swal.fire(msgDeleteSuccess, '', 'success')
          } else {
            Swal.fire('fail!', response.message, '')
          }
        }
      });
    }

    $('#post-table').on('click', '.btn-delete-post', function(e) {
      e.preventDefault();
      const id = $(this).data('id')
      const name = $(this).data('name')
      const msgConfirmDelete = "<?php echo __('post.message.delete_post_confirm_js'); ?>" + ' ' + name + ' ?'
      Swal.fire({
        title: msgConfirmDelete,
        showDenyButton: false,
        showCancelButton: true,
        confirmButtonText: "<?php echo __('post.btn_confirm'); ?>",
        cancelButtonText: "<?php echo __('post.btn_cancel'); ?>",
      }).then((result) => {
        /* Read more about isConfirmed, isDenied below */
        if (result.isConfirmed) {
          handleDelPost(id)
        } else if (result.isDenied) {
          Swal.fire('Changes are not saved', '', 'info')
        }
      })
    });

  });
</script>
@stop