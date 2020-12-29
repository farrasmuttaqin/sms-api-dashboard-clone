@extends('layouts.main',['title'=> trans('app.report_page')])

@push('breadcrumb')
    <li class="uk-active">@lang('app.report_page')</li>
@endpush

@section('content')
  @component('components.panel-content')
  	@slot('title')
  		<h3 class="uk-panel-title">@lang('app.report_page')</h3>
  	@endslot
      @component('components.search')
          <li class="uk-width-large-1-4 uk-width-medium-1-3 uk-width-small-1-3 ">
              <label class="uk-form-label uk-inline-top uk-width-large-1-3 uk-width-small-1-1">@lang('app.report_name')</label>
              <input type="text" class="uk-width-large-6-10  uk-width-small-1-1 uk-width-medium-1-1" name="report_name">
          </li>
          <li class="uk-width-large-1-4 uk-width-medium-1-3 uk-width-small-1-3 ">
              <label class="uk-form-label uk-inline-top uk-width-large-1-3 uk-width-small-1-1">@lang('app.status')</label>
              <input type="text" class="uk-width-large-6-10  uk-width-small-1-1 uk-width-medium-1-1" name="message_status">
          </li>
          @can('grant', $policies['user'])
          <li class="uk-width-large-1-4 uk-width-medium-1-3 uk-width-small-1-3 ">
              <label class="uk-form-label uk-inline-top uk-width-large-1-3 uk-width-small-1-1">@lang('app.company')</label>
              <div class="uk-width-large-6-10 uk-width-medium-1-1 uk-width-small-1-1 uk-inline-top">
                  <div id="input-client_id" name="client_id"></div>
              </div>
          </li>
          @endcan
          <li class="uk-width-large-1-4 uk-width-medium-1-3 uk-width-small-1-3 ">
              <label class="uk-form-label uk-inline-top uk-width-large-1-3 uk-width-small-1-1">@lang('app.api_user')</label>
              <div class="uk-width-large-6-10 uk-width-small-1-1 uk-width-medium-1-1 uk-inline-top">
                   <div id="input-api_user" name="api_user" class="uk-margin-bottom"></div>
              </div>
          </li>
          <li class="uk-width-large-1-4 uk-width-medium-1-3 uk-width-small-1-3 ">
              <label class="uk-form-label uk-inline-top uk-width-large-1-3 uk-width-small-1-1">@lang('app.file_type')</label>
              <div class="uk-width-large-6-10 uk-width-small-1-1 uk-width-medium-1-1 uk-inline-top">
                  <div id="select-file_type" name="file_type"></div>
              </div>
          </li>
      @endcomponent
	@endcomponent

  @include('components.alert-success', ['autoHide' => true])
  @include('components.alert-danger', ['autoHide' => true])

  @component('components.panel-content')
      @can('generate', $policies['report'])
          <div class="uk-width-11 uk-margin-bottom">
              <a href="{{route('report.create')}}" class="uk-button uk-button-primary"> @lang('app.report_create')</a>
          </div>
      @endcan
  	<div id="table-report"></div>
	@endcomponent
  @component('components.window-delete')
      <p>@lang('app.delete_confirmation')</p>
  @endcomponent

  @component('components.window-delete', ['id' => 'window-cancel', 'title' => trans('app.cancel_report'), 'submitText' => trans('app.cancel')])
      <p>@lang('app.cancel_confirmation')</p>
  @endcomponent
@endsection

@push('scripts')

<script>
    $(document).ready(function() {

        /**
         * Create UserPage instance
         */
        var options = {
            tableUrl: '{{route('report.table')}}',
            processingUrl: '{{route('report.processing')}}',
            cancelUrl: '{{route('report.cancel')}}',
            isSearching: true,
            deleteUrl: '{{route('report.index')}}',
            regenerateUrl: '{{route('report.regenerate',['report' => ''])}}',
            canDelete: '{{ auth()->user()->can('delete', $policies['report'])}}',
            canDownload: '{{ auth()->user()->can('download', $policies['report'])}}',
            local: {
            	all : '{{trans('app.all')}}',
              success_cancel: '{{trans('app.success_request_cancel')}}',
              failed_cancel: '{{trans('app.failed_cancel')}}',
              success_delete: '{{trans('app.success_delete')}}',
              failed_delete: '{{trans('app.failed_delete')}}',
            }
        };
        var report = new ReportPage(options);
        var fileType = new FileType(options);
        /**
         * Initialize component
         */
        report.table();
        report.initComponent();
        report.bindEvent();
        fileType.init();

        /**
         * Search Component
         */
        (new SelectActive()).init();

        /**
         * Create UserForm instance
         */
        var options = {
            clientUrl : "{{route('client.select')}}",
            apiUserUrl: "{{route('apiuser.all')}}",
        };
        var userForm = new UserForm(options);
        var reportForm = new ReportForm(options);
        userForm.clientInput();
        reportForm.apiUserDropdown();
    });
</script>
@endpush
