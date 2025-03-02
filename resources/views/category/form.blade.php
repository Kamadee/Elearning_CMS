<div class="container-fluid">
  <div class="row">
    <div class="col-md-12">
      <div class="card card-primary">
        <div class="card-header">
          @if($category)
          <h3 class="card-title">{{ __('category.update_category') }}</h3>
          @else
          <h3 class="card-title">{{ __('category.create_category') }}</h3>
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
        <form id="form-user" @if(isset($category)) action="{{ route('category.updateCategory', ['id' => $category->id]) }}"
          @else action="{{ route('category.createCategory') }}"
          @endif method="POST"
          enctype="multipart/form-data">
          {{ csrf_field() }}
          <div class=" card-body">
            @if($category)
            <div class="form-group">
              <label for="id">{{ __('category.id') }}</label>
              <input type="text" value="{{ $category->id }}" disabled class="form-control" id="id">
            </div>
            @endif
            <div class="form-group">
              <label for="category-name">{{ __('category.category_name') }}</label>
              <input type="text" value="{{old('category_name', $category ? $category->category_name : '')}}" name="category_name" class="form-control" id="category-name" placeholder="{{ __('category.form_placeholder.name_placeholder') }}">
            </div>
          </div>
          <div class="card-footer">
            <button class="btn btn-primary btn-submit-admin" type="submit">{{ $category ? __('category.update_category') : __('category.create_category') }}</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
@section('js')
@stop