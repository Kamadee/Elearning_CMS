<div class="container-fluid">
  <div class="row">
    <div class="col-md-12">
      <div class="card card-primary">
        <div class="card-header">
          @if($permission)
          <h3 class="card-title">{{ __('permission.detail_permission') }} <small>{{ $permission->permission_name }}</small></h3>
          @else
          <h3 class="card-title">{{ __('permission.create_permission') }}</h3>
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
        <form id="form-permission" method="POST" action="{{ $permission ? route('permission.updatePermission', ['id' => $permission->id]) : route('permission.createPermission') }}" enctype="multipart/form-data">
          {{ csrf_field() }}
          <div class="card-body">
            @if($permission)
            <div class="form-group">
              <label for="id">{{ __('permission.id') }}</label>
              <input type="text" value="{{ $permission->id }}" disabled class="form-control" id="id">
            </div>
            @endif
            <div class="form-group">
              <label for="post-name">{{ __('permission.permission_name') }}</label>
              <input type="text" value="{{old('permissionName', $permission ? $permission->permission_name : '')}}" name="permissionName" class="form-control" id="role-name" placeholder="{{ __('permission.form_placeholder.name_placeholder') }}">
            </div>
            <div class="form-group">
              <label for="post-name">{{ __('permission.permission_description') }}</label>
              <input type="text" value="{{old('permissionDescription', $permission ? $permission->description : '')}}" name="permissionDescription" class="form-control" id="role-description" placeholder="{{ __('permission.form_placeholder.description_placeholder') }}">
            </div>
          </div>
          <!-- /.card-body -->
          <div class="card-footer">
            <button class="btn btn-primary btn-submit-permission" type="submit">{{ $permission ? __('permission.update_permission') : __('permission.create_permission') }}</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>