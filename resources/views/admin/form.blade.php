<div class="container-fluid">
  <div class="row">
    <div class="col-md-12">
      <div class="card card-primary">
        <div class="card-header">
          @if($user)
          <h3 class="card-title">{{ __('admin.username') }} <small>{{ $user->username }}</small></h3>
          @else
          <h3 class="card-title">{{ __('admin.create_user') }}</h3>
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
        <form id="form-user" method="POST" action="{{ $user ? route('admin.updateAccount', ['id' => $user->id]) : route('admin.createNewAccount') }}" enctype="multipart/form-data">
          {{ csrf_field() }}
          <div class="card-body">
            @if($user)
            <div class="form-group">
              <label for="id">{{ __('admin.id') }}</label>
              <input type="text" value="{{ $user->id }}" disabled class="form-control" id="id">
            </div>
            @endif
            <div class="form-group">
              <label for="post-name">{{ __('admin.username') }}</label>
              <input type="text" value="{{old('username', $user ? $user->username : '')}}" name="username" class="form-control" id="username" placeholder="{{ __('role.form_placeholder.name_placeholder') }}">
            </div>
            <div class="form-group">
              <label for="post-name">{{ __('admin.password') }}</label>
              <input type="password" name="password" class="form-control" id="password" placeholder="{{ __('admin.form_placeholder.password_placeholder') }}">
            </div>
            <div class="form-group">
              <label for="post-name">{{ __('admin.repassword') }}</label>
              <input type="password" name="repassword" class="form-control" id="repassword" placeholder="{{ __('admin.form_placeholder.repassword_placeholder') }}">
            </div>
            <!-- ------------------------------ -->
            <div class="form-group">
              <label>{{ __('admin.role_name') }}</label>
              <select id="admin-role-filter" name="roleSelected[]" class="select2 form-control" multiple="multiple"
                data-placeholder="{{ __('admin.role_selected') }}" style="width: 100%;">
                <?php $arrChecked = old('roles', $user ? $user->roles->pluck('id')->all() : []) ?>
                @foreach($roleList as $role)
                <option
                  <?php if (in_array($role->id, $arrChecked)) {
                    echo 'selected';
                  } ?>
                  value="{{ $role->id }}">{{ $role->role_name }}</option>
                @endforeach
              </select>
            </div>
            <!-- ------------------------------ -->

          </div>
          <!-- /.card-body -->
          <div class="card-footer">
            <button class="btn btn-primary btn-submit-admin" type="submit">{{ $user ? __('role.update_role') : __('role.create_role') }}</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<script>
  $('.select2').select2()
</script>