<div id="search-panel" class="uk-panel uk-panel-box">
    <ul class="uk-grid uk-grid-small uk-form uk-form-search uk-margin-small-top" data-uk-grid-margin>
         {{$slot}}

         <li class="uk-width-1-1 uk-margin-top uk-nowrap">
            <div class="uk-float-right">
                <button class="uk-button uk-button-primary" id="search-clear" type="button">Clear</button>
                <button class="uk-button uk-button-primary" id="search-button" type="button">Search</button>
            </div>
        </li>
    </ul>
</div>