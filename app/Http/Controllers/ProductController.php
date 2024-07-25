<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
class ProductController extends Controller
{
    public function getproducts(){
        $products=DB::table("products")->get();
        foreach($products as &$product) {
            $product->more_imgs=json_decode($product->more_imgs,true);
            $averageRating = DB::table('rating')->where('product_id', $product->id)->avg('rating');
            if($averageRating < 0) {
                $roundedRating = 0;
            } else {
                $roundedRating = floor($averageRating + 0.5);
            }
            $product->rating=$roundedRating;
        }



        return response()->json(["message"=>"success" , "data"=>$products]);
    }
    public function getproduct($id){
        $product=DB::table("products")->select()->where("id","$id")->first();
            if($product) {
                $product->more_imgs=json_decode($product->more_imgs,true);
            }
        $brand_name=DB::table("brands")->select("name")->where("brand_id" , $product->brand_id)->first();
        $cat_name=DB::table("categories")->select("name")->where("cat_id" , $product->category)->first();
        $product->BrandName=$brand_name->name;
        $product->cat_name=$cat_name->name;



        $averageRating = DB::table('rating')
        ->where('product_id', $id)
        ->avg('rating');

    if($averageRating < 0) {
        $roundedRating = 0;
    } else {
        $roundedRating = floor($averageRating + 0.5);
    }
    $product->rating=$roundedRating;





        return response()->json(["message"=>"success" , "data"=>$product]);
    }

    public function filter_products(Request $request) {
       $request->validate([
            'min_price' => 'nullable|int',
            'max_price' => 'nullable|int',
            'category_id' => 'nullable|int',
            'brand_id' => 'nullable|int',
            'rating'=>'nullable|int'
    ]);
    $query = DB::table("products");

    if ($request->filled('min_price')) {
        $query->where('price', '>=', $request->input('min_price'));
    }

    if ($request->filled('max_price')) {
        $query->where('price', '<=', $request->input('max_price'));
    }

    if ($request->filled('category_id')) {
        $query->where('category', $request->input('category_id'));
    }

    if ($request->filled('brand_id')) {
        $query->where('brand_id', $request->input('brand_id'));
    }
      // Apply rating filter
    if ($request->filled('rating')) {
        $rating = $request->input('rating');
        $query->join('ratings', 'products.id', '=', 'ratings.product_id')
              ->select('products.*')
              ->groupBy('products.id')
              ->havingRaw('ROUND(AVG(ratings.rating)) >= ?', [$rating]);
    }

    $filteredProducts = $query->get();
     foreach($filteredProducts as &$product) {
            $averageRating = DB::table('rating')->where('product_id', $product->id)->avg('rating');
            if($averageRating < 0) {
                $roundedRating = 0;
            } else {
                $roundedRating = floor($averageRating + 0.5);
            }
            $product->rating=$roundedRating;
        }

    return response()->json(["message" => "success", "data" => $filteredProducts]);
}

public function getcomments($id){
    $comments = DB::table("comments")->where("product_id",$id)->get();
    return response()->json(["message"=>"success" , "data"=>$comments]);
}


public function addcomment(Request $request) {
    $request->validate([
        'customer_id' => 'required|int',
        'token' => 'required|string',
        "product_id"=>'required|int',
        "value"=>'required|string'
    ]);
    $customer = DB::table('customers')->where('id', "$request->customer_id")->first();
    $product=DB::table("products")->where("id","$request->product_id")->first();
    if (!$customer || $request->token!=$customer->token || !$product) {
        return response()->json(["message"=>"Wrong id or token"] , 401);
    }
    DB::table("comments")->insert(
        [
            "customer_id"=>$request->customer_id,
            "product_id"=>$request->product_id,
            "value"=>$request->value,
            "customer_name"=>$customer->name
        ]
    );

    return response()->json(["message"=>"success"]);
}

public function delcomment(Request $request) {
    $request->validate([
        'customer_id' => 'required|int',
        'token' => 'required|string',
        "comment_id"=>'required|int'
    ]);
    $customer = DB::table('customers')->where('id', "$request->customer_id")->first();
    $comment=DB::table("comments")->where("id",$request->comment_id)->first();

    if (!$customer || $request->token!=$customer->token ||$comment->customer_id !=$request->customer_id  ) {
        return response()->json(["message"=>"Wrong id or token"] , 401);
    }

    DB::table("comments")->where("id",$request->comment_id)->delete();
    return response()->json(["message"=>"success"]);

}


}
