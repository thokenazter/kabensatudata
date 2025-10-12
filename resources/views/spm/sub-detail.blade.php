@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50">
  <div class="max-w-7xl mx-auto px-4 py-8">
    @if(!(auth()->check() && method_exists(auth()->user(), 'hasAnyRole') && auth()->user()->hasAnyRole(['super_admin','pegawai','nakes'])))
      @include('spm._nav')
    @endif
    @include('spm.partials.sub-detail-content')
  </div>
</div>
@endsection
