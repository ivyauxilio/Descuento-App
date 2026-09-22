@extends('layouts.admin')

@section('title', 'Create Plan')
{{-- @section('page-title', 'Create Subscription Plan')
@section('page-description', 'Add a new subscription plan for merchants') --}}

@section('content')
    <form method="POST" action="{{ route('admin.plans.store') }}">
        @csrf
        @include('admin.plans.partials.form')
    </form>
@endsection
