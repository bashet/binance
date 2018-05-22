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

        $first_row = collect($records->pluck('4')); // take only closing price and convert them as array
        $first_row = $first_row->map(function ($item) {
            return substr($item, -5, 5);
        });
        $second_row = [];
        $third_row = [];
        $macd_signals = collect(); // this method will hold the data as object and will give lot of flexibility to work with.

        $second_row_initial = $first_row->take($ema_12)->avg(); // take fist 12 and average them
        $third_row_initial = $first_row->take($ema_26)->avg(); // take fist 26 and average them

        for ($i = 0; $i < 25; $i++){
            if($i == 0){
                $second_row [$i] = $second_row_initial;
            }else{
                $second_row [$i] = ( $first_row[ ($ema_12 + $i) -1 ] * (2/($ema_12+1)) ) + ( $second_row [ $i - 1 ] * ( 1 - (2/($ema_12+1)) ) );
            }
        }


        for ($i = 0; $i < 11; $i++){
            if($i == 0){
                $third_row [$i] = $third_row_initial;
            }else{
                $third_row [$i] = ( $first_row[ ($ema_26 + $i) -1 ] * (2/($ema_26+1)) ) + ( $third_row [ $i - 1 ] * ( 1 - (2/($ema_26+1)) ) );
            }
        }

        for ($i = 0; $i < 10; $i++){
            $macd_signals->put($i, $second_row[ 14 + $i ] - $third_row[ $i ]);
        }

        $ema_signal[0] = $macd_signals->take(10)->avg();
        $ema_signal[1] = ( $macd_signals->last() * (2/($ema_9+1)) ) + ( $ema_signal[0] * ( 1 - (2/($ema_9+1)) ) );



        return [
            '1st_row' => $first_row,
            '2nd_row' => $second_row,
            '3rd_row' => $third_row,
            'macd' => $macd_signals->last(),
            'ema' => $ema_signal[1]
        ];
    }
}
