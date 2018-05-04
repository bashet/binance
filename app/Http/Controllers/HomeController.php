<?php

namespace App\Http\Controllers;

use adman9000\binance\BinanceAPI;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        //$this->middleware('auth');
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

        return view('welcome', $data);
    }

    public function get_current_price(Request $request){
        $base_url = "https://api.binance.com/api";
        $coinPair = $request->optCoinPair;
        $timeInterval = $request->optInterval;

        $link = $base_url . '/v1/klines?symbol='.$coinPair.'&interval='.$timeInterval.'m&limit=20';

        $records = json_decode(file_get_contents($link), true);

        alert()->success('Total Record found: ' . count($records))->persistent();
        flash()->success('Total Record found: ' . count($records))->important();

        //return $records;

        return redirect()->back();
    }
}
