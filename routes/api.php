<?php

use App\Http\Controllers\api\LandApi;
use App\Http\Controllers\api\LoginApi;
use App\Http\Controllers\api\ProductApi;
use App\Http\Controllers\api\RegionApi;
use App\Http\Controllers\api\RegisterApi;
use App\Http\Controllers\api\RentApi;
use App\Http\Controllers\api\TutorialApi;
use App\Http\Controllers\api\UserApi;
use App\Http\Controllers\api\ProductCategoryApi;
use App\Http\Controllers\api\PurchaseProductApi;
use App\Http\Controllers\api\DurianPayApi;
use App\Http\Controllers\api\LandWishlistApi;
use App\Http\Controllers\api\LogMessageApi;
use App\Http\Controllers\api\PaymentApi;
use App\Http\Controllers\api\ProductWishlistApi;
use App\Http\Controllers\api\RajaOngkirApi;
use App\Http\Controllers\api\ReviewLandApi;
use App\Http\Controllers\api\ReviewProductApi;
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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('/greeting', function () {
    return 'Hello World';
});

Route::get('/test_paginate', [LandApi::class, 'testing_paginate']);

Route::middleware(['checkAuthApi'])->group(function () {
    // Manage User
    Route::get('/user', [UserApi::class, 'index']);
    Route::post('/user', [UserApi::class, 'store']);
    Route::post('/user_update', [UserApi::class, 'update']);
    Route::delete('/user', [UserApi::class, 'delete']);
    Route::put('/update_token', [UserApi::class, 'update_token_user']);

    // Manage Product
    Route::post('/product', [ProductApi::class, 'store']);
    Route::get('/product_user', [ProductApi::class, 'product_user']);
    Route::post('/product_update', [ProductApi::class, 'update']);
    Route::delete('/product', [ProductApi::class, 'delete']);
    Route::put('/verify_product', [ProductApi::class, 'verify']);
    Route::get('/list_product_admin', [ProductApi::class, 'list_product_web']);

    // Product Wishlist
    Route::get('/product_wishlist', [ProductWishlistApi::class, 'index']);
    Route::post('/product_wishlist', [ProductWishlistApi::class, 'store']);
    Route::delete('/product_wishlist', [ProductWishlistApi::class, 'destroy']);

    // Manage Product Category
    // Route::get('/product_category', [ProductCategoryApi::class, 'index']);

    // Manage Lahan
    Route::post('/land', [LandApi::class, 'store']);
    Route::get('/land_user', [LandApi::class, 'land_user']);
    Route::post('/land_update', [LandApi::class, 'update']);
    Route::delete('/land', [LandApi::class, 'delete']);
    Route::put('/verify_land', [LandApi::class, 'verify']);
    Route::put('/change_status_land', [LandApi::class, 'status_land']);
    Route::get('/land_web_admin', [LandApi::class, 'index_web']);

    // Land Wishlist
    Route::get('/land_wishlist', [LandWishlistApi::class, 'index']);
    Route::post('/land_wishlist', [LandWishlistApi::class, 'store']);
    Route::delete('/land_wishlist', [LandWishlistApi::class, 'destroy']);

    // Sewa Lahan
    Route::get('/rent', [RentApi::class, 'index']);
    Route::get('/rent_web_admin', [RentApi::class, 'index_web']);
    Route::get('/detailrent', [RentApi::class, 'detail']);
    Route::post('/rent', [RentApi::class, 'store']);
    Route::post('/evidence_rent', [RentApi::class, 'evidence_upload']);
    Route::post('/verif_rent', [RentApi::class, 'verif_rent']);

    // Manage Tutorial & Tutorial Detail
    Route::post('/tutorial', [TutorialApi::class, 'store_tutorial']);
    Route::post('/tutorial_update', [TutorialApi::class, 'update_tutorial']);
    Route::delete('/tutorial', [TutorialApi::class, 'delete_tutorial']);
    Route::post('/tutorial_detail', [TutorialApi::class, 'store_tutorial_detail']);
    Route::post('/tutorial_detail_update', [TutorialApi::class, 'update_tutorial_detail']);
    Route::delete('/tutorial_detail', [TutorialApi::class, 'delete_tutorial_detail']);

    // Manage Purchase Product
    Route::post('/purchase_product', [PurchaseProductApi::class, 'store']);
    Route::get('/list_purchase_product', [PurchaseProductApi::class, 'list_purchase']);
    Route::get('/list_purchase_product_web_admin', [PurchaseProductApi::class, 'list_purchase_web']);
    Route::get('/detail_purchase_product', [PurchaseProductApi::class, 'detail']);
    Route::post('/evidence_product', [PurchaseProductApi::class, 'evidence_upload']);
    Route::post('/verif_purchase_product', [PurchaseProductApi::class, 'verif_purchase_product']);

    // Log Message / Notif
    Route::get('/message_notif', [LogMessageApi::class, 'index']);
    Route::put('/message_notif', [LogMessageApi::class, 'update']);

    // Review Lahan
    Route::post('/review_land', [ReviewLandApi::class, 'store']);

    // Review Produk
    Route::post('/review_product', [ReviewProductApi::class, 'store']);
});
// Web
// Lahan
Route::get('/land', [LandApi::class, 'index']);
Route::get('/detailland', [LandApi::class, 'detail']);

