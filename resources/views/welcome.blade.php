@extends('layouts.app')

@push('scripts')
<script src="{{ asset('assets/js/show-trade.js') }}"></script>
@endpush

@section('content')
    @include('show-trade')
@endsection