@extends('layouts.main',['title'=> trans('app.report_'.request()->segment(2))])

@push('breadcrumb')
    <li><a href="{{route('report.index')}}">@lang('app.'.str_singular(request()->segment(1)).'_page')</a></li>
    <li class="uk-active">@lang('app.'.str_singular(request()->segment(1)).'_'.request()->segment(2))</li>
@endpush

@section('content')
    @component('components.panel-content')
    	@slot('title')
    		<h3 class="uk-panel-title">@lang('app.'.str_singular(request()->segment(1)).'_'.request()->segment(2))</h3>
    	@endslot
        @include('components.alert-danger')
        @include('components.alert-success')
        <form method="POST" action="" class="uk-form uk-margin-top" autocomplete="off">
            {!! csrf_field() !!}
            <ul class="uk-grid uk-grid-small" data-uk-grid-margin>
                <li class="uk-width-1-1 ">
                    <label class="uk-width-small-3-10 uk-inline-middle uk-form-label">@lang('app.report_name')</label>
                    <input name="report_name" type="text" value="{{old('report_name')}}" class="uk-width-large-3-10 uk-width-medium-3-10 uk-width-small-4-10" maxlength="50" />
                </li>
                <li class="uk-width-1-1 ">
                    <label class="uk-width-small-3-10 uk-inline-top uk-form-label">@lang('app.file_type')<span class="uk-text-danger">*</span></label>
                    <div class="uk-width-large-3-10 uk-width-medium-3-10 uk-width-small-4-10 uk-inline-middle">
                        <div id="select-file_type" name="file_type" class=""></div>
                    </div>
                </li>
                <li class="uk-width-1-1 ">
                    <label class="uk-width-small-3-10 uk-inline-top uk-form-label">@lang('app.start_date')<span class="uk-text-danger">*</span></label>
                    <div class="uk-width-large-3-10 uk-width-medium-3-10 uk-width-small-4-10 uk-inline-middle">
                        <div id="input-start_date" name="start_date" class=""></div>
                    </div>
                </li>
                <li class="uk-width-1-1 ">
                    <label class="uk-width-small-3-10 uk-inline-top uk-form-label">@lang('app.end_date')<span class="uk-text-danger">*</span></label>
                    <div class="uk-width-large-3-10 uk-width-medium-3-10 uk-width-small-4-10 uk-inline-middle">
                        <div id="input-end_date" name="end_date" class=""></div>
                    </div>
                </li>
                <li class="uk-width-1-1 ">
                    <label class="uk-width-small-3-10 uk-inline-top uk-form-label">@lang('app.status')<span class="uk-text-danger">*</span></label>
                    <div class="uk-width-large-3-10 uk-width-medium-3-10 uk-width-small-4-10 uk-inline-middle">
                        <div id="select-status" name="message_status" class=""></div>
                    </div>
                </li>
                @if(auth()->user()->is_admin)
                <li class="uk-width-1-1 ">
                    <label class="uk-width-small-3-10 uk-inline-middle uk-form-label">@lang('app.company')</label>
                    <div class="uk-width-large-3-10 uk-width-medium-3-10 uk-width-small-4-10 uk-inline-middle">
                        <div id="input-client_id" name="client_id"></div>
                    </div>
                </li>
                @endif
                <li class="uk-width-1-1 ">
                    <label class="uk-width-small-3-10 uk-inline-top uk-form-label">@lang('app.api_user')<span class="uk-text-danger">*</span></label>
                    <div class="uk-width-large-3-10 uk-width-medium-3-10 uk-width-small-4-10 uk-inline-middle">
                        <div id="input-api_user" name="api_users" class="uk-margin-bottom"></div>
                        <button class="uk-button uk-button-small" type="button" id="button-checkAll">@lang('app.check_all')</button>
                        <button class="uk-button uk-button-small" type="button" id="button-uncheckAll">@lang('app.uncheck_all')</button>
                    </div>
                </li>
                <li class="uk-width-1-1">
                    <div class="uk-width-small-3-10 uk-inline-middle"></div>
                    <div class="uk-width-large-3-10 uk-width-medium-3-10 uk-width-small-4-10 uk-inline-middle uk-text-right uk-form-row uk-margin-large-top">
                        <a href="{{ route('report.index') }}" class="uk-button uk-button-primary">@lang('app.cancel')</a>
                        <button type="submit" class="uk-button uk-button-primary" id="submit">@lang('app.generate')</button>
                    </div>
                </li>
            </ul>
        </form>
	@endcomponent
@endsection

@push('scripts')

<script>
    $(document).ready(function() {
        /**
         * Create UserForm instance
         */
        var options = {
            apiUserUrl: "{{route('apiuser.select',['client_id'=>auth()->user()->client_id])}}",
            roleUrl: "{{route('role.select')}}",
        };
        var reportForm = new ReportForm(options);
        var fileType = new FileType(options);

        @if(auth()->user()->is_admin)
        options.clientUrl = "{{route('client.select')}}";
        var userForm = new UserForm(options);
        userForm.clientInput("{{ old('client_id') ?? auth()->user()->client_id}}");
        userForm.apiUserInput("{{old('api_users')}}");
        @else
        reportForm.apiUserInput("{{old('api_users')}}");
        @endif
        reportForm.datePicker("{{old('start_date')}}","{{old('end_date')}}");
        reportForm.selectStatus("{{old('message_status')}}");
        fileType.init("{{old('file_type')}}");
        reportForm.bindEvent();
    });
</script>
@endpush