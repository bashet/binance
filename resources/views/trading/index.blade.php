@extends('layouts.app')

@push('scripts')
<script src="{{ asset('assets/js/trading.js') }}"></script>
@endpush


@section('content')
    <div class="container">
        <section class="card col-xs-12">
            <div class="card-header">
                <h3 class="card-title">Observe Coin</h3>
            </div>
            <div class="card-block pt-2">
                {!! Form::open(['id' => 'frm_trading', 'url' => url('start-scanning'), 'class' => '']) !!}
                <div class="form-group row">
                    {!! Form::label('coin', 'Coin', ['class' => 'col-md-3 control-label text-right']) !!}
                    <div class="col-md-7">
                        {!! Form::select('coin', $coins, '', ['class' => 'form-control']) !!}
                    </div>
                </div>
                <div class="form-group row">
                    {!! Form::label('interval', 'Interval', ['class' => 'col-md-3 control-label text-right']) !!}
                    <div class="col-md-7">
                        {!! Form::select('interval', $intervals, '5m', ['class' => 'form-control']) !!}
                    </div>
                </div>
                {!! Form::close() !!}
            </div>
            <div class="card-footer">
                <div class="form-group row">
                    {!! Form::label('', '', ['class' => 'col-md-3 control-label text-right']) !!}
                    <div class="col-md-7">
                        <button id="btn_start_scanning" class="btn btn-primary">Start Scanning...</button>
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection