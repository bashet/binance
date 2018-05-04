<div class="card">
    <div class="card-header">
        <h3>Showing Current Market Price @ {{\Carbon\Carbon::now()->format('h:i a')}}</h3>
    </div>
    <div class="card-body">
        <table id="table_current_market" class="table table-bordered table-striped table-hover" style="width: 100%">
            <thead>
            <tr>
                <th class="text-center">Symbol</th>
                <th class="text-center">Status</th>
                <th class="text-center">BaseAsset</th>
                <th class="text-center">Precision</th>
                <th class="text-center">QuoteAsset</th>
                <th class="text-center">QuotePrecision</th>
            </tr>
            </thead>
            <tbody>
            @foreach($markets as $market)
                <tr>
                    <td class="text-center">{{$market['symbol']}}</td>
                    <td class="text-center">{{$market['status']}}</td>
                    <td class="text-center">{{$market['baseAsset']}}</td>
                    <td class="text-center">{{$market['baseAssetPrecision']}}</td>
                    <td class="text-center">{{$market['quoteAsset']}}</td>
                    <td class="text-center">{{$market['quotePrecision']}}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>