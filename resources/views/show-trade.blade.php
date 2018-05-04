<div class="card card-body">

</div>


<div class="" style="margin-top:10px;">
    <div class="fluid">
        <div id="LeftCoinInfoPanel" class="col-sm-12">
            <div id="WidgetHolder" style="overflow:auto; border: 1px solid gray;">

                <div class="fluid" style="padding:10px;">

                    <div class="alert alert-warning">
                        Coin-Pair:
                        <form>
                            <label class="radio-inline">
                                <input type="radio" name="optCoinPair" value="ADAETH">Cardano
                            </label>
                            <label class="radio-inline">
                                <input type="radio" name="optCoinPair" value="TRXETH">Tron
                            </label>
                            <label class="radio-inline">
                                <input type="radio" name="optCoinPair" value="XVGETH">Verge
                            </label>
                            <br/>

                            Time interval:<br/>
                            <label class="radio-inline">
                                <input type="radio" name="optInterval" value="5m">5 Minutes
                            </label>
                            <label class="radio-inline">
                                <input type="radio" name="optInterval" value="15m">15 minutes
                            </label>
                            <label class="radio-inline">
                                <input type="radio" name="optInterval" value="1hr">1 Hour
                            </label>


                        </form>
                        <button id="btnTradeAction" class="btn btn-primary">Start Trade</button>
                        <span id="lastUpdatedTimeStamp" role="alert">
										</span>

                    </div>

                    <table id="stockTable" class="table table-bordered table-striped table-hover table-sm" style="background-color:#fff;">
                        <thead class="thead-inverse">
                        <tr>
                            <th>
                                <span class="glyphicon glyphicon-time" aria-hidden="true"></span> &nbsp;
                                Time</th>
                            <!-- <th>Sym</th> -->
                            <th>
                                <span class="glyphicon glyphicon-random" aria-hidden="true"></span> &nbsp;


                                Buy/Sell</th>
                            <!-- <th>Price £</th> -->
                            <th>Stoch</th>
                            <th>MACD</th>
                            <th>
                                <span class="glyphicon glyphicon-euro" aria-hidden="true"></span> &nbsp;
                                Price</th>
                            <th>
                                <span class="glyphicon glyphicon-heart" aria-hidden="true"></span> &nbsp;
                                Gain %</th> <!-- Only for a SELL trade -->

                            <!-- <th>Change 1hr</th> -->
                            <!-- <th>Change 1day</th> -->
                            <!-- <th>Updated on</th> -->
                        </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
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

                </div> <!-- End of: WidgetHolder -->





            </div><!-- End of Left Panel-->

        </div>
    </div>
</div><!-- End of Main Div -->
