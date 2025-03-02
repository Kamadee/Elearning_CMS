<div class="container-fluid">
  <div class="row">
    <div class="col-md-12">
      <div class="card card-primary">
        <div class="card-header">
          @if($tag)
          <h3 class="card-title">{{ __('tag.update_tag') }}</h3>
          @else
          <h3 class="card-title">{{ __('tag.create_tag') }}</h3>
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
        <form id="form-user" @if(isset($tag)) action="{{ route('tag.updateTag', ['id' => $tag->id]) }}"
          @else action="{{ route('tag.createTag') }}"
          @endif method="POST"
          enctype="multipart/form-data">
          {{ csrf_field() }}
          <div class=" card-body">
            @if($tag)
            <div class="form-group">
              <label for="id">{{ __('tag.id') }}</label>
              <input type="text" value="{{ $tag->id }}" disabled class="form-control" id="id">
            </div>
            @endif
            <div class="form-group">
              <label for="tag-name">{{ __('tag.tag_name') }}</label>
              <input type="text" value="{{old('tag_name', $tag ? $tag->tag_name : '')}}" name="tag_name" class="form-control" id="tag-name" placeholder="{{ __('tag.form_placeholder.name_placeholder') }}">
            </div>
          </div>
          <div class="card-footer">
            <button class="btn btn-primary btn-submit-admin" type="submit">{{ $tag ? __('tag.update_tag') : __('tag.create_tag') }}</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
@section('js')
<!-- CSS jquery -->
<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet">
<!-- <link href="https://cdnjs.cloudflare.com/ajax/libs/select2-bootstrap4-theme/1.0.0/select2-bootstrap4.min.css" rel="stylesheet"> -->
<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<!-- Select 2 -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
<!-- DataTables -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
@stop