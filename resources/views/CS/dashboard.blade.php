@extends('layouts.app')

@section('content')
<div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
    <div class="breadcrumb-title pe-3">CS Dashboard</div>
</div>

<!-- Search Section -->
<div id="loader-overlay" class="loader-overlay">
        <div class="loader"></div>
    </div>
    <h2 class="text-center display-4">Search</h2>
            <div class="row">
                <div class="col-md-8 offset-md-2">
                    <form action="{{ route('customer.device') }}" method="GET">
                        <div class="input-group">
                            <input type="search" name="device_id" class="form-control form-control-lg" placeholder="Type Device Serial Number">
                            <div class="input-group-append">
                                <button type="submit" class="btn btn-lg btn-default">
                                    <i class="fa fa-search"></i>
                                </button>
                            </div>
                        </div>
                    </form>                    
                </div>
            </div>

@endsection