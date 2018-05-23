@extends('layouts.app')

@push('scripts')
<script src="{{ asset('assets/js/trading.js') }}"></script>
@endpush


@section('content')
    <div class="container">
        <section class="card col-xs-12">
            <div class="card-header">
                <h3 class="card-title">Trading Indicator signal <i class="fa fa-btc" aria-hidden="true"></i>
                    <i class="fa fa-eth" aria-hidden="true"></i></h3>


            </div>
            <div class="card-block pt-2">
                {!! Form::open(['id' => 'frm_trading', 'url' => url('scanning'), 'class' => '']) !!}
                <div class="form-group row">
                    {!! Form::label('coin', 'Coin Settings', ['class' => 'col-md-3 control-label text-right']) !!}
                    <div class="col-md-7">
                        {!! Form::select('coin', $coins, '', ['class' => 'col-md-3']) !!} &nbsp;
                        {{--{!! Form::radio('$coinPairs', 1, '') !!} &nbsp;--}}
                        {!! Form::select('coinPairs', $coinPairs, '', ['class' => 'col-md-3']) !!} &nbsp;
                        {!! Form::select('interval', $intervals, '5m', ['class' => 'col-md-3']) !!}

{{--                        {!! Form::select('coin', $coins, '', ['class' => 'form-control']) !!} &nbsp;
                        {!! Form::select('coinPairs', $coinPairs, '', ['class' => 'form-control']) !!} &nbsp;
                        {!! Form::select('interval', $intervals, '5m', ['class' => 'form-control']) !!}--}}
                    </div>
                </div>

                <div class="form-group row">
                    {!! Form::label('macd', 'MACD Settings', ['class' => 'col-md-3 control-label text-right']) !!}
                    <div class="col-md-7">
                        <input name="macdFastLineInputBox" value="12" /> &nbsp;
                        <input name="macdSlowLineInputBox" value="26" /> &nbsp;
                        <input name="macdSignalLineInputBox" value="9" />

                    </div>
                </div>

                <div class="form-group row">
                    {!! Form::label('stoch', 'Stochastic Settings', ['class' => 'col-md-3 control-label text-right']) !!}
                    <div class="col-md-7">
                        <input name="stochK" value="14" /> &nbsp;
                        <input name="stochD" value="6" /> &nbsp;
                        <input name="stochSmoothing" value="6" />

                    </div>
                </div>
{{--                <div class="form-group row">
                    {!! Form::label('coinPairs', 'Pair', ['class' => 'col-md-3 control-label text-right']) !!}
                    <div class="col-md-7">

                    </div>
                </div>
                <div class="form-group row">
                    {!! Form::label('interval', 'Interval', ['class' => 'col-md-3 control-label text-right']) !!}
                    <div class="col-md-7">

                    </div>
                </div>--}}
                {!! Form::close() !!}
            </div>
            <div class="card-footer">
                <div class="form-group row">
                    {!! Form::label('', '', ['class' => 'col-md-3 control-label text-right']) !!}
                    <div class="col-md-7">
                        <button id="btn_start_scanning" class="btn btn-primary"><i class="fas fa-play"></i> Start</button>
                        <button id="btn_stop_scanning" disabled class="btn btn-primary"><i class="fas fa-stop"></i> Stop</button>
                        <button id="btn_clear_table" class="btn btn-warning"><i class="fas fa-window-close"></i> Clear</button>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <div id="result_container" class="container">
        <section class="card col-xs-12">
            <div id="results" class="card-block pt-2">

            </div>

            <div>
                <div class="card-header">
                    <h5 class="card-title">Auto trade signal <i class="fa fa-bar-chart" aria-hidden="true"></i>
                    </h5>
                </div>
                <table id="AutoSignalDataTable" class="table table-condensed col-md-12">
                    <thead>
                    <tr>
                        <th>Time</th>
                        <th>CoinPair</th>
                        <th>MACD</th>
                        <th>Stoch</th>
                        <th>OrderType</th>
                        <th>Price</th>
                        <th>Profit</th>
                        <th>Margin</th>
                    </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>

            <div>
                <div class="card-header">
                    <h5 class="card-title">MACD Signal data
                        <i class="fa fa-line-chart" aria-hidden="true"></i>
                    </h5>
                </div>
                {{--<table id="SignalDataTable" class="table table-condensed col-md-12">--}}
                <table id="SignalDataTable" class="col-md-12">
                    <thead>
                    <tr>
                        <th>#</th>
                        <th>Time <i class="fa fa-line-chart" aria-hidden="true"></i></th>
                        <th>Closing Price<i class="fa fa-money" aria-hidden="true"></i></th>
                        <th>MACD</th>
                        <th>Signal</th>
                    </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>

        </section>
    </div>
@endsection