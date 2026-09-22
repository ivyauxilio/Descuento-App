@extends('layouts.admin')

@section('title', 'Edit Plan')
@section('page-title', 'Edit Subscription Plan')
@section('page-description', 'Update plan details: ' . $plan->name)

@section('content')
    <form method="POST" action="{{ route('admin.plans.update', $plan) }}">
        @csrf
        @method('PUT')
        @include('admin.plans.partials.form', ['plan' => $plan])
    </form>
@endsection
