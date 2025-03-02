@extends('adminlte::page')

@section('title', __('customer.customer_management'))

@section('content_header')
<h1>{{__('customer.customer_management')}}</h1>
@stop

@section('css')
<link rel="stylesheet" href="{{ asset('css/receipt.add.css') }}">
@stop

@section('content')
<div class="row">
  <div class="col-12">
    <div class="card">
      <div class="card-body">
        <div class="dataTables_wrapper dt-bootstrap4">
          <table class="table table-bordered" id="customer-table">
            <thead>
              <tr>
                <th>{{__('customer.id')}}</th>
                <th>{{__('customer.name')}}</th>
                <th>{{__('customer.email')}}</th>
                <th>{{__('customer.phone')}}</th>
                <th>{{__('customer.avatar_2d')}}</th>
                <th>{{__('customer.rank')}}</th>
                <th>{{__('customer.money')}}</th>
                <th class="status">{{__('customer.status')}}</th>
                <th>{{__('customer.created_at')}}</th>
                <th>{{__('customer.action')}}</th>
              </tr>
            </thead>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>
@include('common.loadingSpinner')
@stop

@section('js')
<script>
  $(function() {
    const table = $('#customer-table').DataTable({
      serverSide: true,
      fixedHeader: true,
      searchDelay: 800,
      ajax: {
        url: '/customer/anyData',
        dataSrc: 'data'
      },
      pageLength: 50,
      columns: [{
          data: 'id',
          name: 'id',
        },
        {
          data: 'name',
          name: 'name',
        },
        {
          data: 'email',
          name: 'email',
        },
        {
          data: 'phone',
          name: 'phone',
        },
        {
          data: 'image2D',
          name: 'avatar_2d',
          'sortable': false
        },
        {
          data: 'rank',
          name: 'rank'
        },
        {
          data: 'money',
          name: 'money'
        },
        {
          data: 'customerStatus',
          name: 'status',
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
    });
  });
</script>
@stop