@extends('adminlte::page')

@section('title', __('video.video_management'))

@section('content_header')
<h1>{{__('video.video_management')}}</h1>
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
            @if(\App\Helpers\Helper::checkPermission('video.upload'))
            <div class="ml-auto">
              <button class="btn btn-info fetch_thumbnail_btn">{{ __('video.fetch_thumbnail') }}</button>
              <a href="{{ route('video.create') }}">
                <button class="btn btn-success">{{ __('video.upload_video') }}</button>
              </a>
            </div>
            @endif
          </div>
        </div>
      </div>
      <div class="card-body">
        <div class="dataTables_wrapper dt-bootstrap4">
          <table class="table table-bordered" id="video-table">
            <thead>
              <tr>
                <th>{{__('video.id')}}</th>
                <th>{{__('video.name')}}</th>
                <th>{{__('video.videoThumbnail')}}</th>
                <th>{{__('video.created_at')}}</th>
                <th>{{__('video.action')}}</th>
              </tr>
            </thead>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Modal -->
<div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Chi tiết video</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <div class="custom-body-content" style="position: relative; padding-bottom: 56.25%; height: 0;">
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>
@include('common.loadingSpinner')
@stop

@section('js')
<script>
  $(document).ready(() => {
    const columnDefs = [{
        data: 'id',
        name: 'id',
        orderable: false,
      },
      {
        data: 'title',
        name: 'title',
        orderable: false,
      },
      {
        data: 'videoThumbnail',
        name: 'thumbnail_id',
        searchable: false
      },
      {
        data: 'created_at',
        name: 'created_at',
        searchable: false
      },
      {
        data: 'action',
        name: 'action',
        orderable: false,
        searchable: false
      }
    ]
    const table = $('#video-table').DataTable({
      serverSide: true,
      fixedHeader: true,
      searchDelay: 800,
      ajax: {
        url: '/video/anyData',
        dataSrc: 'data'
      },
      pageLength: 50,
      columns: columnDefs
    });

    function handleChangeFilter() {
      $("#video-table").dataTable().fnDestroy();
      $('#video-table').DataTable({
        serverSide: true,
        fixedHeader: true,
        searchDelay: 800,
        ajax: {
          "url": '/video/anyData',
          "type": 'GET',
        },
        pageLength: 50,
        columns: columnDefs
      });
    }



    function uploadVimeoThumbnail() {
      $.ajax({
        url: '/video/vimeo/thumbnail',
        type: 'GET',
        success: function(response) {
          if (response.status) {
            location.reload()
          } else {
            Swal.fire('fail!', response.message, '')
          }
        }
      });
    }

    $(".fetch_thumbnail_btn").on("click", function(e) {
      e.preventDefault();
      console.log(1111);

      uploadVimeoThumbnail();
    });

    // Xem video
    function showVideoDetail(vimeoId) {
      $.ajax({
        url: '/video/vimeo/detail/' + vimeoId,
        type: 'get',
        success: function(response) {
          if (response.status) {
            // Xóa nội dung cũ trước khi thêm nội dung mới
            $('.custom-body-content').empty().append(response.data);
            // Sử dụng CSS để đảm bảo video chiếm toàn bộ không gian
            $('.custom-body-content iframe').css({
              'position': 'absolute',
              'top': '0',
              'left': '0',
              'width': '100%',
              'height': '100%',
            });
            $('#exampleModal').modal('show');
          } else {
            Swal.fire('fail!', response.message, '')
          }
        }
      });
    }

    $('#video-table').on('click', '.btn-info-video', function(e) {
      e.preventDefault();
      $('.custom-body-content').html('');
      const id = $(this).attr('video-id');
      showVideoDetail(id);
    });

    // Xóa video
    function handleDelVideo(vimeoId) {
      $.ajax({
        url: '/video/delete/' + vimeoId,
        type: 'delete',
        data: {
          "_token": $('meta[name="csrf-token"]').attr('content'),
          "id": vimeoId
        },
        success: function(response) {
          if (response.status) {
            console.log(response)
            handlerFilter()
            const msgDeleteSuccess = "<?php echo __('video.message.delete_video_success') ?>"
            Swal.fire(msgDeleteSuccess, '', 'success')
          } else {
            Swal.fire('fail!', response.message, '')
          }
        }
      });
    }

    $('#video-table').on('click', '.btn-delete-video', function(e) {
      e.preventDefault();
      const vimeoId = $('.video').attr('video-id');
      const name = $(this).data('name')
      const msgConfirmDelete = "<?php echo __('video.message.delete_video_confirm_js'); ?>" + ' ' + name + ' ?'
      Swal.fire({
        title: msgConfirmDelete,
        showDenyButton: false,
        showCancelButton: true,
        confirmButtonText: "<?php echo __('video.btn_confirm'); ?>",
        cancelButtonText: "<?php echo __('video.btn_cancel'); ?>",
      }).then((result) => {
        /* Read more about isConfirmed, isDenied below */
        if (result.isConfirmed) {
          handleDelVideo(vimeoId)
        } else if (result.isDenied) {
          Swal.fire('Changes are not saved', '', 'info')
        }
      })
    });
  });
</script>
@stop