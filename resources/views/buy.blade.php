@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">{{ __('Confirm Purchase') }}</div>

                    <div class="card-body">
                        You're about to purchase <b>{{ $product->name }}</b> for <b>${{ round($product->price / 100, 2) }}</b>
                        <hr />
                        ... TO DO FORM ...
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
