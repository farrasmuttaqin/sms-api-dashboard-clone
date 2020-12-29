@if(isset($errors) && $errors->any())
	<div class="uk-alert uk-alert-danger uk-text-left uk-text-small {{ isset($autoHide) && $autoHide ? 'uk-alert-hide' : '' }}">
	    <ul class='uk-list'>
	        @foreach ($errors->all() as $error)
	        	<li><span class="uk-icon-warning"></span> {{ $error }}</li>
	        @endforeach
	    </ul>
	</div>
@elseif($danger = session('alert-danger'))
	<div class="uk-alert uk-alert-danger uk-text-left uk-text-small {{ isset($autoHide) && $autoHide ? 'uk-alert-hide' : '' }}">
		@if(is_array($danger))
		    <ul class='uk-list'>
		        @foreach ($danger as $state)
		        	<li><span class="uk-icon-warning"></span> {{ $state }}</li>
		        @endforeach
	    	</ul>
		@else
			{{ $danger }}
		@endif
	</div>
@endif