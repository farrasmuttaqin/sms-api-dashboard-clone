@extends('layouts.main')

@section('content')

@component('components.panel-content')
<div class="uk-grid uk-margin-large-top" style="height: 300px">
    <div class="uk-width-medium-1-3 uk-width-large-1-3" >
        <div id="loader-daily" class="chart-loader"></div>
        <div
            id="chart-daily"
            style="width:300px; height:250px; margin: 0 auto;"
            ></div>

    </div>
    <div class="uk-width-medium-1-3 uk-width-large-1-3">
        <div id="loader-weekly" class="chart-loader"></div>
        <div
            id="chart-weekly"
            style="width:300px; height:250px; margin: 0 auto;"
            ></div>
    </div>
    <div class="uk-width-medium-1-3 uk-width-large-1-3">
        <div id="loader-monthly" class="chart-loader"></div>
        <div
            id="chart-monthly"
            style="width:300px; height:250px; margin: 0 auto;"
            ></div>
    </div>
    <li class="uk-width-1-1">
        <hr class="uk-margin-bottom">
        <article class="uk-comment">
            <header class="uk-comment-header">
                <h4 class="uk-comment-title">{{trans('app.credit_info')}}</h4>
            </header>
            <blockquote>
                <small>{{trans('app.total_credit')}} {{$totalCredit}}</small>
                <small></small>
            </blockquote>
        </article>
    </li>

    @foreach ($apiUsers->chunk(5) as $apiUsersChunk)
       <div class="uk-child-width-1-2@s uk-child-width-1-3@m uk-text-center" uk-grid>
           <table class="uk-table uk-table-hover uk-table-striped border-rounded" >
                <tbody>
                    @foreach($apiUsersChunk as $apiUser)
                        <tr>
                            <th>{{$apiUser->user_name}}<th>
                            <td>
                                @if($apiUser->is_postpaid == 1)
                                    <font style="color:blue;">{{trans('app.unlimited')}}</font>
                                @elseif($apiUser->credit < 1)
                                    <font style="color:red;">{{$apiUser->credit}}</font>
                                @else
                                    {{$apiUser->credit}}
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            &nbsp;
        </div>
    @endforeach
</div>


@endcomponent
@endsection


@push('scripts')

<script>
    $(document).ready(function () {
        var dashboard = new DashboardPage();
        dashboard.init();
    });
</script>
@endpush
