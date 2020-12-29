<header id="header_main">
    <nav class="uk-navbar uk-height-1-1">
        <div class="brand-logo uk-height-1-1">
            <a href="/">
                <img src="{{asset('images/logo/logo.png')}}">
            </a>
        </div>
        @if($user = auth()->user())
        <div class="uk-navbar-profile uk-navbar-flip">
            <ul class="uk-navbar-nav">
                <li data-uk-dropdown="{mode:'click',pos:'bottom-left'}" aria-haspopup="true" aria-expanded="false" class="uk-height-1-1">
                    <a href="#!" class="user_action_image">
                        <img class="md-user-image" src="{{$user->avatarUrl}}" alt="">
                        <p>Welcome, <b>{{ $user->name }}</b></p>
                    </a>
                    <div class="uk-dropdown uk-dropdown-small uk-dropdown-bottom">
                        <ul class="uk-nav js-uk-prevent">
                            <li><a href="{{ route('profile.edit')}}">My profile</a></li>
                            <li>
                                <a href="{{ route('auth.logout') }}" onclick="event.preventDefault();
                                    document.getElementById('logout-form').submit();">
                                Logout
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>
            </ul>
            <form id="logout-form" action="{{ route('auth.logout') }}" method="POST" style="display: none;">
                {{ csrf_field() }}
            </form>
        </div>
        @endif
    </nav>
</header>