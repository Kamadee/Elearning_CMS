@extends('adminlte::page')

@section('title', __('course.course_management'))

@section('content_header')
<h1>{{__('course.course_management')}}</h1>
@stop

@section('content')
<div class="container-fluid">
  <div class="row">
    <div class="col-md-12">
      <div class="card card-primary">
        <div class="card-header">
          <h3 class="card-title">Danh sách top khóa học</h3>
        </div>
        @if(count($errors) > 0 )
        <div class="card-body">
          <div class="form-group">
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
              <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
              <ul class="m-0">
                @foreach($errors->all() as $error)
                <li>{{$error}}</li>
                @endforeach
              </ul>
            </div>
          </div>
        </div>
        @endif
        @if(session('success'))
        <div class="card-body">
          <div class="form-group">
            <div class="alert alert-success alert-dismissible fade show" role="alert">
              <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
              <ul class="m-0">
                <li>{{session('success')}}</li>
              </ul>
            </div>
          </div>
        </div>
        @endif
        <form id="form-course" method="POST">
          {{ csrf_field() }}
          <div class="card-body">
            <div class="form-group">
              <label for="room-name">Chọn khóa học</label>
              <div>
                <div data-toggle="modal" data-target="#modal-select-video">
                  <div class="box">
                    <label for="fileUpload-1" class="file-upload">
                      <div class="wrap">
                        <div id="video-selection">Chọn khóa học</div>
                      </div>
                    </label>
                  </div>
                </div>
              </div>
              <div class="table table-wrapper">
                <table class="table table-bordered w-full table-result">
                  <thead>
                    <tr>
                      <th style="width: 250px">Tên khóa học</th>
                      <th style="width: 40px">Ảnh nhỏ</th>
                      <th>Mô tả nội dung</th>
                      <th style="width: 40px">Giá gốc</th>
                      <th style="width: 40px">Giá khuyến mãi</th>
                      <th style="width: 40px">Tác giả</th>
                      <th style="width: 40px">Action</th>
                    </tr>
                  </thead>
                  <tbody id="video-body-list">
                  </tbody>
                </table>
                <input class="hidden" id="courseListHidden" name="courseList" />
                <!-- <button class="btn btn-primary hidden" id="btn-save">Lưu</button> -->
              </div>
              <div id="modal-select-video" class="modal fade" role="dialog">
                <div class="modal-dialog modal-xl">
                  <div class="modal-content">
                    <div class="modal-header">
                      <h4 class="modal-title">Danh sách video</h4>
                      <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                      </button>
                    </div>
                    <div class="modal-body modal-body-select-video">
                      <div class="card-body">
                        <div class="dataTables_wrapper dt-bootstrap4">
                          <table class="table table-bordered" id="video-table">
                            <thead>
                              <tr>
                                <th>Chọn</th>
                                <th>Tên khóa học</th>
                                <th>Tác giả</th>
                                <th>Ảnh nhỏ</th>
                                <th>Giá gốc</th>
                                <th>Giá khuyến mãi</th>
                              </tr>
                            </thead>
                          </table>
                        </div>
                      </div>
                      <div class="modal-footer justify-content-end">
                        <button type="button" class="btn btn-primary btn-select-course-video">Xác nhận</button>
                        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
@include('common.loadingSpinner')
@stop

