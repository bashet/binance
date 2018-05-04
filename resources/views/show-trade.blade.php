<div class="well well-lg">
{!! Form::open(['id' => 'frm_trade', 'url' => 'get-current-price']) !!}
    <div class="form-group row">
        {!! Form::label('', 'Coin-Pair', ['class' => 'col-md-3 control-label text-right']) !!}
        <div class="col-md-7">
            <div class="custom-control custom-radio custom-control-inline">
                <input type="radio" id="customRadioInlineADAETH" name="optCoinPair" value="ADAETH" class="custom-control-input">
                <label class="custom-control-label" for="customRadioInlineADAETH">Cardano</label>
            </div>
            <div class="custom-control custom-radio custom-control-inline">
                <input type="radio" id="customRadioInlineTRXETH" name="optCoinPair" value="TRXETH" class="custom-control-input">
                <label class="custom-control-label" for="customRadioInlineTRXETH">Tron</label>
            </div>
            <div class="custom-control custom-radio custom-control-inline">
                <input type="radio" id="customRadioInlineVerge" name="optCoinPair" value="XVGETH" class="custom-control-input">
                <label class="custom-control-label" for="customRadioInlineVerge">Verge</label>
            </div>
        </div>
    </div>

    <div class="form-group row">
        {!! Form::label('', 'Time interval', ['class' => 'col-md-3 control-label text-right']) !!}
        <div class="col-md-7">
            <div class="custom-control custom-radio custom-control-inline">
                <input type="radio" id="customRadioInline5" name="optInterval" value="5" class="custom-control-input">
                <label class="custom-control-label" for="customRadioInline5">5 Minutes</label>
            </div>
            <div class="custom-control custom-radio custom-control-inline">
                <input type="radio" id="customRadioInline15" name="optInterval" value="15" class="custom-control-input">
                <label class="custom-control-label" for="customRadioInline15">15 minutes</label>
            </div>
            <div class="custom-control custom-radio custom-control-inline">
                <input type="radio" id="customRadioInline60" name="optInterval" value="60" class="custom-control-input">
                <label class="custom-control-label" for="customRadioInline60">1 Hour</label>
            </div>
        </div>
    </div>
    <div class="form-group row">
        {!! Form::label('', '', ['class' => 'col-md-3 control-label text-right']) !!}
        <div class="col-md-7">
            <button class="btn btn-primary" id="btnTradeAction">Start Trade</button>
        </div>
    </div>
{!! Form::close() !!}
</div>

@include('current-market')

<div class="alert alert-success">
    https://www.binance.com/api/v1/time
    <br/>
    https://www.binance.com/api/v3/ticker/price?symbol=ADAETH
    <br/>
    Kline/Candlestick data: https://www.binance.com/api/v1/klines?symbol=ADAETH&interval=15m&limit=20
    <br/>
    Symbol price ticker: https://www.binance.com/api/v3/ticker/price?symbol=ADAETH
    <br/>
    Binance API: https://github.com/binance-exchange/binance-official-api-docs/blob/master/rest-api.md
    <br/>
    <a href="https://github.com/binance-exchange/binance-official-api-docs/blob/master/rest-api.md#signed-endpoint-examples-for-post-apiv1order">Public Rest API for Binance </a>
</div>