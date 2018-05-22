<?php

namespace App\Http\Controllers;

use adman9000\binance\BinanceAPI;
use Illuminate\Http\Request;
use Larislackers\BinanceApi\BinanceApiContainer;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */

    //### Public variables
    public $firstMACD_Call = 1;
    public $firstStoch_Call = 1;

    public $previous_FastLine_EMA = 1;  //## Stores old value to calculate New/Current EMA
    public $previous_SlowLine_EMA = 1;  //## Stores old value
    public $previous_SmoothingLine_EMA = 1;     //### It is required to calculate the new SmoothingLine EMA (9 Day EMA)


//############################ MACD Parameters ###########################
    public $macd_FastLengthPeriod        = 12;
    public $macd_SlowlengthPeriod        = 26;
    public $macd_SignalSmoothingPeriod   = 9;
    public $macd_PriceSource        = "close";

    public $fastLength_K = 0;              //## Fast Line Weighting Factor
    public $slowLength_K = 0;              //## Slow Line Weighting Factor
    public $signalSmoothing_K = 0;    //## Signal Smoothing Weighting Factor

    //############################ Stochastic Parameters ###########################
    public $stoch_K        = 14;
    public $stoch_D        = 6;
    public $stoch_Smooth   = 6;


    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('home');
    }

    public function welcome(){

        $binance = new BinanceAPI();
        $markets = $binance->getMarkets();

        $data = array();
        $data['markets'] = $markets;


//		alert()->success('Data not found!')->persistent();
//            flash()->success('Data not found!')->important();

        return view('welcome', $data);
    }

    public function adaeth(){
        $data = array();

        return view('adaeth', $data);
    }

    public function get_adaeth(Request $request){
        $coinPair = 'ADAETH';
        //$coinPair = $request->coinpair;
        $timeInterval = '5m';

        //## Data Limit: First time read enough data to make a proper MACD/Stoch point. Thereafter read only Current KLine
        $dataLimitMACD = ($this->firstMACD_Call == 1 ? ($this->macd_SlowlengthPeriod+$this->macd_SignalSmoothingPeriod) : 1);
        //$dataLimitStoch = ($this->firstStoch_Call == 1 ? 20 : 1);

        $api = new BinanceApiContainer('','');

        $data = $api->getKlines(['symbol' => $coinPair, 'interval' => $timeInterval, 'limit' => $dataLimitMACD]);
        if(! $data){
            alert()->success('Data not found!')->persistent();
            flash()->success('Data not found!')->important();
            return redirect()->back();
        }

        $resultMACD = json_decode($data->getBody()->getContents(), true);

        if($this->firstMACD_Call == 1)
        {
            $this->firstMACD_Call = 0; //## First call is made, so No more call as FirstCall and avoid loading 36 Previous Data
            return $this->ProcessInitialDataForFirstMACD_Point($resultMACD);
        }else{
            return $this->CalculateCurrentEMA($resultMACD[0][4]);
        }
        //return $resultMACD[0];
    }

    //### This will take the array of 36 interval's Data and process the price
    //      Will Calculate the 12 Day SMA; Then Calculate the 13th EMA using the 12th SMA.
    //      Then calculate the 26 Day EMA similar way
    //      Then Calculate the Difference of 12-26 da EMA-> This will be the MACD point
    //      Then Calculate the First Signal Point based on previous 9 EMA of MACD Point
    public function ProcessInitialDataForFirstMACD_Point($macdResult)
    {
        //$macdResult = json_encode();
        //## we need an Array to hold EMA values for FastLine (usually 14 EMA)
        //## The size will be SlowLine-FastLine (ie: 26 - 12) = 14 Values to calculate the very first MACD value-> which is Difference of 26 Period Minus 12 Period
        //## Refer to Excel Sheet "MACD G31"; MACD(12,26,9)-> Will need 23 Values to get first 9EMA Value
        $fastLine_EMA_Array[$this->macd_SlowlengthPeriod - $this->macd_FastLengthPeriod + $this->macd_SignalSmoothingPeriod] = [0];     //## This wil have the initial 26 EMA value of Fast Line (12 Day!)
        $slowLineEMA_Array[$this->macd_SignalSmoothingPeriod] = [0]; //### WIll need 9 more values to Calculate the First 9EMA point

        $macdFastLineArray[$this->macd_SignalSmoothingPeriod] = [0];  //### Will be used to Store the MACD Fast Line on the Graph. Excel Worksheet G31.

        $fastLength_SEED_SMA = 0.0;    //## This is 12 Period; First Param of MACD
        $slowLength_SEED_SMA = 0.0;    //## This is 26 Period; First Param of MACD

        //### Calculate the Constants for Weighting Factors!
        $this->fastLength_K         = (2/($this->macd_FastLengthPeriod + 1));       //## 12
        $this->slowLength_K         = (2/($this->macd_SlowlengthPeriod + 1));       //## 26
        $this->signalSmoothing_K    = (2/($this->macd_SignalSmoothingPeriod + 1));  //##  9

        $fastLineEMA_Counter = 0;   //### This will be used in the array of FastLineEMA (usually 12 Period)
        $slowLineEMA_Counter = 0;
        $macdArrayCounter    = 0;

        $currentClosingValue = 0.0;
        $previousEMA         = 0.0;

        $firstMACD_Point            = 0.0;  //### This is the Desired value we want.. needs loads of calculation
        $firstSmoothingSignal_Point = 0.0; //### Second key Important Point to draw the Graph; Needs Loads of calculation

        //### First Calculate the 12 Day and 26 Day SMA; $macdResult.length should be at least 36
        for ($x = 0; $x<$this->macd_SlowlengthPeriod ; $x++) //## Up to first 26-Day Price value
        {
            $currentClosingValue = $macdResult[$x][4];

            if($x< ($this->macd_FastLengthPeriod-1)) //### Usually 12
            {
                $fastLength_SEED_SMA += $macdResult[$x][4];
            }
            else{
                //### Once the loop has processed 12 Values and found the SEED SMA value- we can start to calculate the First EMA.
                if($x= ($this->macd_FastLengthPeriod-1) ) //### Usually 12
                {
                    $fastLengthMovingAverage = ($fastLength_SEED_SMA/$this->macd_FastLengthPeriod); //### 12 Period's Sum Divided by 12
                    //## Only for the First EMA.
                    $fastLine_EMA_Array[0] = $this->GetEMA_Value($fastLengthMovingAverage, $macdResult[$x][4], $this->fastLength_K);  //### First EMA calculation formula
                }else{
                    $previousEMA = $fastLine_EMA_Array[$fastLineEMA_Counter-1];
                    //### First EMA was done using a 12-Period SMA. But thereafter all EMA will be using previous EMA value
                    $fastLine_EMA_Array[$fastLineEMA_Counter] = $this->GetEMA_Value($previousEMA, $currentClosingValue, $this->fastLength_K);  //### EMA calculation formula
                }
                $fastLineEMA_Counter++;
            }

            $slowLength_SEED_SMA += $macdResult[$x][4];

            //###### Now Lets talk about 26-EMA...
            $slowLength_SEED_SMA += $macdResult[$x][4]; //### Let's calculate till 26 Period...

            //## Once 26 Moving Average is SUMMED- we can use it for 27th Day EMA calculation; 28th and onwards wil be different
            if($x = ($this->macd_SlowlengthPeriod)) //## Looking for 27th Day
            {
                //#### Refer to Excel sheet: 'MACD! F31'
                $averageSMA_Seed_Value = ($slowLength_SEED_SMA/$this->macd_SlowlengthPeriod);  //### Sum Divided by Num of Periods!

                $slowLineEMA_Array[0] = $this->GetEMA_Value($averageSMA_Seed_Value, $currentClosingValue, $this->slowLength_K); //### First value (26th Day) is the Average of the current 26 Period
                $slowLineEMA_Counter = 1; //### I am 100% sure- first ONE is done!
            }else{
                if($x > ($this->macd_SlowlengthPeriod + 1)) //## Looking for 28th Day onwards value)
                {
                    //#### Refer to Excel sheet: 'MACD! F32'
                    //### Here EMA will get Previous EMA to formulate.. Previous EMA from 27th Day. And 26th Day is a Simple Average!
                    $previousEMA = $slowLineEMA_Array[$slowLineEMA_Counter-1];  //### The array was already filled on 27th Day..
                    $slowLineEMA_Array[$slowLineEMA_Counter] = $this->GetEMA_Value($previousEMA, $currentClosingValue, $this->slowLength_K); //### First value (26th Day) is the Average of the current 26 Period
                    $slowLineEMA_Counter++;
                }
            }
            $slowLineEMA_Counter++;

            //#### 1. MACD Fast Line: Excel-> G31
            //### Let's talk about the First MACD Point. First Important Point to Draw the MACD. Next is Smoothing Signal (9)
            //### Let's do it within this ForLoop.. as we go- calculate the MACD- which is FastLine Minus Slow Line.. very simple
            if($x >= $this->macd_SlowlengthPeriod-1) //## After reading enough Data! 26 day EMA. Now continue till 26+9 days..
            {
                //### First MACD Point is: 12EMA - 26EMA
                $macdFastLineArray[$macdArrayCounter] = $fastLine_EMA_Array[$x] - $slowLineEMA_Array[$x];  //### Refer to Excelsheet: 'MACD G31'

                //#### 2. Smoothing Signal Line: Excel->H40
                //### First SmoothingLine Point (9EMA) will be the simple Average of previous 9 MACD Values
                //### Also- the First SmoothingSignal Point is simply a summed Average of MACD Line.. So- use the same values to
                $firstSmoothingSignal_Point += $macdFastLineArray[$macdArrayCounter]; //### Carry on till SlowLine+SmoothingLine (26 + 9 = 35 Days)

                $macdArrayCounter++;
            }

        } //## End of For..Loop

        //### Now calculate the First MACD Point: 35th Elements; 34th Index; Or SlowLine+SmoothingLine (26 + 9 )
        $lastArrayElement = $this->macd_SlowlengthPeriod + $this->macd_SignalSmoothingPeriod - 1;   //## Will be re-used several places
        $firstMACD_Point = $macdFastLineArray[$lastArrayElement];

        //### Now Calculate the Average of Last 9 MACDs.. This will be used for Future EMA Calculation
        $firstSmoothingSignal_Point = $firstSmoothingSignal_Point / $this->macd_SignalSmoothingPeriod; //### (Sum of 9 MACDs / Number of Signal Period)

        $macd_signal_points[2] = [$firstMACD_Point, $firstSmoothingSignal_Point];   //## Array with two values. One for MACD Point and another for SignalSmoothing Line

        //## Store the Values for Next calculation
        $this->previous_FastLine_EMA = $fastLine_EMA_Array[$lastArrayElement];
        $this->previous_SlowLine_EMA = $slowLineEMA_Array[$lastArrayElement];
        $this->previous_SmoothingLine_EMA = $firstSmoothingSignal_Point;

        return $macd_signal_points;
    }

    //### To Calculate Current EMA- we need FastLine (12 EMA), Slow Line (26 EMA) and The Previous Smoothing Line (9 EMA value)
    public function CalculateCurrentEMA($currentClosing)
    {
        //### We need only Current Price to Calculate New EMA, Because we already have previous EMAs
        $newFastLine = $this->GetEMA_Value($this->previous_FastLine_EMA, $currentClosing, $this->macd_FastLengthPeriod);
        $newSlowLine = $this->GetEMA_Value($this->previous_SlowLine_EMA, $currentClosing, $this->macd_SlowlengthPeriod);

        $newMACD_Line = $newFastLine - $newSlowLine;
        $newSmoothingLine = $this->GetEMA_Value($this->previous_SlowLine_EMA, $currentClosing, $this->macd_SlowlengthPeriod);

        $newEMA_Points[2] = [$newMACD_Line, $newSmoothingLine];
        return $newEMA_Points;

    }
    //#### This will calculate the Current Actual EMA point Value- using weighting factor and Previous EMA Value
    public function GetEMA_Value($previousEMA, $currentClosing, $k)
    {
        /*      (Today's CL Price * K) + (Yesterday's EMA * (1-K))       */
        return ( ($currentClosing * $k) + ($previousEMA * (1-$k)));
    }

    //##########
    //#### Check whether this is the first call... Once first call is made- don't read all 36 previous data
    //##########
    public function setInitialDataIsProcessed()
    {
        if ($this->firstMACD_Call == 1){
            $this->firstMACD_Call = 0;
            $this->firstStoch_Call = 0;
        }
    }

    //#### This will take one single value for Current Closing Price and compare with the previous
    //      EMA- and see whether the new price is making a CrossOver-> MACD going Over Signal Line;
    public function checkForCrossOverMACD(currentPrice $price)
    {
        return 0;
    }

    public function get_current_price(Request $request){
        $coinPair = $request->optCoinPair;
        $timeInterval = $request->optInterval;

        // Get Kline/candlestick data for a symbol
        // Periods: 1m,3m,5m,15m,30m,1h,2h,4h,6h,8h,12h,1d,3d,1w,1M

        $api = new BinanceApiContainer('','');

        $data = $api->getKlines(['symbol' => $coinPair, 'interval' => $timeInterval, 'limit' => 20]);
        if(! $data){
            alert()->success('Data not found!')->persistent();
            flash()->success('Data not found!')->important();
            return redirect()->back();
        }

        $records = json_decode($data->getBody()->getContents(), true);


        return $records;
    }
}
