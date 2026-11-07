@extends('layouts.app')

@push('styles')
<link rel="stylesheet" href="/css/cal-policy-pages.css?v=20260706">
@endpush

@section('content')
@include('partials.policy-page', ['title' => $page->post_title ?? 'Complaints Policy'])
@endsection