// Product
Route::get('/product', [ProductApi::class, 'index']);
Route::get('/list_product', [ProductApi::class, 'list_product']);
Route::get('/product_category', [ProductCategoryApi::class, 'index']);

// Review
Route::get('/review_land', [ReviewLandApi::class, 'index']);
Route::get('/review_product', [ReviewProductApi::class, 'index']);

// Tutorial
Route::get('/tutorial', [TutorialApi::class, 'index_tutorial']);
Route::get('/tutorial_detail', [TutorialApi::class, 'index_tutorial_detail']);

Route::post('/register', [RegisterApi::class, 'register']);
Route::get('/verify', [RegisterApi::class, 'verify']);
Route::post('/login', [LoginApi::class, 'login']);
Route::post('/resendOTP', [RegisterApi::class, 'resendOTP']);

Route::get('/province', [RegionApi::class, 'province']);
Route::get('/city', [RegionApi::class, 'city']);
Route::get('/district', [RegionApi::class, 'district']);

Route::get('/province_raja_ongkir', [RajaOngkirApi::class, 'provinceRajaOngkir']);
Route::get('/city_raja_ongkir', [RajaOngkirApi::class, 'cityRajaOngkir']);
Route::post('/ship_cost', [RajaOngkirApi::class, 'ship_cost']);

// Orders
Route::get("tes", [DurianPayApi::class, 'tes']);
Route::post("createorder", [DurianPayApi::class, 'create_order']);
Route::get("fetchorders", [DurianPayApi::class, 'fetch_orders']);
Route::get("fetchsingle", [DurianPayApi::class, 'fetch_single']);
Route::post("createpaymentlink", [DurianPayApi::class, 'create_payment_link']);

// Payments
Route::post("createpaymentcharge", [DurianPayApi::class, 'create_payment_charge']);
Route::get("fetchpayments", [DurianPayApi::class, 'fetch_payments']);
Route::get("fetchpayments_single", [DurianPayApi::class, 'fetch_payments_single']);
Route::get("payments_status", [DurianPayApi::class, 'payments_status']);
Route::post("verify_payments", [DurianPayApi::class, 'verify_payments']);
Route::put("cancel_payments", [DurianPayApi::class, 'cancel_payments']);

// Payments Midtrans
Route::post("create_payment_link", [PaymentApi::class, 'create_payment_link']);
Route::delete("delete_payment_link/{id}", [PaymentApi::class, 'delete_payment_link']);
Route::post("paymidtrans_handler", [PaymentApi::class, 'payment_handler']);
