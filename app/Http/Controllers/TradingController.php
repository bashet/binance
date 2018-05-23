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

//        Get_Stoch_MACD_ParamValues($request); //## This will fetch all Param Values for both Stochastic and MACD indicators
        $api = new BinanceApiContainer('','');

        $isLoadingFirstTime = session('firstTime', 'true');
        if($isLoadingFirstTime==1)
        {
            session(['firstTime' => 'false']); //### Now Toggle the value... We have loaded the Initial Batch.. So- set to False
            $isLoadingFirstTime = 'false';
        }

        $dataLimitMACD = ($isLoadingFirstTime == 'true' ? (12+26) : 1);

        $data = $api->getKlines(['symbol' => ($request->coin . $request->coinPairs), 'interval' => $request->interval, 'limit' => $dataLimitMACD]);
        if(! $data){
            alert()->success('Data not found!')->persistent();
            flash()->success('Data not found!')->important();
            return redirect()->back();
        }

        $records = json_decode($data->getBody()->getContents(), true);

// TO DO:        return ['time' => $records[0][6], 'closingPrice' => $records[0][4], 'signals' => $this->get_signals(collect($records), '123')]
        if($isLoadingFirstTime=='true') {
            return ['time' => $records[0][6], 'closingPrice' => $records[0][4], 'signals' => $this->get_signals(collect($records), $request->coin)];
        }
        else {
            return ['time' => $records[0][6], 'closingPrice' => $records[0][4], 'signals' => $this->get_signals(collect($records), $request->coin)];
            //return ['time' => $records[0][6], 'closingPrice' => $records[0][4], 'signals' => $this->CalculateCurrentEMA($records[0][4])];
            /*
            return ['time' => $records[0][6], 'coinPair' => 'ADAETH',
                'MACD' => 'Y',
                'Stoch' => '',
                'OrderType' => 'X',
                'Price' => $records[0][4],
                'Profit' => '', 'Margin' => '0',
                'signals' => $this->CalculateCurrentEMA($records[0][4])];
            */
        }


    }

    //### My Global Variables Data feeding.
    function Get_Stoch_MACD_ParamValues(Request $request)
    {
        //public $ema_9  = $request->macdSignalLineInputBox;
        $this->ema_12 = $request->macdFastLineInputBox;
        //public $ema_26 = $request->macdSlowLineInputBox;
    }

    public function get_signals(Collection $records, $tradeCoin){ // TO DO: Add this second parameter

        $ema_9 = 9;
        $ema_12 = 12;
        $ema_26 = 26;

        $price_column = collect($records->pluck('4')); // take only closing price and convert them as array
        $price_column = $price_column->map(function ($item) {
            // TO DO: Price will be formatted according to the Price!
            /*if($tradeCoin=='ETH' || $tradeCoin =='BTC') //### When trading with ETHUSDT or BTCUSDT- we need the whole value- not Part or anything after trimming!
            {return $item;}
            else{return substr($item, -8, 8);}*/
            return substr($item, -8, 8);
        });
        $ema12_column = [];
        $ema26_column = [];
        $macd_line = collect(); // this method will hold the data as object and will give lot of flexibility to work with.

        $second_col_initial = $price_column->take($this->ema_12)->avg(); // take fist 12 and average them
        $third_col_initial = $price_column->take($ema_26)->avg(); // take fist 26 and average them

        //#### Now build the 12-EMA Values
        for ($i = 0; $i < $ema_26-1; $i++){
            if($i == 0){
                $ema12_column [$i] = intval($second_col_initial);
            }else{
                $value = ( $price_column->get(($this->ema_12 + $i) -1) * (2/($this->ema_12+1)) ) + ( $ema12_column [ $i - 1 ] * ( 1 - (2/($this->ema_12+1)) ) );
                //$value = $this->GetEMA_Value($ema12_column [ $i - 1 ], $price_column->get(($this->ema_12 + $i) -1), $this->ema_12);
                $ema12_column [$i] = intval($value);
            }
        }


        //## Now calculate the 26-EMA Values
        for ($i = 0; $i < $this->ema_12-1; $i++){
            if($i == 0){
                $ema26_column [$i] = intval($third_col_initial);
            }else{
                $value = ( $price_column->get(($ema_26 + $i) -1) * (2/($ema_26+1)) ) + ( $ema26_column [ $i - 1 ] * ( 1 - (2/($ema_26+1)) ) );

                $ema26_column [$i] = intval($value);
            }
        }

        //### MACD Line- ( Fast Line Minus Slow Line, ie: 12 - 26 EMA)
        for ($i = 0; $i < $ema_9; $i++){
            $macd_line->put($i, $ema12_column[ $ema_26-$this->ema_12 + $i] - $ema26_column[ $i ]);
        }

        $ema_signal[0] = $macd_line->take($ema_9)->avg();

        //### Calculation Ends here.. Subsequent Calculation will be done on every 5 Secs interval
       // $ema_signal[1] = ( $macd_line->last() * (2/($ema_9+1)) ) + ( $ema_signal[0] * ( 1 - (2/($ema_9+1)) ) );


        //### Now all done! So, store the currently Read values for the subsequent calls.. When we get next request to return MACD+Signal values- we can use the previous MACD and SIgnal Values to aclculate the New ones
        /* // TO DO: Store the values in the SESSION variables
        $lastEMA12_Val = $ema12_column->last();
        $lastEMA26_Val = $ema26_column->last();
        $lastEMA9_Val = $ema_signal[0];

        session::set('previous_FastLine_EMA',$lastEMA12_Val);
        session::set('previous_SlowLine_EMA',$lastEMA26_Val);
        session::set('previous_SignalLineEMA',$lastEMA9_Val);
        */
/*
        session(['previous_FastLine_EMA' => $lastEMA12_Val]);// $ema12_column->last() ]);
        session(['previous_SlowLine_EMA' => $lastEMA26_Val]);// $ema26_column->last() ]);
        session(['previous_SignalLineEMA' => $lastEMA9_Val]);// $ema_signal[0] ]);
*/
        //### Now return the total Result
        return [
            'Price' => $price_column,
            '12-EMA' => $ema12_column,
            '26-EMA' => $ema26_column,
            'MACD' => number_format($macd_line->last(), 2),
            'Signal' => number_format($ema_signal[0], 2)
        ];
    }

    //### To Calculate Current (Single) EMA- we need FastLine (12 EMA), Slow Line (26 EMA) and The Previous Smoothing Line (9 EMA value)
    public function CalculateCurrentEMA($currentClosing)
    {
        $previous_FastLine_EMA  = session('previous_FastLine_EMA', 0);  //### 12 EMA
        $previous_SlowLine_EMA  = session('previous_SlowLine_EMA', 0);  //### 26 EMA
        $previous_SignalLineEMA = session('previous_SignalLineEMA', 0); //### 9 EMA

        //### We need only Current Price to Calculate New EMA, Because we already have previous EMAs
        $newFastLine = $this->GetEMA_Value($currentClosing, $previous_FastLine_EMA, $this->macd_FastLengthPeriod);
        $newSlowLine = $this->GetEMA_Value($currentClosing, $previous_SlowLine_EMA, $this->macd_SlowlengthPeriod);

        $newMACD_Line = $newFastLine - $newSlowLine;
        $newSignalLine_EMA = $this->GetEMA_Value($currentClosing, $previous_SignalLineEMA, $this->macd_SlowlengthPeriod);

        //### Now return the total Result
        return [
            'MACD' => number_format($newMACD_Line, 2), //### 12EMA Minus 26EMA
            'Signal' => number_format($newSignalLine_EMA, 2) //### EMA result of Previous 9 MACD Values
        ];

    }


    //### A Re-usable EMA Calculation function for MACD Calculation
    public function GetEMA_Value($currentClosing, $previousEMA, $k)
    {
        /*      (Today's CL Price * K) + (Yesterday's EMA * (1-K))       */
        return ( ($currentClosing * $k) + ($previousEMA * (1-$k)));
    }
}
