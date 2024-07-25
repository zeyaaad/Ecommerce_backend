<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


class WishlistController extends Controller
{
        public function getwishlist(Request $request) { // cutomet_id , token
        $request->validate([
        'customer_id' => 'required|int',
        'token' => 'required|string',
    ]);
    $customer = DB::table('customers')->where('id', "$request->customer_id")->first();
    if (!$customer || $request->token!=$customer->token) {
        return response()->json(["message"=>"Wrong id or token"] , 401);
    }
    $wishlist_products=DB::table("wishlist")->where("customer_id","$request->customer_id")->get();
    $count=0;
    foreach($wishlist_products as &$prod){
        $count+=1;
        $product=DB::table("products")->where("id","$prod->product_id")->first();
        if($product){
            $prod->name=$product->name ;
            $prod->img=$product->main_img;
        }
    }
    return response()->json(["message"=>"success" ,"count"=>$count,"data"=>$wishlist_products] );

}

    public function addtowishlist(Request $request){ // customer_id , token , product_id
         $request->validate([
            'customer_id' => 'required|int',
            'token' => 'required|string',
            "product_id"=>'required|int'
        ]);
        $customer = DB::table('customers')->where('id', "$request->customer_id")->first();
        $product=DB::table("products")->where("id","$request->product_id")->first();
        if (!$customer || $request->token!=$customer->token || !$product) {
            return response()->json(["message"=>"Wrong id or token"] , 401);
        }


        DB::table("wishlist")->insert([
            "customer_id"=>$request->customer_id,
            "product_id"=>$request->product_id
        ]);

        return response()->json(["message"=>"success"]);


    }
    public function delwishlist(Request $request) {
        $request->validate([
            'customer_id' => 'required|int',
            'token' => 'required|string',
            "product_id"=>'required|int',
        ]);
        $customer = DB::table('customers')->where('id', "$request->customer_id")->first();
        $product=DB::table("products")->where("id","$request->product_id")->first();
        if (!$customer || $request->token!=$customer->token || !$product) {
            return response()->json(["message"=>"Wrong id or token"] , 401);
        }

        DB::table("wishlist")->where("product_id" , $request->product_id)->delete();
        return response()->json(["message"=>"success"]);
    }

    public function clearwishlist(Request $request) {
        $request->validate([
            'customer_id' => 'required|int',
            'token' => 'required|string'
        ]);
        $customer = DB::table('customers')->where('id', "$request->customer_id")->first();
        if (!$customer || $request->token!=$customer->token) {
            return response()->json(["message"=>"Wrong id or token"] , 401);
        }

        DB::table("wishlist")->where("customer_id" , $request->customer_id)->delete();
        return response()->json(["message"=>"success"]);
    }

     public function countwishlist(Request $request) {
        $request->validate([
            'customer_id' => 'required|int',
            'token' => 'required|string'
        ]);
        $customer = DB::table('customers')->where('id', "$request->customer_id")->first();
        if (!$customer || $request->token!=$customer->token) {
            return response()->json(["message"=>"Wrong id or token"] , 401);
        }

        $products=DB::table("wishlist")->where("customer_id" , $request->customer_id)->get();
        return response()->json(["message"=>"success","count"=>count($products)]);
    }

      public function getidproducts(Request $request) {
         $request->validate([
            'customer_id' => 'required|int',
            'token' => 'required|string'
        ]);
        $customer = DB::table('customers')->where('id', "$request->customer_id")->first();
        if (!$customer || $request->token!=$customer->token) {
            return response()->json(["message"=>"Wrong id or token"] , 401);
        }
        $ids=DB::table("wishlist")->select("product_id")->where("customer_id",$request->customer_id)->get();
        $allids=[];
        foreach($ids as $id) {
            $allids[]=$id->product_id;
        }
        return response()->json(["message"=>"success" , "data"=>$allids]);

    }
}
