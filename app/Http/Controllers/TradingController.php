<?php

namespace App\Http\Controllers;

use adman9000\binance\BinanceAPI;
use Illuminate\Http\Request;
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

        return $records;
    }
}
