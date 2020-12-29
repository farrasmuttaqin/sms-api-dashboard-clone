@if(isset($status) || $status = session('alert-success'))
	<div class="uk-alert uk-alert-success uk-text-left uk-text-small {{ isset($autoHide) && $autoHide ? 'uk-alert-hide' : '' }}" data-uk-alert>
		<a href="#!" class="uk-alert-close uk-close"></a>
		@if(is_array($status))
		    <ul class='uk-list'>
		        @foreach ($status as $state)
		        	<li><span class="uk-icon-check"></span> {{ $state }}</li>
		        @endforeach
	    	</ul>
		@else
			{{ $status }}
		@endif
	</div>
@endif