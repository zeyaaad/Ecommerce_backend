<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BrandController extends Controller
{
    public function getbrans() {
        $brands=DB::table("brands")->get();
        return response()->json(["message"=>"success" , "data"=>$brands]);
    }

    public function getProductsbrand($id) {
        $products=DB::table("products")->where("brand_id",$id)->get();
        return response()->json(["message"=>"success" , "data"=>$products]);

    }
}
