<div id="{{ $id ?? "window-delete" }}" hidden>
	<div>{{ $title ?? "Delete Data" }}</div>
	<div>
		{{ $slot }}
        <input type="hidden" name="data_id" value="0" />
        <div class="uk-float-right">
            <button id="window-button-close" class="uk-button" type="button">Close</button>
            <button id="window-button-delete" class="uk-button uk-button-primary" type="button">{{ $submitText ?? "Delete"}}</button>
        </div>
	</div>
</div>
