<div class="container-fluid">
  <div class="row">
    <div class="col-md-12">
      <div class="card card-primary">
        <div class="card-header">
          @if($course)
          <h3 class="card-title">{{ __('course.detail_course') }} <small>{{ $course->title }}</small></h3>
          @else
          <h3 class="card-title">{{ __('course.create_course') }}</h3>
          @endif
        </div>
        <!-- /.card-header -->
        <!-- form start -->
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
        <form id="form-post" method="POST" action="{{ $course ? route('courses.update', ['id' => $course->id])
         : route('courses.createCourse') }}"
          enctype="multipart/form-data">
          {{ csrf_field() }}
          <div class="card-body">
            @if($course)
            <div class="form-group">
              <label for="id">{{ __('course.id') }}</label>
              <input type="text" value="{{ $course->id }}" disabled class="form-control" id="id">
            </div>
            @endif
            <div class="form-group">
              <label for="post-name">{{ __('course.name') }}</label>
              <input type="text" value="{{old('title', $course ? $course->title : '')}}" name="title" class="form-control" id="course-name" placeholder="{{ __('course.form_placeholder.name_placeholder') }}">
            </div>

            <div class="form-group">
              <label for="room-description">{{ __('course.description') }}</label>
              <textarea name="description" cols="20" rows="5" class="form-control" placeholder="{{ __('course.form_placeholder.description_placeholder') }}">{{old('description', $course ? $course->description : '')}}</textarea>

            </div>

            <div class="col-6 form-group">
              <label for="room-name">{{ __('course.course_category') }}</label>
              <select id="category-filter" name="courseCategories[]" class="select2 form-control" multiple="multiple"
                data-placeholder="{{ __('course.filter_category_placeholder') }}" style="width: 100%;">
                <?php $arrChecked = old('courseCategories', $course ? $course->courseCategories->pluck('id')->all() : []) ?>
                @foreach($categoryList as $category)
                <option
                  <?php
                  if (in_array($category->id, $arrChecked)) {
                    echo 'selected';
                  } ?> value="{{ $category->id }}">{{ $category->category_name }}
                </option>
                @endforeach
              </select>
            </div>

            <div class="form-group">
              <label for="room-name">{{ __('course.original_price') }}</label>
              <input value="{{old('originalPrice', $course ? \App\Helpers\Helper::convertMoney($course->original_price) : '')}}" id="originalPrice" name="originalPrice" type="text" class="form-control" onkeyup="onlyNumberAmount(event)">
     
            </div>

            <div class="form-group">
              <label for="room-name">{{ __('course.sale_off_price') }}</label>
              <input value="{{old('saleOffPrice', $course ? \App\Helpers\Helper::convertMoney($course->sale_off_price) : '')}}" id="saleOffPrice" name="saleOffPrice" type="text" class="form-control" onkeyup="onlyNumberAmount(event)">
        
            </div>

            <div class="form-group">
              <label for="room-name">{{ __('course.course_duration') }}</label>
              <input id="courseDuration" value="{{old('courseDuration', $course ? $course->course_duration : '')}}" name="courseDuration" type="text" class="form-control">
      
            </div>

            <input type="hidden" name="video-list[]" class="input-video-list" />
            <div class="form-group">
              <label for="room-name">{{ __('course.video_selection') }}</label>
              <div>
                <div data-toggle="modal" data-target="#modal-select-video">
                  <div class="box">
                    <label for="fileUpload-1" class="file-upload">
                      <div class="wrap">
                        <div id="video-selection">Chọn video</div>
                      </div>
                    </label>
                  </div>
                </div>
              </div>
              <div class="table table-wrapper">
                <table class="table table-bordered w-full table-result">
                  <thead>
                    <tr>
                      <th style="width: 250px">Tiêu đề tập</th>
                      <th style="width: 40px">Ảnh nhỏ</th>
                      <th>Mô tả nội dung</th>
                      <th style="width: 40px">Xem preview</th>
                      <th style="width: 40px">Action</th>
                    </tr>
                  </thead>
                  <tbody id="video-body-list">
                  </tbody>
                </table>
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
                                <th>{{__('video.id')}}</th>
                                <th>{{__('video.title')}}</th>
                                <th>{{__('video.videoThumbnail')}}</th>
                                <th>{{__('video.created_at')}}</th>
                                <th>{{__('video.action')}}</th>
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
                      <div class="custom-body-content">
                      </div>
                    </div>
                    <div class="modal-footer">
                      <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    </div>
                  </div>
                </div>
              </div>
            </div>

              <div class="form-group">
                <label for="room-name">{{ __('course.course_tag') }}</label>
                <select id="tag-filter" name="courseTags[]" class="select2 form-control" multiple="multiple"
                  data-placeholder="{{ __('course.form_placeholder.tag_placeholder') }}" style="width: 100%;">
                  <?php $arrChecked = old('courseTags', $course ? $course->courseTags->pluck('id')->all() : []) ?>
                  @foreach($tagList as $tag)
                  <option
                    <?php
                    if (in_array($tag->id, $arrChecked)) {
                      echo 'selected';
                    } ?> value="{{ $tag->id }}">{{ $tag->tag_name }}
                  </option>
                  @endforeach
                </select>
              </div>

              <div class="form-group">
                <label for="room-name">{{ __('course.status') }}</label>
                <select id="status-filter" name="status" class="select2 form-control"
                  data-placeholder="{{ __('post.status_placeholder') }}" style="width: 100%;">
                  @foreach($courseStatus as $id => $status)
                  <option <?php if ($course && $course->status == $id) {
                            echo 'selected';
                          } ?> value="{{ $id }}">{{ __('course.status_list')[$id] }}</option>
                  @endforeach
                </select>
              </div>

              <div class="form-group">
                <label for="room-name">{{ __('course.thumbnail') }}</label>
                <div class="file-loading">
                  <input id="input-pd" name="input-pd[]" type="file">
                </div>
              </div>

              <div class="form-group">
                <label for="room-name">{{ __('course.banner') }}</label>
                <div class="file-loading">
                  <input id="input-banner-pd" name="input-banner-pd[]" type="file">
                </div>
              </div>

              <div class="form-group">
                <label for="post-content">{{ __('course.content') }}</label>
                <div class="row">
                  <div class="col-md-12">
                    <div class="card card-outline card-info">
                      <div class="card-header">
                        <h3 class="card-title">
                          {{ __('course.ck_editor_header') }}
                        </h3>
                      </div>
                      <!-- /.card-header -->
                      <div class="card-body">
                        <textarea id="content" class="ckeditor" name="content">{{old('content', $course ? $course->content : '')}}</textarea>
                      </div>
                    </div>
                  </div>
                  <!-- /.col-->
                </div>
              </div>

              <div class="form-group">
                <label for="post-name">Tên tác giả</label>
                <input type="text" value="{{old('title', $course ? $course->author : '')}}" name="author" class="form-control" id="author" placeholder="Nhập tên tác giả">
       
              </div>

              <div class="form-group">
                <label for="post-content">Giới thiệu về tác giả</label>
                <div class="row">
                  <div class="col-md-12">
                    <div class="card card-outline card-info">
                      <div class="card-header">
                        <h3 class="card-title">
                          {{ __('course.ck_editor_header') }}
                        </h3>
                      </div>
                      <!-- /.card-header -->
                      <div class="card-body">
                        <textarea id="authorDescription" class="ckeditor" name="authorDescription">{{old('authorDescription', $course ? $course->authorDescription : '')}}</textarea>
                      </div>
                    </div>
                  </div>
                  <!-- /.col-->
                </div>
              </div>
              <!-- /.card-body -->
              <div class="card-footer">
                <button class="btn btn-primary btn-submit-course" type="submit">{{ $course ? __('course.update_course') : __('course.create_course') }}</button>
              </div>
        </form>
      </div>
    </div>
  </div>
