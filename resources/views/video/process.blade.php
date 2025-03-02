@extends('adminlte::page')

@section('title', __('video.video_process_management'))

@section('content_header')
<h1>{{__('video.video_process_management')}}</h1>
@stop

@section('css')
@stop

@section('content')
<div class="row">
  <div class="col-12">
    <div class="card">
      <div class="card-body">
        <div class="dataTables_wrapper dt-bootstrap4">
          <table class="table table-bordered" id="process-table">
            <thead>
              <tr>
                <th>{{__('video.id')}}</th>
                <th>{{__('video.title')}}</th>
                <th>{{__('video.created_at')}}</th>
                <th>{{__('video.error_log')}}</th>
                <th>{{__('video.status')}}</th>
                <th>{{__('video.action')}}</th>
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
    const table = $('#process-table').DataTable({
      serverSide: true,
      fixedHeader: true,
      searchDelay: 800,
      ajax: '/video/process/data',
      pageLength: 50,
      order: [
        [2, 'desc']
      ],
      columns: [{
          data: 'id',
          name: 'id',
        },
        {
          data: 'video_id',
          name: 'video_id',
        },
        {
          data: 'created_at',
          name: 'created_at',
        },
        {
          data: 'error_log',
          name: 'error_log',
        },
        {
          data: 'job_status',
          name: 'job_status',
          'className': 'text-center',
          'sortable': false
        },
        {
          data: 'action',
          name: 'action',
          width: '100',
          'sortable': false
        }
      ]
    });
  });
</script>
@stop