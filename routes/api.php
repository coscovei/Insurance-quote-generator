<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\InsuranceController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/offer', [InsuranceController::class, 'createOffer']);


Route::get('/requests', function () {
    return App\Models\InsuranceRequest::with([
        'vehicle',
        'offers',
        'policyholder.personable'
    ])->latest()->get();
});

Route::post('/checkout', [InsuranceController::class, 'checkout']);