@section('js')
<link rel="stylesheet" href="https://code.jquery.com/ui/1.13.3/themes/smoothness/jquery-ui.css">
<link rel="stylesheet" href="https://code.jquery.com/ui/1.13.3/themes/smoothness/jquery-ui.css">
<script src="https://code.jquery.com/ui/1.13.3/jquery-ui.js"></script>
<script>
  const hotData = @json($hotData);

  const courseData = hotData.map(a => a.course);
  $('#video-body-list').sortable()
  let checkedData = [...courseData];
  updateVideoBodyList()

  // Tạo khóa học nổi bật
  $('.btn-select-course-video').on('click', function() {
    let courseList = []
    let newCourses = []
    $('#video-table .form-checkbox-input:checked').each(function() {
      var row = $(this).closest('tr')
      var rowData = table.row(row).data()
      const courseExists = checkedData.some(item => item.id === rowData['id']);
      if (!courseExists) {
        newCourses.push(rowData);
      }
    });
    Swal.fire({
      title: 'Xác nhận tạo khóa học nổi bật?',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonText: 'Xác nhận',
      cancelButtonText: "Đóng",
    }).then((result) => {
      if (result['isConfirmed']) {
        courseList = JSON.stringify(newCourses)
        $.ajax({
          url: '/courses/postHotCourse',
          method: 'POST',
          data: {
            "_token": $('meta[name="csrf-token"]').attr('content'),
            "courseList": courseList
          },
          success: function(response) {
            // Cập nhật checkedData
            checkedData = [...checkedData, ...newCourses]; // Cập nhật danh sách
            updateVideoBodyList(); // Cập nhật giao diện
            Swal.fire({
              title: 'Thành công!',
              text: response.message,
              icon: 'success'
            });
          },
          error: function(error) {
            console.error('Có lỗi xảy ra:', error);
          }
        });
      }
    })
    updateVideoBodyList()
    $('#modal-select-video').modal('hide')
  })

  let table;
  $('#modal-select-video').on('show.bs.modal', function(e) {
    if ($.fn.dataTable.isDataTable('#video-table')) {
      $('#video-table').DataTable().clear().destroy();
    }
    const courseIdSelected = checkedData.map(item => item.id);
    table = $('#video-table').DataTable({
      serverSide: true,
      fixedHeader: true,
      paging: true,
      scrollY: '500px',
      scrollCollapse: true,
      ajax: '/courses/anyDataForHot',
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
          data: 'title',
          name: 'title',
          "searchable": true,
          width: '8%',
        },
        {
          data: 'author',
          name: 'author',
          "searchable": true,
          'sortable': true
        },
        {
          data: 'courseThumbnail',
          name: 'courseThumbnail',
          'sortable': false
        },
        {
          data: 'originalPrice',
          name: 'originalPrice'
        },
        {
          data: 'saleOffPrice',
          name: 'saleOffPrice'
        },
      ],
      createdRow: function(row, data, dataIndex) {
        $(row).data('data-id', data['id'])
        if (courseIdSelected.includes(data['id'])) {
          $(row).addClass('has-selected');
          $(row).find('.form-checkbox-input').prop('checked', true);
          $(row).find('.form-checkbox-input').attr('disabled', true)
        }
      }
    });
  })

  $('#video-body-list').on('click', '.btn-delete-video', function(e) {
    const $row = $(this).closest('tr');
    const courseId = $row.data('id');
    console.log(courseId);

    Swal.fire({
      title: 'Xác nhận xóa?',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonText: 'Xác nhận',
      cancelButtonText: "Đóng",
    }).then((result) => {
      if (result['isConfirmed']) {
        deleteHotCourse(courseId)
      }
    })
  })

  function deleteHotCourse(courseId) {
    $.ajax({
      url: '/courses/delete-hot/' + courseId,
      method: 'delete',
      data: {
        "_token": $('meta[name="csrf-token"]').attr('content'),
        "id": courseId
      },
      success: function(response) {
        if (response.status) {
          // Cập nhật checkedData
          checkedData = checkedData.filter(course => course.id !== courseId); // Xóa khóa học
          updateVideoBodyList(); // Cập nhật giao diện
          const msgDeleteSuccess = "<?php echo __('course.message.delete_course_success') ?>"
          Swal.fire(msgDeleteSuccess, '', 'success')
        }
      },
      error: function(error) {
        console.error('Có lỗi xảy ra:', error.responseText);
        Swal.fire({
          title: 'Có lỗi xảy ra!',
          text: response.message || 'Vui lòng thử lại.',
          icon: 'error'
        });
      }
    });
  }

  function updateVideoBodyList() {
    let rowHtml = ''
    const currency = 'VND'
    for (let i = 0; i < checkedData.length; i++) {
      let {
        id,
        title,
        description,
        author,
        thumbnail,
        originalPrice,
        saleOffPrice
      } = checkedData[i]
      if (checkedData[i].hasOwnProperty('sale_off_price')) {
        saleOffPrice = new Intl.NumberFormat('en-US', {
          style: 'currency',
          currency: currency,
        }).format(checkedData[i].sale_off_price);
      }
      if (checkedData[i].hasOwnProperty('original_price')) {
        originalPrice = new Intl.NumberFormat('en-US', {
          style: 'currency',
          currency: currency,
        }).format(checkedData[i].original_price);
      }

      rowHtml += '<tr class="row-episode" data-id="' + id + '">' +
        '<td>' +
        title +
        '</td>' +
        '<td>' +
        '<img style="width: 50px" src="' + thumbnail + '" />' +
        '</td>' +
        '<td>' +
        description +
        '</td>' +
        '<td>' +
        originalPrice +
        '</td>' +
        '<td>' +
        saleOffPrice +
        '</td>' +
        '<td>' +
        author +
        '</td>' +
        '<td>' +
        '<i class="fas fa-trash red-icon btn-delete-video"></i>' +
        '</td>' +
        '</tr>'
    }
    $('#video-body-list').empty().html(rowHtml)
    if (checkedData.length === 0) {
      $('.table-result').addClass('hidden')
    } else {
      $('.table-result').removeClass('hidden')
    }
  }
</script>
@stop

@section('css')
<style lang="css">
  .file-container {
    width: 100%;
  }

  .file-upload {
    width: 100%;
    display: flex;
    background-color: #eeeeee;
    border-radius: 15px;
    transition: all 0.3s;
  }

  .file-upload .wrap {
    width: 100%;
    background-color: #f8f8f8;
    padding: 25px;
    margin: 25px;
    border-radius: 10px;
    border: 1px dashed #606060;
    text-align: center;
    cursor: pointer;
  }

  .video-selection {
    width: max-content;
    padding: 0 10px;
    margin: 0 auto;
    border: 1px solid #606060;
    border-radius: 8px;
  }

  #video-body-list tr {
    cursor: move;
  }

  #video-table {
    width: 100% !important;
  }

  .form-checkbox-input {
    width: 1.5rem;
    /* Adjust width */
    height: 1.5rem;
    /* Adjust height */
  }

  .red-icon {
    color: red;
    cursor: pointer;
  }

  .has-selected {
    opacity: 0.5;
  }

  .modal-body-select-video {
    /* max-height: 500px; */
  }

  .table-wrapper {
    max-height: 600px;
    overflow-y: auto;
  }

  .hidden {
    display: none;
  }
</style>
@stop

@section('js')

@stop