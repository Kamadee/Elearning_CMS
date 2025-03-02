<div class="container-fluid">
  <div class="row">
    <div class="col-md-12">
      <div class="card card-primary">
        <div class="card-header">
          @if($customer->first_name && $customer->last_name)
          <h3 class="card-title">{{ __('customer.detail_customer') }}
            <small>{{ $customer->first_name . ' ' . $customer->last_name }}</small>
          </h3>
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
        <form id="form-user" method="POST" action="{{ route('customer.updateCustomer', ['id' => $customer->id]) }}"
          enctype="multipart/form-data">
          {{ csrf_field() }}
          <div class=" card-body">
            @if($customer)
            <div class="form-group">
              <label for="id">{{ __('customer.id') }}</label>
              <input type="text" value="{{ $customer->id }}" disabled class="form-control" id="id">
            </div>
            @endif
            <div class="form-group">
              <label for="post-name">{{ __('customer.first_name') }}</label>
              <input type="text" value="{{old('first_name', $customer ? $customer->first_name : '')}}" name="first_name" class="form-control" id="firstname" placeholder="{{ __('customer.form_placeholder.first_name') }}">
            </div>
            <div class="form-group">
              <label for="post-name">{{ __('customer.last_name') }}</label>
              <input type="text" value="{{old('last_name', $customer ? $customer->last_name : '')}}" name="last_name" class="form-control" id="lastname" placeholder="{{ __('customer.form_placeholder.last_name') }}">
            </div>
            <div class="form-group">
              <label for="customer-email">{{ __('customer.email') }}</label>
              <input type="text" value="{{old('email', $customer ? $customer->email : '')}}" name="email" class="form-control" id="customer-email" disabled placeholder="{{ __('customer.form_placeholder.email_placeholder') }}">
            </div>
            <div class="form-group">
              <label for="customer-phone">{{ __('customer.phone') }}</label>
              <input type="text" value="{{old('phone', $customer ? $customer->phone : '')}}" name="phone" class="form-control" id="customer-phone" disabled placeholder="{{ __('customer.form_placeholder.phone_placeholder') }}">
            </div>
            <div class="form-group">
              <label for="customer-avatar-3d">{{ __('customer.avatar_2d') }}</label>
              <input type="text" value="{{old('avatar2d', $customer ? $customer->avatar_2d : '')}}" name="avatar2d" disabled class="form-control" id="customer-avatar-2d" placeholder="{{ __('customer.form_placeholder.avatar_2d_placeholder') }}">
            </div>
            <div class="form-group">
              <label for="customer-rank">{{ __('customer.rank') }}</label>
              <input type="text" value="{{old('rank', $customer ? $customer->rank : '')}}" name="rank" class="form-control" id="customer-rank" placeholder="{{ __('customer.form_placeholder.rank_placeholder') }}">
            </div>
            <div class="form-group">
              <label for="customer-money-1">{{ __('customer.money') }}</label>
              <input type="text" value="{{old('money', $customer ? $customer->money : '')}}" name="moneyFormat" disabled class="form-control number-separator" placeholder="{{ __('customer.form_placeholder.money_placeholder') }}">
              <input type="hidden" value="{{old('money', $customer ? $customer->money : '')}}" name="money" class="form-control" id="customer-money">
            </div>
            <!--  -->
            <div class="form-group">
              <label for="customer-status">{{ __('customer.status') }}</label>
              <select id="customer-status" class="select2 form-control" disabled name="status" data-placeholder="{{ __('customer.status_placeholder') }}" style="width: 100%;">
                @foreach($customerStatus as $id => $status)
                <option <?php if ($customer && $customer->status == $id) {
                          echo 'selected';
                        } ?> value="{{ $id }}">{{ __('customer.status_list')[$id] }}</option>
                @endforeach
              </select>
            </div>
          </div>
          <!-- /.card-body -->
          <div class="card-footer">
            <button class="btn btn-primary btn-submit-admin" type="submit">Submit</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>