</div>
<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
<script>
  $(document).ready(function() {
    var thumbnailUrl = "{{ $course && $course->thumbnail ? $course->thumbnail : null }}";
    var bannerUrl = "{{ $course && $course->banner ? $course->banner : null }}";

    if (thumbnailUrl) {
      $("#input-pd").fileinput({
        initialPreview: [
          thumbnailUrl
        ],
        initialPreviewAsData: true, // Hiển thị ảnh từ URL
        showUpload: false, // Ẩn nút upload
        previewFileType: 'any' // Loại file để xem trước
      });
    } else {
      $("#input-pd").fileinput({
        showUpload: false,
        previewFileType: 'any'
      });
    }
    if (bannerUrl) {
      $("#input-banner-pd").fileinput({
        initialPreview: [
          bannerUrl
        ],
        initialPreviewAsData: true, // Hiển thị ảnh từ URL
        showUpload: false, // Ẩn nút upload
        previewFileType: 'any' // Loại file để xem trước
      });
    } else {
      $("#input-banner-pd").fileinput({
        showUpload: false,
        previewFileType: 'any'
      });
    }
    $('.select2').select2()
    // $.fn.fileinputBsVersion = "3.3.7";
    // // input-file thumbnail
    // $("#input-pd").fileinput();

    // // with plugin options
    // $("#input-pd").fileinput({
    //   'showUpload': false,
    //   'previewFileType': 'any'
    // });

    // // input-file banner
    // $("#input-banner-pd").fileinput();

    // // with plugin options
    // $("#input-banner-pd").fileinput({
    //   'showUpload': false,
    //   'previewFileType': 'any'
    // });
  });
</script>
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
</style>