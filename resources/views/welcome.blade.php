@extends('layouts.app')

@push('scripts')
<script src="{{ asset('assets/js/show-trade.js') }}"></script>
@endpush

@section('content')
    <i class="fas fa-cog"></i>
    @include('show-trade')
@endsection