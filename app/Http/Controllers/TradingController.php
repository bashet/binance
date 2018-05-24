<?php

namespace App\Http\Controllers;

use adman9000\binance\BinanceAPI;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Larislackers\BinanceApi\BinanceApiContainer;

class TradingController extends Controller
{
    //#### Public Variables
    public $ema_9 = 9;
    public $ema_12 = 12;
    public $ema_26 = 26;

    public function clear_session(Request $request){
        //### Actually reset the value... Same as clearing up!!
        $request->session()->forget('firstTime');
        $request->session()->forget('orderType');

        session(['orderType' => 'null']);
        session(['firstTime' => 'true']); //### Now Toggle the value... We have loaded the Initial Batch.. So- set to False
        $firstTime = session('firstTime', 'true'); //## + '; Value is Reset now'
        return ['IsFirstCall'=>  $firstTime];
    }

    public function index(){
        $data = array();

        //$coins = ['ADAETH' => 'ADAETH', 'BNBETH' => 'BNBETH', 'FUNETH'=>'FUNETH', 'TRXETH' => 'TRXETH', 'ETHUSDT' => 'ETHUSDT', 'BTCUSDT'=> 'BTCUSDT'];
        $coins = ['ADA' => 'ADA', 'BNB' => 'BNB', 'FUN'=>'FUN', 'TRX' => 'TRX', 'ETH' => 'ETH', 'BTC'=> 'BTC'];
        $data['coins'] = $coins;

        $coinPairs= ['ETH'=>'ETH', 'BTC'=>'BTC', 'USDT'=>'USDT'];
        $data['coinPairs'] = $coinPairs;

        $intervals = [
            '5m' => '5 Minutes',
            '15m' => '15 Minutes',
            '1h' => '1 Hour'
        ];
        $data['intervals'] = $intervals;

        return view('trading.index', $data);
    }

    public function start_scanning(Request $request){

        $this->Get_Stoch_MACD_ParamValues($request); //## This will fetch all Param Values for both Stochastic and MACD indicators
        $api = new BinanceApiContainer('','');

        $isLoadingFirstTime = session('firstTime', 'true');
        if($isLoadingFirstTime=='true')
        {
            session(['firstTime' => 'false']); //### Now Toggle the value... We have loaded the Initial Batch.. So- set to False
            $isLoadingFirstTime = 'false';
        }

        $coinPair = $request->coin . $request->coinPairs;
        $dataLimitMACD = ($isLoadingFirstTime == 'true' ? (12+26) : 1);

        $data = $api->getKlines(['symbol' => ($coinPair), 'interval' => $request->interval, 'limit' => $dataLimitMACD]);
        if(! $data){
            alert()->success('Data not found!')->persistent();
            flash()->success('Data not found!')->important();
            return redirect()->back();
        }

        $records = json_decode($data->getBody()->getContents(), true);

        if($isLoadingFirstTime=='true') {
            return ['time' => $records[0][6], 'coinPair'=> $coinPair, 'closingPrice' => $records[0][4], 'IsFirstCall'=> $isLoadingFirstTime, 'signals' => $this->get_signals(collect($records), $request->coin)];
        }
        else {
            //return ['time' => $records[0][6], 'closingPrice' => $records[0][4], 'signals' => $this->get_signals(collect($records), $request->coin)];
            return ['time' => $records[0][6], 'coinPair'=> $coinPair, 'closingPrice' => $records[0][4], 'IsFirstCall'=> $isLoadingFirstTime, 'signals' => $this->CalculateCurrentEMA($records[0][4])];
/*
            return ['time' => $records[0][6], 'coinPair' => 'ADAETH',
                'MACD' => 'Y',
                'Stoch' => '',
                'OrderType' => 'X',
                'closingPrice' => $records[0][4],
                'Profit' => '', 'Margin' => '0',
                'signals' => $this->CalculateCurrentEMA($records[0][4])];
*/
        }


    }

    //### My Global Variables Data feeding.
    public function Get_Stoch_MACD_ParamValues(Request $request)
    {
        $this->ema_9  = $request->macdSignalLineInputBox;
        $this->ema_12 = $request->macdFastLineInputBox;
        $this->ema_26 = $request->macdSlowLineInputBox;
    }

