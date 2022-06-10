<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Log;

use App\Http\Controllers\RecordController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\AbonementController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Log::info("\n.....");

/*
 * все уведомления от yclients
 */
Route::post('/yclients', function (Request $request) {

    Log::info($request);

    switch ($request->post('resource')) {

        case 'record' :

            return app('App\Http\Controllers\RecordController')->index($request);

        case 'finances_operation' :

            return app('App\Http\Controllers\TransactionController')->create($request);

        case 'goods_operations_sale' :

            return app('App\Http\Controllers\AbonementController')->create($request);
            
        default :
            Log::info('Не найден вариант роута : '.$request->post('resource'));
    }
});

/*
 * крон ожидания оплаты
 */
Route::post('/abonements/pay', [AbonementController::class, 'pay']);
