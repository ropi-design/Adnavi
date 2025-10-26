@extends('layouts.app')

@section('content')
    <div class="space-y-6 p-6">
        @volt('recommendations.recommendation-list')
    </div>
@endsection