    public function get_signals(Collection $records, $tradeCoin)
    {
        // ADA, TRX, XVG-> Needs formatting. remove the preceeding Decimal Point
        // ETHUSDT, BTCUSTD, LTCUSDT->> Need full Price, ie: $10502.22
        $price_column = collect($records->pluck('4')); // take only closing price and convert them as array
        // Price will be formatted according to the Price!
        if(!($tradeCoin=='ETH' || $tradeCoin =='BTC')) { //### When trading with ETHUSDT or BTCUSDT- we need the whole value- not Part or anything after trimming!
            $price_column = $price_column->map(function ($item) {
                return substr($item, 2, 8);
            });
        }

        $ema12_column = [];
        $ema26_column = [];
        $macd_line = collect(); // this method will hold the data as object and will give lot of flexibility to work with.

        $second_col_initial = $price_column->take($this->ema_12)->avg(); // take fist 12 and average them
        $third_col_initial = $price_column->take($this->ema_26)->avg(); // take fist 26 and average them

        //#### Now build the 12-EMA Values
        for ($i = 0; $i < $this->ema_26-1; $i++){
            if($i == 0){
                $ema12_column [$i] = intval($second_col_initial);
            }else{
                //$value = ( $price_column->get(($this->ema_12 + $i) -1) * (2/($this->ema_12+1)) ) + ( $ema12_column [ $i - 1 ] * ( 1 - (2/($this->ema_12+1)) ) );
                $value = $this->GetEMA_Value(($price_column->get(($this->ema_12 + $i) -1)), $ema12_column [ $i - 1 ], $this->ema_12);
                $ema12_column [$i] = intval($value);
            }
        }


        //## Now calculate the 26-EMA Values
        for ($i = 0; $i < $this->ema_12-1; $i++){
            if($i == 0){
                $ema26_column [$i] = intval($third_col_initial);
            }else{
                //$value = ( $price_column->get(($this->ema_26 + $i) -1) * (2/($this->ema_26+1)) ) + ( $ema26_column [ $i - 1 ] * ( 1 - (2/($this->ema_26+1)) ) );
                $value = $this->GetEMA_Value($price_column->get(($this->ema_26 + $i) -1), ($ema26_column [ $i - 1 ]), $this->ema_26);

                $ema26_column [$i] = intval($value);
            }
        }

        //### MACD Line- ( Fast Line Minus Slow Line, ie: 12 - 26 EMA)
        for ($i = 0; $i < $this->ema_9; $i++){
            $macd_line->put($i, $ema12_column[ $this->ema_26-$this->ema_12 + $i] - $ema26_column[ $i ]);
        }

        $ema_signal = $macd_line->take($this->ema_9)->avg(); //### Simple Moving Average of previous 9 MACDs

        //### Calculation Ends here.. Subsequent Calculation will be done on every 5 Secs interval
       // $ema_signal[1] = ( $macd_line->last() * (2/($ema_9+1)) ) + ( $ema_signal[0] * ( 1 - (2/($ema_9+1)) ) );


        //### Now all done! So, store the currently Read values for the subsequent calls.. When we get next request to return MACD+Signal values- we can use the previous MACD and SIgnal Values to aclculate the New ones
        $this->SaveCurrentEMA_ForFuture($ema12_column[22], $ema26_column[$this->ema_9-1], $ema_signal);

        //### Now return the total Result
        return [
            'Price' => $price_column,
            '12-EMA' => $ema12_column,
            '26-EMA' => $ema26_column,
            'MACD' => number_format($macd_line->last(), 2),
            'Signal' => number_format($ema_signal[0], 2),
            'TradeSignal' => 'watch' //### A dummy value!
        ];
    }

    //### To Calculate Current (Single) EMA- we need FastLine (12 EMA), Slow Line (26 EMA) and The Previous Smoothing Line (9 EMA value)
    public function CalculateCurrentEMA($currentClosing)
    {
        //### TO DO: when SESSION works well- DELETE the following Session default values
        $previous_FastLine_EMA  = session('previous_FastLine_EMA', 0);  //### 12 EMA
        $previous_SlowLine_EMA  = session('previous_SlowLine_EMA', 0);  //### 26 EMA
        $previous_SignalLineEMA = session('previous_SignalLineEMA', 0); //### 9 EMA

        //### We need only Current Price to Calculate New EMA, Because we already have previous EMAs
        $newFastLine = $this->GetEMA_Value($currentClosing, $previous_FastLine_EMA, $this->ema_12);
        $newSlowLine = $this->GetEMA_Value($currentClosing, $previous_SlowLine_EMA, $this->ema_26);

        $newMACD_Line = $newFastLine - $newSlowLine;
        $newSignalLine_EMA = $this->GetEMA_Value($newMACD_Line, $previous_SignalLineEMA, $this->ema_9);

        //### Now all done! So, store the currently Read values for the subsequent calls.. When we get next request to return MACD+Signal values- we can use the previous MACD and SIgnal Values to aclculate the New ones
        $this->SaveCurrentEMA_ForFuture($newFastLine, $newSlowLine, $newSignalLine_EMA);

        //### Now return the total Result
        return [
            'MACD' => number_format($newMACD_Line, 2), //### 12EMA Minus 26EMA
            'Signal' => number_format($newSignalLine_EMA, 2), //### EMA result of Previous 9 MACD Values
            'TradeSignal' => $this->PriceCrossOverResult_MACD($newMACD_Line, $newSignalLine_EMA, $currentClosing) //### Find out whether BUY/SELL/Watch?
        ];

    }


