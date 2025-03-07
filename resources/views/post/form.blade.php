<div class="container-fluid">
  <div class="row">
    <div class="col-md-12">
      <div class="card card-primary">
        <div class="card-header">
          @if($post)
          <h3 class="card-title">{{ __('post.update_post') }}</h3>
          @else
          <h3 class="card-title">{{ __('post.create_post') }}</h3>
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
        <form id="form-user" @if(isset($post)) action="{{ route('posts.update', ['id' => $post->id]) }}"
          @else action="{{ route('posts.createPost') }}"
          @endif method="POST"
          enctype="multipart/form-data">
          {{ csrf_field() }}
          <div class=" card-body">
            @if($post)
            <div class="form-group">
              <label for="id">{{ __('post.id') }}</label>
              <input type="text" value="{{ $post->id }}" disabled class="form-control" id="id">
            </div>
            @endif
            <div class="form-group">
              <label for="post-name">{{ __('post.title') }}</label>
              <input type="text" value="{{old('title', $post ? $post->title : '')}}" name="title" class="form-control" id="title" placeholder="{{ __('post.form_placeholder.name_placeholder') }}">
            </div>
            <div class="form-group">
              <label for="post-name">{{ __('post.description') }}</label>
              <textarea name="description" cols="20" rows="5" class="form-control" placeholder="{{ __('post.form_placeholder.description_placeholder') }}">{{old('description', $post ? $post->description : '')}}</textarea>
            </div>

            <div class="col-6 form-group">
              <label>{{ __('post.post_category') }}</label>
              <select id="post-category-filter" name="postCategories[]" class="select2 form-control" multiple="multiple"
                data-placeholder="{{ __('post.form_placeholder.category_placeholder') }}" style="width: 100%;">
                <?php $arrChecked = old('postCategories', $post ? $post->postCategories->pluck('id')->all() : []) ?>
                @foreach($categoryList as $category)
                <option
                  <?php if (in_array($category->id, $arrChecked)) {
                    echo 'selected';
                  } ?>
                  value="{{ $category->id }}">{{ $category->category_name }}</option>
                @endforeach
              </select>
            </div>

            <div class="col-6 form-group">
              <label>{{ __('post.post_tag') }}</label>
              <select id="post-tag-filter" name="postTags[]" class="select2 form-control" multiple="multiple"
                data-placeholder="{{ __('post.form_placeholder.tag_placeholder') }}" style="width: 100%;">
                <?php $arrChecked = old('postTags', $post ? $post->postTags->pluck('id')->all() : []) ?>
                @foreach($tagList as $tag)
                <option
                  <?php if (in_array($tag->id, $arrChecked)) {
                    echo 'selected';
                  } ?>
                  value="{{ $tag->id }}">{{ $tag->tag_name }}</option>
                @endforeach
              </select>
            </div>

            <div class="col-6 form-group">
              <label>{{ __('post.status') }}</label>
              <select id="post-status-filter" name="status" class="select2 form-control"
                data-placeholder="{{ __('post.form_placeholder.status_placeholder') }}" style="width: 100%;">
                @foreach($postStatus as $id => $status)
                <option
                  <?php if ($post && $post->status == $id) {
                    echo 'selected';
                  } ?>
                  value="{{ $id }}">{{ __('post.status_list')[$id] }}</option>
                @endforeach
              </select>
            </div>

            <div class="form-group">
              <label>{{ __('post.thumbnail') }}</label>
              <div class="file-loading">
                <input id="input-pd" name="input-pd[]" type="file" class="file" data-preview-file-type="text">
              </div>
            </div>

            <div class="form-group">
              <label for="post-content">{{ __('post.content') }}</label>
              <div class="row">
                <div class="col-md-12">
                  <div class="card card-outline card-info">
                    <div class="card-header">
                      <h3 class="card-title">
                        {{ __('post.ck_editor_header') }}
                      </h3>
                    </div>
                    <!-- /.card-header -->
                    <div class="card-body">
                      <textarea id="content" class="ckeditor" name="content">{{old('content', $post ? $post->content : '')}}</textarea>
                    </div>
                  </div>
                </div>
                <!-- /.col-->
              </div>
            </div>
          </div>
          <!-- /.card-body -->
          <div class="card-footer">
            <button class="btn btn-primary btn-submit-admin" type="submit">{{ $post ? __('post.update_post') : __('post.create_post') }}</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
<script src="{{ asset('ckeditor/ckeditor.js') }}"></script>
<script>
  $(document).ready(function() {
    $('.select2').select2()

    function handleDelThumbnail(postId) {
      $.ajax({
        url: '/posts/delete-img/' + postId,
        type: 'post',
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

    var thumbnailUrl = "{{ $post && $post->thumbnail ? $post->thumbnail : null }}";

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
    $("#input-pd").on('click', '.fileinput-remove', function(event, key, jqXHR, data) {
      console.log("File đã bị xóa:", key);
      var postId = $('#id').val();
      handleDelThumbnail(postId)
    });
    // file input
    $.fn.fileinputBsVersion = "3.3.7";

    $("#input-pd").fileinput();

    // with plugin options
    $("#input-pd").fileinput({
      'showUpload': false,
      'previewFileType': 'any'
    });
  });
</script>