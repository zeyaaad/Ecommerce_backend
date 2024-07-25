<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


class CategoryController extends Controller
{
        public function getcategories() {
        $categories=DB::table("categories")->get();
        foreach ($categories as &$cat) {
            $prducts=DB::table("products")->select()->where("category",$cat->cat_id)->get();
            $number=count($prducts);
            $cat->number_products=$number;
        }
        return response()->json(["message"=>"success" , "data"=>$categories]);
    }

    public function getProductscategory($id) {
        $products=DB::table("products")->where("category",$id)->get();
        $cat_name=DB::table("categories")->select("name")->where("cat_id",$id)->first()->name;
        foreach($products as &$product) {
            $averageRating = DB::table('rating')->where('product_id', $product->id)->avg('rating');
            if($averageRating < 0) {
                $roundedRating = 0;
            } else {
                $roundedRating = floor($averageRating + 0.5);
            }
            $product->rating=$roundedRating;
        }
        return response()->json(["message"=>"success" ,"cat_name"=>$cat_name ,"data"=>$products] );

    }
}


/*


    filter => price(min,max),category,brands,
    comments ,
    rating

*/
