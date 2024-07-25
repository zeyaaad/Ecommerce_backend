<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
class CartController extends Controller
{
        public function getcart(Request $request) { // cutomet_id , token
         $request->validate([
            'customer_id' => 'required|int',
            'token' => 'required|string',
        ]);
        $customer = DB::table('customers')->where('id', "$request->customer_id")->first();
        if (!$customer || $request->token!=$customer->token) {
            return response()->json(["message"=>"Wrong id or token"] , 401);
        }
        $cart_products=DB::table("cart_items")->where("customer_id","$request->customer_id")->get();
        $totalPrice=0;
        $countitms=0;
        foreach($cart_products as &$prod){
            $countitms+=1;
            $prod->main_price=$prod->price/$prod->quantity;
            $product=DB::table("products")->where("id","$prod->product_id")->first();
            if($product){
                $prod->name=$product->name ;
                $prod->img=$product->main_img;
            }
            $totalPrice+=$prod->price;
        }
        return response()->json(["message"=>"success" ,"total"=>$totalPrice,"count"=>$countitms, "data"=>$cart_products] );

    }

    public function addtocart(Request $request){ // customer_id , token , product_id
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


        DB::table("cart_items")->insert([
            "customer_id"=>$request->customer_id,
            "product_id"=>$request->product_id,
            "price"=>$product->price
        ]);

        return response()->json(["message"=>"success"]);


    }


    public function updatecart(Request $request) {
        $request->validate([
            'customer_id' => 'required|int',
            'token' => 'required|string',
            "product_id"=>'required|int',
            "quantity"=>'required|int|min:1'
        ]);
        $customer = DB::table('customers')->where('id', "$request->customer_id")->first();
        $product=DB::table("products")->where("id","$request->product_id")->first();
        if (!$customer || $request->token!=$customer->token || !$product) {
            return response()->json(["message"=>"Wrong id or token"] , 401);
        }

        DB::table("cart_items")->where("product_id" , $request->product_id)
        ->update([
            "quantity"=>$request->quantity,
            "price"=>$request->quantity*$product->price
        ]);
        return response()->json(["message"=>"success"]);
    }

    public function delcart(Request $request) {
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

        DB::table("cart_items")->where("product_id" , $request->product_id)->delete();
        return response()->json(["message"=>"success"]);
    }

    public function clearcart(Request $request) {
        $request->validate([
            'customer_id' => 'required|int',
            'token' => 'required|string'
        ]);
        $customer = DB::table('customers')->where('id', "$request->customer_id")->first();
        if (!$customer || $request->token!=$customer->token) {
            return response()->json(["message"=>"Wrong id or token"] , 401);
        }

        DB::table("cart_items")->where("customer_id" , $request->customer_id)->delete();
        return response()->json(["message"=>"success"]);
    }

     public function countcart(Request $request) {
        $request->validate([
            'customer_id' => 'required|int',
            'token' => 'required|string'
        ]);
        $customer = DB::table('customers')->where('id', "$request->customer_id")->first();
        if (!$customer || $request->token!=$customer->token) {
            return response()->json(["message"=>"Wrong id or token"] , 401);
        }

        $products=DB::table("cart_items")->where("customer_id" , $request->customer_id)->get();
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
        $ids=DB::table("cart_items")->select("product_id")->where("customer_id",$request->customer_id)->get();
        $allids=[];
        foreach($ids as $id) {
            $allids[]=$id->product_id;
        }
        return response()->json(["message"=>"success" , "data"=>$allids]);

    }
    public function checkincart(Request $request) { // token , user,product
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

        $ids=DB::table("cart_items")->select("product_id")->where("customer_id",$request->customer_id)->get();
        $allids=[];
        foreach($ids as $id) {
            $allids[]=$id->product_id;
        }

        $type=true;
        if(in_array($request->product_id , $allids)) {
            $type=false;
        }
        return response()->json(["message"=>"success" , "type"=>$type]);

    }



    public function order(Request $request){ // total , products_id [], customer_id
        $request->validate([
            'customer_id' => 'required|int',
            'token' => 'required|string',
            'total' => 'required|int',
            "products"=>'required|array',
        ]);
        $customer = DB::table('customers')->where('id', "$request->customer_id")->first();
        if (!$customer || $request->token!=$customer->token ) {
            return response()->json(["message"=>"Wrong id or token"] , 401);
        }

        DB::table("orders")->insert([
            "customer_id"=>$request->customer_id,
            "total"=>$request->total,
            "products"=>json_encode($request->products)
        ]);
        return response()->json(["messange"=>"success"]);

    }
}
