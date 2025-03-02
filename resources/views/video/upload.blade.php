@extends('adminlte::page')

@section('title', __('video.upload_video'))

@section('content_header')
<h1>{{__('video.upload_video')}}</h1>
@stop

@section('css')
<link rel="stylesheet" href="{{ asset('css/receipt.add.css') }}">
@stop

@section('content')
<div class="timne line">
  <div class="timeline-header">
    <span>Tải lên tối đa 5 video</span>
    <span>{{ $currentTime->format('d/m/Y H:i') }}</span>
  </div>
  <div class="video-loading">
    <input id="input-pd" name="input-pd[]" type="file" class="file" data-preview-file-type="text" multiple accept="video/*">
  </div>
</div>
@include('common.loadingSpinner')
@stop
@section('js')
<script>
  $(document).ready(() => {
    const maxCapacity = {{ \Config::get('constants.max_capacity_video_upload') }}
    $.fn.fileinputBsVersion = "3.3.7";
  
    // $("#input-pd").fileinput();
  
    // // with plugin options
    // $("#input-pd").fileinput({
    //   'showUpload': false,
    //   'previewFileType': 'any'
    // });
    // file input
    $("#input-pd").fileinput({
        maxFileSize: maxCapacity,
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        enableResumableUpload: true,
        allowedPreviewTypes: ['video'],
        allowedFileExtensions: ['MP4', 'MOV', 'AVI', 'gif', 'WebM', 'WMV', 'FLV'],
        showCancel: true,
        uploadAsync: true,
        showUpload: true,
        showRemove: false,
        maxFileCount: 5,
        overwriteInitial: false,
        initialPreview: [],
        initialPreviewConfig: [],
        uploadUrl: '/video/uploadVideo',
        // uploadExtraData: function(index) {
        //   var files = $("#input-pd").fileinput('getFileStack');
        //   if (files.length > index) {
        //     return {
        //       '_token': $('input[name="_token"]').val(),
        //       // 'fileName': files[index].name,
        //       // 'fileSize': files[index].size,
        //       // 'fileId': 'some_unique_id_' + index,
        //       // 'chunkIndex': index,
        //       // 'chunkCount': files.length
        //     };
        //   }
        //   return {};
        // },
        uploadExtraData: function() {
                    return {
                        '_token': $('input[name="_token"]').val(),
                    }
                },
        initialPreviewAsData: true,
        initialPreviewFileType: 'video',
      }).on('fileuploaded', function(event, previewId, index, fileId, fileName) {
        let filename = fileId.split('_').slice(1).join('_');
        filename = '/uploads/' + filename;
        saveVideoIdAfterUpload(fileId.split('_')[0], filename)
      }).on('fileuploaderror', function(event, data, msg) {
        Swal.fire('fail!', msg, '')
      })
      .on('filebatchuploadcomplete', function(event, preview, config, tags, extraData) {
        Swal.fire({
          title: "Tải video lên server đã hoàn tất, đang xử lý nội dung video trên VIMEO. Bạn có muốn đến trang xử lý tiến trình không ?",
          icon: "warning",
          showCancelButton: true,
          confirmButtonColor: "#3085d6",
          cancelButtonColor: "#d33",
          confirmButtonText: "Có",
          cancelButtonText: "Không"
        }).then((result) => {
          if (result.isConfirmed) {
            window.location.href = "/video/process";
          }
        });
      });
  
    function saveVideoIdAfterUpload(fileId, filePath) {
      console.log("Saving file:", fileId, filePath);
      $.ajax({
        url: '/video/saveVideoId/',
        type: 'post',
        data: {
          "_token": $('meta[name="csrf-token"]').attr('content'),
          fileId,
          filePath
        },
        success: function(response) {
          console.log(response)
          if (!response.status) {
            const msgDeleteSuccess = "<?php echo __('video.message.upload_video_fail') ?>"
            Swal.fire('fail!', response.message, '')
          }
        }
      });
    }

  })
</script>
@stop