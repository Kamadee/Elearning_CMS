@extends('adminlte::page')

@section('title', __('course.course_management'))

@section('content_header')
<h1>{{__('course.course_management')}}</h1>
@stop

@section('css')
<link rel="stylesheet" href="{{ asset('css/receipt.add.css') }}">
@stop

@section('content')
<div class="row">
  <div class="col-12">
    <div class="card">
      <div class="card-header">
        <div class="row mb-2">
          <div class="col">
            @if(\App\Helpers\Helper::checkPermission('course.create'))
            <a href="{{ route('courses.create') }}" class="btn btn-primary">
              <i class="fas fa-plus"></i> {{__('course.create_course')}}
            </a>
            @endif
          </div>
        </div>
        <div class="row">
          <div class="col-6 form-group">
            <label>{{ __('course.filter_category') }}</label>
            <select id="course-category-filter" class="select2 form-control" multiple="multiple"
              data-placeholder="{{ __('course.filter_category_placeholder') }}" style="width: 100%;">
              @foreach($categoryList as $category)
              <option value="{{ $category->id }}">{{ $category->category_name }}</option>
              @endforeach
            </select>
          </div>

          <div class="col-6 form-group">
            <label>{{ __('course.filter_status') }}</label>
            <select id="course-status-filter" class="select2 form-control" multiple="multiple"
              data-placeholder="{{ __('course.filter_status_placeholder') }}" style="width: 100%;">
              @foreach($courseStatus as $id => $status)
              <option value="{{ $id }}">{{ __('course.status_list')[$id] }}</option>
              @endforeach
            </select>
          </div>
        </div>
      </div>
    </div>
    <div class="card-body">
      <div class="dataTables_wrapper dt-bootstrap4">
        <table class="table table-bordered" id="course-table">
          <thead>
            <tr>
                <th>{{__('course.id')}}</th>
                <th>{{__('course.title')}}</th>
                <th>{{__('course.banner')}}</th>
                <th>{{__('course.author')}}</th>
                <th>{{__('course.course_category')}}</th>
                <th>{{__('course.course_duration')}}</th>
                <th>{{__('course.original_price')}}</th>
                <th>{{__('course.sale_off_price')}}</th>
                <th class="status">{{__('course.status')}}</th>
                <th>{{__('course.created_at')}}</th>
                <th>{{__('course.action')}}</th>
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
<!-- Swal cdn -->

<script>
  $(function() {
    $('.select2').select2()
    const columnsDef = [{
        data: 'id',
        name: 'id',
        "searchable": true
      },
      {
        data: 'title',
        name: 'title',
        "searchable": true
      },
      {
        data: 'courseBanner',
        name: 'banner',
        "searchable": false,
        'sortable': false
      },
      {
        data: 'author',
        name: 'author',
        "searchable": true
      },
      {
        data: 'courseCategory',
        name: 'course_category',
        'sortable': false,
        "searchable": false
      },
      {
        data: 'course_duration',
        name: 'course_duration',
        'sortable': false,
        "searchable": false
      },
      {
        data: 'originalPrice',
        name: 'original_price',
        'sortable': false,
        "searchable": false
      },
      {
        data: 'saleOffPrice',
        name: 'sale_off_price',
        'sortable': false,
        "searchable": false
      },
      {
        data: 'courseStatus',
        name: 'status',
        'sortable': true
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
    ];

    const table = $('#course-table').DataTable({
      serverSide: true,
      fixedHeader: true,
      searchDelay: 800,
      ajax: '/courses/anyData',
      pageLength: 50,
      columns: columnsDef
    });

    $('#course-category-filter').on('change', function(e) {
      handlerFilter();
    });
    $('#course-status-filter').on('change', function(e) {
      handlerFilter();
    });

    function handlerFilter() {
      $("#course-table").dataTable().fnDestroy();
      const courseCategories = $('#course-category-filter').val();
      const statusList = $('#course-status-filter').val();

      const dataFilter = {
        courseCategories,
        statusList
      }
      $('#course-table').DataTable({
        serverSide: true,
        fixedHeader: true,
        searchDelay: 800,
        ajax: {
          url: '/courses/anyData',
          data: function(d) {
            d.courseCategories = courseCategories;
            d.statusList = statusList;
            console.log("Ajax request data:", d);
          },
          error: function(xhr) {
            console.error("AJAX error:", xhr.responseText);
          }
        },
        pageLength: 50,
        columns: columnsDef
      });
    }

    function handleDelCourse(courseId) {
      $.ajax({
        url: '/courses/delete/' + courseId,
        type: 'delete',
        data: {
          "_token": $('meta[name="csrf-token"]').attr('content'),
          "id": courseId
        },
        success: function(response) {
          if (response.status) {
            console.log(response)
            handlerFilter()
            const msgDeleteSuccess = "<?php echo __('course.message.delete_course_success') ?>"
            Swal.fire(msgDeleteSuccess, '', 'success')
          } else {
            Swal.fire('fail!', response.message, '')
          }
        }
      });
    }

    $('#course-table').on('click', '.btn-delete-course', function(e) {
      e.preventDefault();
      const id = $(this).data('id')
      console.log(id);

      const name = $(this).data('name') // video_id
      const msgConfirmDelete = "<?php echo __('course.message.delete_course_confirm_js'); ?>" + ' ' + name + ' ?'
      Swal.fire({
        title: msgConfirmDelete,
        showDenyButton: false,
        showCancelButton: true,
        confirmButtonText: "<?php echo __('course.btn_confirm'); ?>",
        cancelButtonText: "<?php echo __('course.btn_cancel'); ?>",
      }).then((result) => {
        /* Read more about isConfirmed, isDenied below */
        if (result.isConfirmed) {
          handleDelCourse(id)
        } else if (result.isDenied) {
          Swal.fire('Changes are not saved', '', 'info')
        }
      })
    });

  });
</script>
@stop