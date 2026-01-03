@extends('layouts.app')

@section('content')
    @include('layouts.navbar')

    {{-- Content --}}
    @include('components.home.banner')
    @include('components.home.medical-services')
    @include('components.home.doctors')
    @include('components.home.services')
    @include('components.home.about-us')
    @include('components.home.reservasi')

    {{-- End Content --}}

    @include('layouts.footter')
@endsection
