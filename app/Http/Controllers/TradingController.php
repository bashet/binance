<?php

namespace App\Http\Controllers;

use adman9000\binance\BinanceAPI;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Larislackers\BinanceApi\BinanceApiContainer;

class TradingController extends Controller
{
    //

    public function index(){
        $data = array();

        $coins = ['ADAETH' => 'ADAETH','ADABTC' => 'ADABTC'];
        $data['coins'] = $coins;

        $intervals = [
            '1m' => '1 Minute',
            '3m' => '3 Minutes',
            '5m' => '5 Minutes',
            '15m' => '15 Minutes',
            '30m' => '30 Minutes',
            '1h' => '1 Hour',
            '2h' => '2 Hours',
            '4h' => '4 Hours',
            '6h' => '6 Hours',
            '8h' => '6 Hours',
            '12h' => '12 Hours',
            '1d' => '1d Day',
            '3d' => 'd Days',
            '1w' => '1w Week',
            '1M' => '1 Month'
        ];
        $data['intervals'] = $intervals;

        return view('trading.index', $data);
    }

    public function start_scanning(Request $request){


        $api = new BinanceApiContainer('','');

        $data = $api->getKlines(['symbol' => $request->coin, 'interval' => $request->interval, 'limit' => 100]);
        if(! $data){
            alert()->success('Data not found!')->persistent();
            flash()->success('Data not found!')->important();
            return redirect()->back();
        }

        $records = json_decode($data->getBody()->getContents(), true);

        return ['time' => $records[0][6], 'signals' => $this->get_signals(collect($records))];
    }

    public function get_signals(Collection $records){
        $ema_9 = 9;
        $ema_12 = 12;
        $ema_26 = 26;

        $first_column = collect($records->pluck('4')); // take only closing price and convert them as array
        $first_column = $first_column->map(function ($item) {
            return substr($item, -8, 8);
        });
        $second_column = [];
        $third_column = [];
        $macd_line = collect(); // this method will hold the data as object and will give lot of flexibility to work with.

        $second_col_initial = $first_column->take($ema_12)->avg(); // take fist 12 and average them
        $third_col_initial = $first_column->take($ema_26)->avg(); // take fist 26 and average them

        //#### Now build the 12-EMA Values
        for ($i = 0; $i < $ema_26-1; $i++){
            if($i == 0){
                $second_column [$i] = intval($second_col_initial);
            }else{
                $value = ( $first_column->get(($ema_12 + $i) -1) * (2/($ema_12+1)) ) + ( $second_column [ $i - 1 ] * ( 1 - (2/($ema_12+1)) ) );
                $second_column [$i] = intval($value);
            }
        }


        //## Now calculate the 26-EMA Values
        for ($i = 0; $i < $ema_12-1; $i++){
            if($i == 0){
                $third_column [$i] = intval($third_col_initial);
            }else{
                $value = ( $first_column->get(($ema_26 + $i) -1) * (2/($ema_26+1)) ) + ( $third_column [ $i - 1 ] * ( 1 - (2/($ema_26+1)) ) );
                $third_column [$i] = intval($value);
            }
        }

        //### MACD Line- ( Fast Line Minus Slow Line, ie: 12 - 26 EMA)
        for ($i = 0; $i < $ema_9; $i++){
            $macd_line->put($i, $second_column[ $ema_26-$ema_12 + $i] - $third_column[ $i ]);
        }

        $ema_signal[0] = $macd_line->take($ema_9)->avg();

        //### Calculation Ends here.. Subsequent Calculation will be done on every 5 Secs interval
        $ema_signal[1] = ( $macd_line->last() * (2/($ema_9+1)) ) + ( $ema_signal[0] * ( 1 - (2/($ema_9+1)) ) );



        return [
            'Price' => $first_column,
            '12-EMA' => $second_column,
            '26-EMA' => $third_column,
            'MACD' => number_format($macd_line->last(), 2),
            'Signal' => number_format($ema_signal[0], 2)
        ];
    }
}
