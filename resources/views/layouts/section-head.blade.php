<div class="section-bar">
    <div class="uk-grid">
        <div class="uk-width-1-1">
            <a id="btn-swap" class="uk-margin-right uk-icon-reorder" href="#"></a>
            <div class="uk-float-right uk-text-small">
                <span>@lang('app.version',['value' => config('app.version')])</span>
            </div>
        </div>
        <div class="uk-width-1-1 uk-margin-small-top">
            <ul class="uk-breadcrumb uk-text-small">
                @if(request()->segment(1) === null)
                  <li class="uk-active">@lang('app.home_page')</li>
                @else
                  <li><a href="/">@lang('app.home_page')</a></li>
                @endif
                @stack('breadcrumb')
            </ul>
        </div>
    </div>
</div>