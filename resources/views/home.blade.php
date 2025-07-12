{{-- home.blade.php --}}

@extends('layouts.app')

@section('content')
    {{-- Memuat tampilan dashboard spesifik berdasarkan role yang dikirim dari HomeController --}}
    @include($dashboardView)
@endsection