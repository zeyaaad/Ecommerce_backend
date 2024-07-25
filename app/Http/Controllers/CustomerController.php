<?php

namespace App\Http\Controllers;

use Dotenv\Exception\ValidationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class CustomerController extends Controller
{
    public function register(Request $request){

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:customers',
            'password' => 'required|string|min:8|max:30',
            'phone' => ['required', 'regex:/^(01[0125][0-9]{8})$/'],
        ]);

        DB::table('customers')->insert([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'phone'=>$request->phone
        ]);
        return response()->json(["message"=>"success"]);

    }


    public function login(Request $request) {
         $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        $customer = DB::table('customers')->where('email', $request->email)->first();

        if (!$customer || !Hash::check($request->password, $customer->password)) {
            return response()->json(["message"=>"Wrong Email or password"] , 401);
        }
        $token=$this->generateRandomToken();
        DB::table("customers")->where("id",$customer->id)->update(["token"=>$token]);
        $customer->token=$token;
        return response()->json(["message"=>"success" , "token"=>$token,"user"=>$customer]);
    }


    public function logout(Request $request) { // id ,token
        $request->validate([
            'id' => 'required|int',
            'token' => 'required|string',
        ]);

        $customer = DB::table('customers')->where('id', $request->id)->first();

        if (!$customer || $request->token!=$customer->token) {
            return response()->json(["message"=>"Wrong id or token"] , 401);
        }

        DB::table("customers")->where("id",$customer->id)->update(["token"=>null]);
        return response()->json(["message"=>"success"]);
    }


    public function checklogin( Request $request){ // id , token
        $request->validate([
            'id' => 'required|int',
            'token' => 'required|string',
        ]);

        $customer = DB::table('customers')->where('id', $request->id)->first();

        if (!$customer || $request->token!=$customer->token) {
            return response()->json(["message"=>false] );
        }

        return response()->json(["message"=>true]);
    }


    public function changepass(Request $request) {
        $request->validate([
            'id' => 'required|int',
            'token' => 'required|string',
            "currnet_pass"=>'required|string',
            "new_pass"=>'required|string'
        ]);
        $customer = DB::table('customers')->where('id', $request->id)->first();

        if (!$customer || $customer->token !=$request->token) {
            return response()->json(["message"=>"Wrong id or token"] , 401);
        }
        if (!Hash::check($request->currnet_pass, $customer->password) ){
            return response()->json(["message"=>"Worng Currnet password"] , 401);
        }
        DB::table("customers")->where('id', $request->id)
        ->update([
            "password"=>Hash::make($request->new_pass)
        ]);
        return response()->json(["message"=>"success"] );

    }

    public function changedata(Request $request) {
         $request->validate([
            'id' => 'required|int',
            'token' => 'required|string',
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:customers',
            'phone' => ['required', 'regex:/^(01[0125][0-9]{8})$/']
        ]);
        $customer = DB::table('customers')->where('id', $request->id)->first();

        if (!$customer || $customer->token !=$request->token) {
            return response()->json(["message"=>"Wrong id or token"] , 401);
        }
        DB::table('customers')->where("id",$request->id)->update(
            [
                "name"=>$request->name,
                "email"=>$request->email,
                "phone"=>$request->phone
            ]
            );
            return response()->json(["message"=>"success"]);

    }
    public function getuserdata(Request $request) {
         $request->validate([
            'id' => 'required|int',
            'token' => 'required|string'
        ]);
        $customer = DB::table('customers')->where('id', $request->id)->first();

        if (!$customer || $customer->token !=$request->token) {
            return response()->json(["message"=>"Wrong id or token"] , 401);
        }

        return response()->json(["message"=>"success","data"=>$customer]);

    }

   function generateRandomToken() {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ-_';
    $charactersLength = strlen($characters);

    $length = rand(150, 250);

    $token = '';

    for ($i = 0; $i < $length; $i++) {
        $token .= $characters[rand(0, $charactersLength - 1)];
    }

    return $token;
}




}

/*

    cart(add,update,delete,read) ,
    wistlt(add,delete,read) ,
    comments,
    rating ,
    get all products ,
    get specif product ,
    get brands ,
    get categories ,


    comments:
        customer_id,product_id , value , date
*/
