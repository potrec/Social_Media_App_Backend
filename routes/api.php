<?php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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
Route::get('/products', function () {
    return products::all();
});

Route::post('/products', function (Request $request) {
    return response()->json([
        'message' => 'Product created successfully',
        'product' => $request->all()
    ]);
});

Route::get('/user', function () {
    return 'user';
});

Route::post('/user', function (Request $request) {
    return response()->json([
        'message' => 'User created successfully',
        'user' => $request->all()
    ]);
});

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});