    //### A Re-usable EMA Calculation function for MACD Calculation
    public function GetEMA_Value($currentClosing, $previousEMA, $weigthingPeriod)
    {
        //### First calculate the $k value from the Weighting Periods..
        $k = (2 / ($weigthingPeriod + 1) );

        /*      (Today's CL Price * K) + (Yesterday's EMA * (1-K))       */
        return ( ($currentClosing * $k) + ($previousEMA * (1-$k) ) );
    }

    ///### function AnalyzePriceAction: Not in USE!
    public function AnalyzePriceAction($current_MACD_Value, $current_Signal_Value)
    {

        $previousMACD = session('previousMACD', '0');
        $previousSignal = session('previousSignal', '0');

        //### First check for Existing OrderType which was placed very recently
        $existingOrderType = session("orderType", "null");
        if($existingOrderType=="null") //## Initial Load and Check..
        {
            //### When we come here second time- becoz after the very first time- no BUY/SELL signal was produced-
            //          we will keep coming in this block...

            if($previousMACD==0 && $previousSignal==0){ //### This is the very first time! Only once!
                session(['previousMACD' => $current_MACD_Value]);
                session(['previousSignal' => $current_Signal_Value]);

                return 'null'; //### Go back to the Caller function.. come back later after 5 seconds!
            }
            session(['previousMACD' => $current_MACD_Value]);
            session(['previousSignal' => $current_Signal_Value]);

            return 'null';
        }

        //#### If already previously signalled for a BUY-> then now see if its good time to SELL
        if($existingOrderType=="buy") //## Initial Load and Check..
        {
            session(['previousMACD' => $current_MACD_Value]);
            session(['previousSignal' => $current_Signal_Value]);
        }

        //#### If already previously signalled for a SELL-> then now see if its good time to BUY
        if($existingOrderType=="sell") //## Initial Load and Check..
        {
            if($current_MACD_Value > $current_Signal_Value) {
                session(['$existingOrderType' => 'buy']);
                return 'buy';
            }
        }

        //### Once everything is done flush the previous MACD and Signal Line values.. Get the Current values and save them as previous values for Future calculation!
        session(['previousMACD' => $current_MACD_Value]);
        session(['previousSignal' => $current_Signal_Value]);
    }

    public function  PriceCrossOverResult_MACD($macd_value, $signal_value, $current_price)
    {
        if($macd_value == $signal_value){
            //### Very rarely this can happen.... but still 'know how' to deal this.. Just watch and no trade!
            return 'watch';
        }

        //### First check for Existing OrderType which was placed very recently
        $existingOrderType = session("orderType", "null");

        //#### BUY Scenario: MACD Line going above Signal Line-> Blue Line Crossing the Orange line and Climbing UP!
        if($macd_value > $signal_value) {
            //### When Uptrend is starting- we will get BUY signal values (crossover) several times- take action only one- First time! and then watch for a new SELL signal!
            if($existingOrderType=='buy' || $existingOrderType=='null') {         //### Already BUY signal is ON.. that means we have bought already... DON'T buy anymore. Watch for SELL signal!
                return 'watch';
            }else if($existingOrderType=='sell'){   //## Previously we SOLD and just now found a BUY signal which is First time.. Commit a BUY Action
                session(['existingOrderType' => 'buy']);
                session(['lastMACD_Buy_Action_Price' => $current_price]); //### This will be used again while Selling- to calculate Profit!
                return 'buy';
            }
        }

        //####  SELL Scenario: Signal Line going above MACD Line-> Orange going above Blue.
        if($macd_value < $signal_value) {
            //### When downtrend is going on- we will get SELL signal values (crossover) several times- take action only one- First time! and then watch for SELL signal!
            if($existingOrderType=='sell' || $existingOrderType=='null') {        //### Already SELL signal is ON.. that means we have SOLD already... DON'T SELL anymore. Watch for BUY signal!
                return 'watch';
            }else if($existingOrderType=='buy'){    //## Previously we BOUGHT and just now found a fresh SELL signal which is First time.. Commit a SELL Action
                session(['existingOrderType' => 'sell']);
                $lastMACD_Buy_Action_Price = session('lastMACD_Buy_Action_Price','0');

                $profit_MACD = $current_price - $lastMACD_Buy_Action_Price;     //### This is the profit after the Buy and Sell trades!
                return 'sell: ' + $profit_MACD;
            }
        }
    }

    // ### Store the values in the SESSION variables
    public function SaveCurrentEMA_ForFuture($fastLine, $slowLine, $signalLine)
    {
        // ### Store the values in the SESSION variables
        session(['previous_FastLine_EMA' => $fastLine ]); //## 23rd Element
        session(['previous_SlowLine_EMA' => $slowLine ]); //### 9th Element after the First 26 Slow EMA values!
        session(['previous_SignalLineEMA' => $signalLine ]); //### Signal Smoothing Line; ie: 9 EMA
    }
}
