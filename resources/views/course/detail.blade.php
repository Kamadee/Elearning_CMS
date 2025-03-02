@extends('adminlte::page')

@section('title', __('course.course_management'))


@section('content_header')
<h1>{{__('course.course_management')}}</h1>
@stop

@section('content')
@include('course.form', ['course' => $course, 'courseCategoryList' => $courseCategoryList, 'courseStatus' => $courseStatus, 'tagList' => $tagList])
@include('common.loadingSpinner')
@stop
@section('css')
<style>
  .custom-body-content iframe {
    width: 1100px !important;
    height: 500px !important;
  }
</style>
@stop
@section('js')
<link rel="stylesheet" href="https://code.jquery.com/ui/1.13.3/themes/smoothness/jquery-ui.css">
<!-- <script src="https://code.jquery.com/jquery-3.7.1.js"></script>
<script src="https://code.jquery.com/ui/1.13.3/jquery-ui.js"></script> -->
<script type="text/javascript" src="{{ URL::asset('plugins/ckeditor/ckeditor.js') }}"></script>
<script src="{{ asset('/js/util.js') }}"></script>
<link rel="stylesheet" href="https://code.jquery.com/ui/1.13.3/themes/smoothness/jquery-ui.css">
<script src="https://code.jquery.com/ui/1.13.3/jquery-ui.js"></script>
<script>
  const editor = CKEDITOR.replace('content', {
    fileTools_requestHeaders: {
      'X-CSRFToken': '{{ csrf_token() }}',
    },
    filebrowserUploadUrl: '/courses/upload-img',
  });

  editor.on('fileUploadRequest', function(evt) {
    const token = '{{ csrf_token() }}'
    var fileLoader = evt.data.fileLoader,
      formData = new FormData(),
      xhr = fileLoader.xhr;
    xhr.setRequestHeader('x-csrf-token', '{{ csrf_token() }}');
    xhr.open('POST', fileLoader.uploadUrl, true);
    formData.append('upload', fileLoader.file, fileLoader.fileName);
    formData.append('_token', token);
    fileLoader.xhr.send(formData);
    evt.stop();
  });

  const authorEditor = CKEDITOR.replace('authorDescription', {
    fileTools_requestHeaders: {
      'X-CSRFToken': '{{ csrf_token() }}',
    },
    filebrowserUploadUrl: '/courses/upload-img',
  });

  authorEditor.on('fileUploadRequest', function(evt) {
    const token = '{{ csrf_token() }}'
    var fileLoader = evt.data.fileLoader,
      formData = new FormData(),
      xhr = fileLoader.xhr;
    xhr.setRequestHeader('x-csrf-token', '{{ csrf_token() }}');
    xhr.open('POST', fileLoader.uploadUrl, true);
    formData.append('upload', fileLoader.file, fileLoader.fileName);
    formData.append('_token', token);
    fileLoader.xhr.send(formData);
    evt.stop();
  });

  $('.select2').select2()
  const form = $('#form-course')
  const original = form.serialize()
  let isClickedSubmit = false

  //   handle upload room image
  const maxCapacity = {
    {
      \
      Config::get('constants.max_capacity_image_upload')
    }
  }
  const course = @json($course);

  if (course) {
    const initialPreview = [course.thumbnail]
    const initialPreviewConfig = [{
      caption: course.thumbnail,
      width: "120px",
      url: "/courses/delete-img/" + course.id,
      key: course.id,
      extra: {
        '_token': $('input[name="_token"]').val()
      }
    }]
    var meta_token = $("meta[name=csrf-token]");
    $("#input-pd").fileinput({
      maxFileSize: maxCapacity,
      allowedFileExtensions: ['jpg', 'jpeg', 'png', 'gif'],
      uploadAsync: true,
      showUpload: false,
      showRemove: false,
      minFileCount: 0,
      maxFileCount: 1,
      overwriteInitial: false,
      uploadExtraData: function() {
        return {
          '_token': $('input[name="_token"]').val(),
          'course_id': course.id
        }
      },
      initialPreview: initialPreview,
      initialPreviewAsData: true, // identify if you are sending preview data only and not the raw markup,
      initialPreviewConfig: initialPreviewConfig,
      initialPreviewFileType: 'image', // image is the default and can be overridden in config below
    }).on('fileuploaded', function(e, params) {
      console.log('File uploaded params', params);
    })

    const initialPreviewBanner = [course.banner]
    const initialPreviewConfigBanner = [{
      caption: course.banner,
      width: "120px",
      url: "/courses/delete-img-banner/" + course.id,
      key: course.id,
      extra: {
        '_token': $('input[name="_token"]').val()
      }
    }]

    $("#input-banner-pd").fileinput({
      maxFileSize: maxCapacity,
      allowedFileExtensions: ['jpg', 'jpeg', 'png', 'gif'],
      uploadAsync: true,
      showUpload: false,
      showRemove: false,
      minFileCount: 0,
      maxFileCount: 1,
      overwriteInitial: false,
      uploadExtraData: function() {
        return {
          '_token': $('input[name="_token"]').val(),
          'course_id': course.id
        }
      },
      initialPreview: initialPreviewBanner,
      initialPreviewAsData: true, // identify if you are sending preview data only and not the raw markup,
      initialPreviewConfig: initialPreviewConfigBanner,
      initialPreviewFileType: 'image', // image is the default and can be overridden in config below
    }).on('fileuploaded', function(e, params) {
      console.log('File uploaded params', params);
    })
  }

  $('#saleOffPrice').on("change", function() {
    $('#originalPrice').valid();
  });

  $('#form-course').validate({
    rules: {
      title: {
        required: true,
        maxlength: 255,
      },
      author: {
        maxlength: 255,
      },
      description: {
        maxlength: 1000,
        required: true,
      },
      content: {
        required: true,
      },
      originalPrice: {
        greaterThan: "#saleOffPrice",
      },
      status: {
        required: true,
      }
    },
    messages: {
      originalPrice: {
        greaterThan: "Giá gốc phải cao hơn hoặc bằng giá sale."
      },
    },
    submitHandler: function(form) {
      // Form is valid, so you can submit it
      let videoList = []
      $('.row-episode').each(function(i, obj) {
        const epTitle = $(obj).find('.ep-title').val();
        const epDescription = $(obj).find('.ep-description').val();
        const epThumbnail = $(obj).find('.ep-thumbnail').attr('src');
        const vimeoId = $(obj).data('vimeo-id')
        videoList.push({
          epTitle,
          epDescription,
          vimeoId,
          epThumbnail
        })
      })
      videoList = JSON.stringify(videoList)
      $('.input-video-list').val(videoList)
      form.submit();
    },
  });

  $('.btn-submit-course').on('click', function() {
    isClickedSubmit = true;
    $("#input-pd").fileinput('upload');
    $("#input-banner-pd").fileinput('upload');
  })

  window.onbeforeunload = function() {
    if (form.serialize() != original && !isClickedSubmit)
      return 'Are you sure you want to leave?'
  }


  $('#video-body-list').sortable()
  const columnDefs = [{
      data: 'id',
      name: 'id',
      "searchable": true,
      width: '8%',
    },
    {
      data: 'video_title',
      name: 'video_title',
      "searchable": true,
      'sortable': true
    },
    {
      data: 'videoThumbnail',
      name: 'videoThumbnail',
      'sortable': false
    },
    {
      data: 'created_at',
      name: 'created_at'
    },
    {
      data: 'action',
      name: 'action',
      'sortable': false
    }
  ]
  // const table = $('#video-table').DataTable({
  //     serverSide: true,
  //     fixedHeader: true,
  //     searchDelay: 800,
  //     ajax: '/video/anyData',
  //     pageLength: 50,
  //     columns: columnDefs,
  //     select: {
  //       style: 'multi',
  //       selector: 'td:first-child'
  //     }
  // });

  function showVideoDetail(vimeoId) {
    $.ajax({
      url: '/video/vimeo/detail/' + vimeoId,
      type: 'get',
      success: function(response) {
        if (response.status) {
          $('.custom-body-content').append(response.data);
          $('#exampleModal').modal('show');
        } else {
          Swal.fire('fail!', response.message, '')
        }
      }
    });
  }

  let table;
  $('#modal-select-video').on('show.bs.modal', function(e) {
    if ($.fn.dataTable.isDataTable('#video-table')) {
      $('#video-table').DataTable().clear().destroy();
    }
    const vimeoIdSelectedList = checkedData.map(item => item.vimeo_id);
    table = $('#video-table').DataTable({
      serverSide: true,
      fixedHeader: true,
      paging: true,
      scrollY: '500px',
      scrollCollapse: true,
      ajax: '/video/anyDataForCreate',
      pageLength: 50,
      order: [],
      columns: [{
          data: 'check',
          name: 'check',
          target: "no-sort",
          orderable: false,
          className: 'checkbox-wrap'
        },
        {
          data: 'id',
          name: 'id',
          "searchable": true,
          width: '8%',
        },
        {
          data: 'title',
          name: 'title',
          "searchable": true,
          'sortable': true
        },
        {
          data: 'videoThumbnail',
          name: 'videoThumbnail',
          'sortable': false
        },
        {
          data: 'created_at',
          name: 'created_at'
        },
      ],
      createdRow: function(row, data, dataIndex) {
        $(row).data('vimeo-id', data['vimeo_id'])
        $(row).data('vimeo-thumbnail', data['thumbnail_id'])
        if (vimeoIdSelectedList.includes(data['vimeo_id'])) {
          $(row).addClass('has-selected');
          $(row).find('.form-checkbox-input').prop('checked', true);
          $(row).find('.form-checkbox-input').attr('disabled', true)
        }
      }
    });
  })

  $('#video-table').on('hidden.bs.modal', function(e) {
    // Clear the DataTable
    if (table) {
      table.clear().draw();
    }
  });

  $('#video-table').on('click', '.btn-info-video', function(e) {
    e.preventDefault();
    $('.custom-body-content').html('');
    const id = $(this).attr('video-id');
    showVideoDetail(id);
  });

  let checkedData = [...course.videos] ?? [];
  if (checkedData.length === 0) {
    $('.table-result').addClass('hidden')
  }
  updateVideoBodyList();

  $('.btn-select-course-video').on('click', function() {
    $('#video-table .form-checkbox-input:checked').each(function() {
      var row = $(this).closest('tr');
      var rowData = table.row(row).data();
      rowData.thumbnail = row.data('thumbnail')
      const vimeoIdSelectedList = checkedData.map(item => item.vimeo_id);
      if (!vimeoIdSelectedList.includes(rowData['vimeo_id'])) {
        checkedData.push(rowData);
      }
    });
    updateVideoBodyList()
    $('#modal-select-video').modal('hide')
  })

  function updateVideoBodyList() {
    let rowHtml = ''
    for (let i = 0; i < checkedData.length; i++) {
      const {
        video_title,
        video_description,
        vimeo_id,
        thumbnail,
        video_thumbnail,
        thumbnail_id,
        title
      } = checkedData[i]

      let videoThumbnailDisplay = ''
      if (video_thumbnail) {
        videoThumbnailDisplay = video_thumbnail
      } else if (thumbnail_id) {
        videoThumbnailDisplay = thumbnail_id
      } else {
        videoThumbnailDisplay = thumbnail
      }

      let videoTitleDisplay = ''
      if (video_title) {
        videoTitleDisplay = video_title
      } else {
        videoTitleDisplay = title
      }

      let videoDescriptionDisplay = ''
      if (video_description) {
        videoDescriptionDisplay = video_description
      } else {
        videoDescriptionDisplay = ''
      }
      rowHtml += '<tr class="row-episode" data-vimeo-id="' + vimeo_id + '">' +
        '<td>' +
        '<input type="text" class="ep-title form-control" value="' + videoTitleDisplay + '" />' +
        '</td>' +
        '<td>' +
        '<img src="' + videoThumbnailDisplay + '" width="100" height="100" class="ep-thumbnail" />' +
        '</td>' +
        '<td>' +
        '<textarea class="ep-description form-control" rows="4" cols="50">' + videoDescriptionDisplay + '</textarea>' +
        '</td>' +
        '<td>' +
        '<button type="button" class="btn btn-block btn-info btn-info-video" style=" width: 130px; " video-id="' + vimeo_id + '">Xem video</button>' +
        '</td>' +
        '<td>' +
        '<i class="fas fa-trash red-icon btn-delete-video"></i>' +
        '</td>' +
        '</tr>'
    }
    $('#video-body-list').empty().append(rowHtml)
    if (checkedData.length === 0) {
      $('.table-result').addClass('hidden')
    } else {
      $('.table-result').removeClass('hidden')
    }
  }

  $('#video-body-list').on('click', '.btn-delete-video', function(e) {
    const $this = $(this)
    Swal.fire({
      title: 'Xác nhận xóa tập',
      icon: 'warning',
      confirmButtonText: 'Xác nhận',
      cancelButtonText: "Đóng",
      showCancelButton: true,
    }).then((result) => {
      if (result['isConfirmed']) {
        const row = $this.closest('tr');
        const vimeoId = row.data('vimeo-id')
        const currentIdx = checkedData.findIndex((el) => {
          return el.vimeo_id === vimeoId;
        })
        checkedData.splice(currentIdx, 1)
        updateVideoBodyList()
      }
    })
  })

  $('#video-body-list').on('click', '.btn-info-video', function(e) {
    e.preventDefault();
    $('.custom-body-content').html('');
    const id = $(this).attr('video-id');
    showVideoDetail(id);
  });

  $('#exampleModal').on('hidden.bs.modal', function(e) {
    $('.custom-body-content').html('');
  });

  function showVideoDetail(vimeoId) {
    $.ajax({
      url: '/video/vimeo/detail/' + vimeoId,
      type: 'get',
      success: function(response) {
        if (response.status) {
          $('.custom-body-content').append(response.data);
          $('#exampleModal').modal('show');
          $('.custom-body-content').find('iframe').css({
            "width": "1100px !important",
            "height": "500px !important"
          });
        } else {
          Swal.fire('fail!', response.message, '')
        }
      }
    });
  }
</script>
@stop