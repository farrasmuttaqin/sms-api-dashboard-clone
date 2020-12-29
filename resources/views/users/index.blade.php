@extends('layouts.main',['title'=> trans('app.user_page')])

@push('breadcrumb')
    <li class="uk-active">@lang('app.user_page')</li>
@endpush

@section('content')
    @component('components.panel-content')
    	@slot('title')
    		<h3 class="uk-panel-title">@lang('app.user_page')</h3>
    	@endslot
        @component('components.search')
            <li class="uk-width-large-1-4 uk-width-medium-1-3 uk-width-small-1-3 ">
                <label class="uk-form-label uk-inline-middle uk-width-large-1-3 uk-width-small-1-1">@lang('app.name')</label>
                <input type="text" class="uk-width-large-6-10  uk-width-small-1-1 uk-width-medium-1-1" name="name">
            </li>
            <li class="uk-width-large-1-4 uk-width-medium-1-3 uk-width-small-1-3 ">
                <label class="uk-form-label uk-inline-middle uk-width-large-1-3 uk-width-small-1-1">@lang('app.email')</label>
                <input type="text" class="uk-width-large-6-10  uk-width-small-1-1 uk-width-medium-1-1" name="email">
            </li>
            <li class="uk-width-large-1-4 uk-width-medium-1-3 uk-width-small-1-3 ">
                <label class="uk-form-label uk-inline-middle uk-width-large-1-3 uk-width-small-1-1">@lang('app.company')</label>
                <div class="uk-width-large-6-10 uk-width-small-1-1 uk-inline-middle  uk-float-right">
                    <div id="input-client_id" name="client_id"></div>
                </div>
            </li>
            <li class="uk-width-large-1-4 uk-width-medium-1-3 uk-width-small-1-3 ">
                <label class="uk-form-label uk-inline-middle uk-width-large-1-3 uk-width-small-1-1">@lang('app.active')</label>
                <div class="uk-width-large-6-10 uk-width-small-1-1 uk-inline-middle uk-float-right">
                    <div id="select-active" name="active"></div>
                </div>
            </li>
        @endcomponent
	@endcomponent

    @include('components.alert-success', ['autoHide' => true])
    @include('components.alert-danger', ['autoHide' => true])

    @component('components.panel-content')


        @can('create',$policies['user'])
            <div class="uk-width-11 uk-margin-bottom">
                <a href="{{route('user.create')}}" class="uk-button uk-button-primary"> @lang('app.user_create')</a>
            </div>
        @endcan
    	<div id="table-user"></div>
	@endcomponent
    @component('components.window-delete')
        <p>Are you sure you want to delete this data ?</p>
    @endcomponent
@endsection

@push('scripts')

<script>
    $(document).ready(function() {

        /**
         * Create UserPage instance
         */
        var options = {
            tableUrl: '{{route('user.table')}}',
            deleteUrl: '{{route('user.index')}}',
            editUrl: '{{route('user.edit',['user'=>''])}}',
            canDelete: '{{ auth()->user()->can('delete', $policies['user'])}}',
            canEdit: '{{ auth()->user()->can('update', $policies['user'])}}',
        };
        var user = new UserPage(options);

        /**
         * Initialize component
         */
        user.table();
        user.initComponent();
        user.bindEvent();


        /**
         * Search Component
         */
        (new SelectActive()).init();

        /**
         * Create UserForm instance
         */
        var options = {
            clientUrl : "{{route('client.select')}}"
        };
        var userForm = new UserForm(options);

        userForm.clientInput();
    });
</script>
@endpush