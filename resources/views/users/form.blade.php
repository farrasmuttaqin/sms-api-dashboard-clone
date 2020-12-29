@extends('layouts.main',['title'=> trans('app.'.str_singular(request()->segment(1)).'_'.request()->segment(2))])

@push('breadcrumb')
    @if(request()->segment(1) !== 'profile')
        <li><a href="{{route('user.index')}}">@lang('app.user_page')</a></li>
    @endif
    <li class="uk-active">@lang('app.'.str_singular(request()->segment(1)).'_'.request()->segment(2))</li>
@endpush

@section('content')
    @component('components.panel-content')
    	@slot('title')
    		<h3 class="uk-panel-title">@lang('app.'.str_singular(request()->segment(1)).'_'.request()->segment(2))</h3>
    	@endslot
        @include('components.alert-danger', ['autoHide' => false])
        @include('components.alert-success', ['autoHide' => false])
        <form method="POST" action="" class="uk-form uk-margin-top" autocomplete="off" enctype="multipart/form-data">
            {!! csrf_field() !!}
            <ul class="uk-grid uk-grid-small" data-uk-grid-margin>
                <li class="uk-width-1-1 ">
                    <label class="uk-width-small-3-10 uk-inline-middle uk-form-label">@lang('app.name') <span class="uk-text-danger">*</span></label>
                    <input name="name" type="text" value="{{old('name') ?? $data->name ?? '' }}" class="uk-width-large-3-10 uk-width-medium-3-10 uk-width-small-4-10" maxlength="50" required="required">
                </li>
                <li class="uk-width-1-1 ">
                    <label class="uk-width-small-3-10 uk-inline-middle uk-form-label">@lang('app.email') <span class="uk-text-danger">*</span></label>
                    <input name="email" type="text" value="{{old('email') ?? $data->email ?? '' }}" maxlength="100" class="uk-width-large-3-10 uk-width-medium-3-10 uk-width-small-4-10" required="required">
                </li>
                @if(request()->segment(1) !== 'profile')
                    @can('grant', $data ?? $policies['user'])
                    <li class="uk-width-1-1 ">
                        <label class="uk-width-small-3-10 uk-inline-middle uk-form-label">@lang('app.active')</label>
                        <div class="uk-width-large-3-10 uk-width-medium-3-10 uk-width-small-4-10 uk-inline-middle">
                            <input id="input-active-hidden" type="hidden" value="1" name="active">
                            <div id="input-active"></div>
                        </div>
                    </li>
                    <li class="uk-width-1-1 ">
                        <label class="uk-width-small-3-10 uk-inline-middle uk-form-label">{{trans_choice('app.role',2)}} <span class="uk-text-danger">*</span></label>
                        <div class="uk-width-large-3-10 uk-width-medium-3-10 uk-width-small-4-10 uk-inline-middle">
                            <div id="input-roles" name="roles"></div>
                        </div>
                    </li>
                    @endcan
                @endif
                <li class="uk-width-1-1">
                    <label class="uk-width-small-3-10 uk-inline-top uk-form-label">@lang('app.avatar')</label>
                    <div class="uk-width-large-3-10 uk-width-medium-3-10 uk-width-small-4-10 uk-inline-middle">
                        <div class="uk-form-file">
                            <input type="file" name="avatar" id="input-avatar" accept="image/x-png,image/gif,image/jpeg">
                            <button class="uk-button uk-button-small">@lang('app.browse')</button>
                            <span class="file-name uk-text-small">@lang('app.no_file_chosen')</span>
                            <span class="uk-text-small uk-text-muted uk-display-block uk-margin-small-top">@lang('app.image_file'), @lang('app.max_file',['size'=>'500KB'])</span>
                        </div>
                    </div>
                </li>
                @if(isset($data->avatar))
                <li class="uk-width-1-1">
                    <label class="uk-width-small-3-10 uk-inline-top uk-form-label"></label>
                    <div class="uk-width-large-3-10 uk-width-medium-3-10 uk-width-small-4-10 uk-inline-middle">
                        <img src="{{ $data->avatarUrl }}" alt="" width="200" id="image-avatar">
                    </div>
                </li>
                @endif
                
                @if(request()->segment(1) !== 'profile')
                @can('grant', $data ?? $policies['user'])
                <li class="uk-width-1-1 ">
                    <label class="uk-width-small-3-10 uk-inline-middle uk-form-label">@lang('app.company') <span class="uk-text-danger">*</span></label>
                    <div class="uk-width-large-3-10 uk-width-medium-3-10 uk-width-small-4-10 uk-inline-middle">
                        <div id="input-client_id" name="client_id"></div>
                    </div>
                </li>
                <li class="uk-width-1-1 ">
                    <label class="uk-width-small-3-10 uk-inline-top uk-form-label">@lang('app.api_user')</label>
                    <div class="uk-width-large-3-10 uk-width-medium-3-10 uk-width-small-4-10 uk-inline-middle">
                        <div id="input-api_user" name="api_users" class="uk-margin-bottom"></div>
                        <button class="uk-button uk-button-small" type="button" id="button-checkAll">@lang('app.check_all')</button>
                        <button class="uk-button uk-button-small" type="button" id="button-uncheckAll">@lang('app.uncheck_all')</button>
                    </div>
                </li>
                @endcan
                @endif
                @if(request()->segment(1) === 'profile')
                <li class="uk-width-1-1 uk-margin-top">
                    <label class="uk-width-small-3-10 uk-inline-middle uk-form-label">@lang('app.current_password') <span class="uk-text-danger">*</span></label>
                    <input name="current_password" type="password" value="" maxlength="100" class="uk-width-large-3-10 uk-width-medium-3-10 uk-width-small-4-10 input-password"  required="required">
                </li>
                @endif
                @if($editMode = request()->segment(2) === 'edit')
                <li class="uk-width-1-1">
                    <hr class="uk-margin-bottom">
                    <h3>Change Password</h3>
                </li>

                <li>
                    <small class="uk-article-meta">* @lang('app.change_password_info')</small>
                </li>
                @endif
                <li class="uk-width-1-1 uk-margin-top">
                    @if($editMode)
                    <label class="uk-width-small-3-10 uk-inline-middle uk-form-label">@lang('app.new_password')</label>
                    @else
                    <label class="uk-width-small-3-10 uk-inline-middle uk-form-label">@lang('app.password' ) <span class="uk-text-danger">*</span></label>
                    @endif
                    <input name="password" type="password" value="" maxlength="100" class="uk-width-large-3-10 uk-width-medium-3-10 uk-width-small-4-10 input-password" {{ !$editMode ? 'required="required"' : ''}}>
                </li>
                <li class="uk-width-1-1 ">
                    <label class="uk-width-small-3-10 uk-inline-middle uk-form-label">@lang('app.password_confirmation')  @if(!$editMode) <span class="uk-text-danger">*</span>@endif</label>
                    <input name="password_confirmation" type="password" value="" maxlength="100" class="uk-width-large-3-10 uk-width-medium-3-10 input-password uk-width-small-4-10" {{ !$editMode ? 'required="required"' : ''}}>
                </li>
                <li class="uk-width-1-1">
                    <div class="uk-width-small-3-10 uk-inline-middle"></div>
                    <div class="uk-width-large-3-10 uk-width-medium-3-10 uk-width-small-4-10 uk-inline-middle uk-text-right uk-form-row uk-margin-large-top">
                        <a href="{{ url()->previous() }}" class="uk-button uk-button-primary">@lang('app.cancel')</a>
                        <button type="submit" class="uk-button uk-button-primary" id="submit">@lang('app.save')</button>
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
            clientUrl : "{{route('client.select')}}",
            apiUserUrl : "{{route('apiuser.select')}}",
            roleUrl : "{{route('role.select')}}",
        };
        var userForm = new UserForm(options);

        userForm.clientInput("{{ old('client_id') ?: (isset($data) ? $data->client_id : '') }}");
        userForm.activeInput("{{ old('active') !== null  ?old('active'): (isset($data) ? $data->active : '') }}");
        userForm.passwordInput();
        userForm.apiUserInput("{{old('api_users') ?: (isset($data) ? $data->apiUsers->pluck('user_id')->implode(',') : '') }}");
        userForm.roleInput("{{old('roles') ?: (isset($data) ? $data->roles->pluck('role_id')->implode(',') : '') }}");
        userForm.bindEvent();
    });
</script>
@endpush
