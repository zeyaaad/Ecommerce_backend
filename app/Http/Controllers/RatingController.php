<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RatingController extends Controller
{
   public function addRating(Request $request)
{
    // Validate incoming request
    $request->validate([
        'customer_id' => 'required|exists:customers,id',
        'product_id' => 'required|exists:products,id',
        'rating' => 'required|integer|between:1,5',
    ]);

    // Check if a rating already exists for the given customer and product
    $existingRating = DB::table('rating')
                        ->where('customer_id', $request->customer_id)
                        ->where('product_id', $request->product_id)
                        ->first();

    if ($existingRating) {
        // If a rating exists, update it
        DB::table('rating')
            ->where('customer_id', $request->customer_id)
            ->where('product_id', $request->product_id)
            ->update(['rating' => $request->rating]);

        return response()->json(['message' => 'Rating updated successfully']);
    } else {
        // If no rating exists, insert a new rating
        DB::table('rating')->insert([
            'customer_id' => $request->customer_id,
            'product_id' => $request->product_id,
            'rating' => $request->rating,
        ]);

        return response()->json(['message' => 'Rating added successfully']);
    }
}

    public function removeRating(Request $request){
    $request->validate([
        'customer_id' => 'required|exists:customers,id',
        'product_id' => 'required|exists:products,id',
    ]);

    $deleted = DB::table('rating')
                 ->where('customer_id', $request->customer_id)
                 ->where('product_id', $request->product_id)
                 ->delete();

    if ($deleted) {
        return response()->json(['message' => 'Rating removed successfully']);
    }

    return response()->json(['message' => 'Rating not found'], 404);
}

public function getAverageRating($productId)
{
    $averageRating = DB::table('rating')
        ->where('product_id', $productId)
        ->avg('rating');

    if ($averageRating < 0) {
        $roundedRating = 0;
    } else {
        $roundedRating = floor($averageRating + 0.5);
    }

    return response()->json([
        'product_id' => $productId,
        'average_rating' => $roundedRating
    ]);
}

public function checkCustomerRating(Request $request)
{
    $request->validate([
        'customer_id' => 'required|exists:customers,id',
        'product_id' => 'required|exists:products,id',
    ]);

    $ratingExists = DB::table('rating')
                      ->where('customer_id', $request->customer_id)
                      ->where('product_id', $request->product_id)
                      ->first();
    if($ratingExists) {
        $rate=$ratingExists->rating;
    } else {
        $rate=0;
    }
    return response()->json(['rating_exists' => $rate]);
}

}
