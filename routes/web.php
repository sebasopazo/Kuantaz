<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\BeneficioAggregatedController;

Route::get('/', function () {
    return view('welcome');
});
Route::get('/beneficios-procesados', [BeneficioAggregatedController::class, 'index']);
