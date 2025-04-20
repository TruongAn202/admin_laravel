<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;//api auth
use App\Http\Controllers\UserInfoController;//api user
use App\Http\Controllers\BookController;//api ngau nhien sach 6
use App\Http\Controllers\OrderController;//api quan ly don hang(lich su, payment)
use App\Http\Controllers\UploadController;//up anh
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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
Route::post('/login', [AuthController::class, 'login']);//api login
Route::post('/register', [AuthController::class, 'register']);//api dang ky
Route::get('/user-info', [UserInfoController::class, 'getUserInfo']);//user info
Route::put('/user-info/update', [UserInfoController::class, 'updateUserInfo']);//cap nhat user info
Route::get('/sach/ngau-nhien', [BookController::class, 'layNgauNhien']);//api ngau nhien sach 6
Route::get('/sach/theo-loai', [BookController::class, 'layTheoLoai']);
Route::get('/sach/theo-id', [BookController::class, 'getProductById']);
Route::get('/sach/tim-kiem', [BookController::class, 'search']);//tim kiem
Route::get('/orders', [OrderController::class, 'getOrdersByEmail']);//don hang by mail
Route::post('/checkout', [OrderController::class, 'checkOut']);//thanh toan
Route::post('/upload-image', [UploadController::class, 'upload']);


