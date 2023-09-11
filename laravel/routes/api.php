<?php

use App\Models\AircraftAirportService\Repository;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::get('/aircraft_airports', function (Request $request, Response $response, Repository $repository) {
    $tail = $request->get('tail');
    $date_from = $request->get('date_from');
    $date_to = $request->get('date_to');

    $result = $repository->visitedAirports($tail, $date_from, $date_to);
    return $response->setContent($result);
});

