<aside id="sidebar">
    <div class="sidebar_header">
        <span class="uk-text-contrast uk-text-large">Menu</span>
        <div class="uk-clearfix"></div>
    </div>
    <div class="sidebar_menu">
        <ul class="uk-nav uk-nav-parent-icon" data-uk-nav>
            <li class="{{request()->segment(1) === null ? 'uk-active' : ''}}">
                <a href="/" class="uk-icon-home"> @lang('app.home_page')</a>
            </li>
            @can('index', $policies['report'])
            <li class="{{ request()->segment(1) === 'reports' ? 'uk-active' : ''}}">
                <a href="{{route('report.index')}}" class="uk-icon-file"> @lang('app.report_page')</a>
            </li>
            @endcan
            @can('index', $policies['user'])
            <li class="{{ request()->segment(1) === 'users' ? 'uk-active' : ''}}">
                <a href="{{route('user.index')}}" class="uk-icon-users"> @lang('app.user_page')</a>
            </li>
            @endcan
        </ul>
    </div>
</aside>
