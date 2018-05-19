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


		alert()->success('Data not found!')->persistent();
            flash()->success('Data not found!')->important();

        return view('welcome', $data);
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

        //alert()->success('Total Record found: ' . count($records). ' Please check browser console!')->persistent();
        //flash()->success('Total Record found: ' . count($records). ' Please check browser console!')->important();

        //return $records;

        return $records;
    }
}
