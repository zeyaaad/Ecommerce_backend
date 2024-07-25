<?php

use App\Http\Controllers\BrandController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\RatingController;
use App\Http\Controllers\WishlistController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Stripe\Stripe;
use Stripe\Checkout\Session;
use Illuminate\Support\Facades\Log;
use Stripe\Webhook;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::group(["prefix"=>"eccomerce"],function(){
    Route::group(["prefix"=>"auth"] ,function(){

        Route::post("register" , [CustomerController::class,"register"]);
        Route::post("login" , [CustomerController::class,"login"]);
        Route::post("logout" , [CustomerController::class,"logout"]);
        Route::post("checklogin" , [CustomerController::class,"checklogin"]);
        Route::post("changepass" , [CustomerController::class,"changepass"]);
        Route::post("changedata" , [CustomerController::class,"changedata"]);
        Route::post("getuserdata" , [CustomerController::class,"getuserdata"]);
    });

    Route::group(["prefix"=>"products"] ,function(){
        Route::get("/" , [ProductController::class,"getproducts"]);
        Route::get("/{id}" , [ProductController::class,"getproduct"]);
        Route::post("/filter" , [ProductController::class,"filter_products"]);
        Route::group(["prefix"=>"comments"] , function(){
            Route::get("/{id}" , [ProductController::class,"getcomments"]);
            Route::post("/" , [ProductController::class,"addcomment"]);
            Route::delete("/" , [ProductController::class,"delcomment"]);
        });

    });

    Route::group(["prefix"=>"cart"] , function(){
        Route::get("/" , [CartController::class,"getcart"]);
        Route::post("/" , [CartController::class,"addtocart"]);
        Route::put("/" , [CartController::class,"updatecart"]);
        Route::delete("/" , [CartController::class,"delcart"]);
        Route::delete("/clear" , [CartController::class,"clearcart"]);
        Route::get("/count" , [CartController::class,"countcart"]);
        Route::get("/getids" , [CartController::class,"getidproducts"]);
        Route::post("/order" , [CartController::class,"order"]);

    });
    Route::group(["prefix"=>"wishlist"] , function(){
        Route::get("/" , [WishlistController::class,"getwishlist"]);
        Route::post("/" , [WishlistController::class,"addtowishlist"]);
        Route::delete("/" , [WishlistController::class,"delwishlist"]);
        Route::delete("/clear" , [WishlistController::class,"clearwishlist"]);
        Route::get("/count" , [WishlistController::class,"countwishlist"]);
        Route::get("/getids" , [WishlistController::class,"getidproducts"]);
    });
    Route::group(["prefix"=>"brands"] , function(){
        Route::get("/" , [BrandController::class,"getbrans"]);
        Route::get("/getproducts/{id}" , [BrandController::class,"getProductsbrand"]);
    });
    Route::group(["prefix"=>"categories"] , function(){
        Route::get("/" , [CategoryController::class,"getcategories"]);
        Route::get("/getproducts/{id}" , [CategoryController::class,"getProductscategory"]);
    });
    Route::group(["prefix"=>"rating"] , function(){
        Route::post('/add', [RatingController::class, 'addRating']);
        Route::delete('/del', [RatingController::class, 'removeRating']);
        Route::get('/average/{productId}', [RatingController::class, 'getAverageRating']);
        Route::post('/check', [RatingController::class, 'checkCustomerRating']);
    });



Route::post('/checkout', function (Request $request) {
    Stripe::setApiKey(env('STRIPE_SECRET'));

    $line_items = [];

    foreach ($request->input('cart') as $item) {
        $line_items[] = [
            'price_data' => [
                'currency' => 'usd',
                'product_data' => [
                    'name' => $item['name']

                ],
                'unit_amount' => $item['main_price'] * 100,
            ],
            'quantity' => $item['quantity'],
        ];
    }

    $session = Session::create([
        'payment_method_types' => ['card'],
        'line_items' => $line_items,
        'mode' => 'payment',
        'success_url' => $request->input('success_url'),
        'cancel_url' => $request->input('cancel_url'),
        'metadata' => [
            'customer_id' => $request->input('customer_id') // Include the customer ID here
        ]
    ]);

    return response()->json(['id' => $session->id]);
});


});
