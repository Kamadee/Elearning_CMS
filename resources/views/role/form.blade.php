<div class="container-fluid">
  <div class="row">
    <div class="col-md-12">
      <div class="card card-primary">
        <div class="card-header">
          @if($role)
          <h3 class="card-title">{{ __('role.detail_role') }} <small>{{ $role->role_name }}</small></h3>
          @else
          <h3 class="card-title">{{ __('role.create_role') }}</h3>
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
        <form id="form-role" method="POST" action="{{ $role ? route('role.updateRole', ['id' => $role->id]) : route('role.createRole') }}" enctype="multipart/form-data">
          {{ csrf_field() }}
          <div class="card-body">
            @if($role)
            <div class="form-group">
              <label for="id">{{ __('role.id') }}</label>
              <input type="text" value="{{ $role->id }}" disabled class="form-control" id="id">
            </div>
            @endif
            <div class="form-group">
              <label for="post-name">{{ __('role.role_name') }}</label>
              <input type="text" value="{{old('roleName', $role ? $role->role_name : '')}}" name="roleName" class="form-control" id="role-name" placeholder="{{ __('role.form_placeholder.name_placeholder') }}">
            </div>
            <div class="form-group">
              <label for="post-name">{{ __('role.role_description') }}</label>
              <input type="text" value="{{old('roleDescription', $role ? $role->description : '')}}" name="roleDescription" class="form-control" id="role-description" placeholder="{{ __('role.form_placeholder.description_placeholder') }}">
            </div>
            <div class="form-group">
              <label for="post-name">{{ __('role.permission_list') }}</label>
              <input type="hidden" class="permission-selected" name="permissionSelected">
              <table class="table table-bordered" id="role-table">
                <thead>
                  <tr>
                    <th>{{__('role.group_permision')}}</th>
                    <th>{{__('role.permision')}}</th>
                  </tr>
                </thead>
                <tbody>
                  <?php
                  $permissionCheckedList = [];
                  if ($role) {
                    $permissionCheckedList = $role->permissions ? $role->permissions->pluck('id')->toArray() : [];
                  }
                  ?>
                  @foreach($permissionList as $group => $permissions)
                  <tr>
                    <td>{{ $group }}</td>
                    <td>
                      @foreach($permissions as $permission)
                      <div class="form-check form-check-inline">
                        <input data-groupPermission="{{ $group }}" class="form-check-input permission-selection" type="checkbox"
                          <?php if (in_array($permission->id, $permissionCheckedList)) {
                            echo "checked";
                          } ?> value="{{ $permission->id }}">
                        <label class="form-check-label" for="inlineCheckbox1">{{ $permission->permission_name }}</label>
                      </div>
                      @endforeach
                    </td>
                  </tr>
                  @endforeach
                </tbody>
              </table>
            </div>
          </div>
          <!-- /.card-body -->
          <div class="card-footer">
            <button class="btn btn-primary btn-submit-role" type="button">{{ $role ? __('role.update_role') : __('role.create_role') }}</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>