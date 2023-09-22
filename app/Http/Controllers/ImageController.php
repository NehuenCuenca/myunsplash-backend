<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;
use App\Models\Image;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
use Illuminate\Support\Facades\Artisan;


class ImageController extends Controller
{
    public function getAllImages(Request $request)
    {

        $allImages= DB::table('images')->get();
        //dd($allImages);

        if( $allImages->isEmpty() ){
            return response()->json([
                "msj" => "Error",
                "razon" => "There are no images saved :("
            ]);
        } else {
            return response()->json([
                "msj" => "All images",
                "length" => count($allImages),
                "data" => $allImages
            ]);
        }
    }

    public function getImageById( $image_id, Request $request)
    {

        $image= DB::table('images')
                        ->where('id', '=', $image_id)
                        ->get();
        //dd($image);

        if( $image->isEmpty() ){
            return response()->json([
                "msj" => "Error",
                "razon" => "The image $image_id doesn't exist :("
            ]);
        } else {
            return response()->json([
                "msj" => "Image",
                // "length" => count($image),
                "data" => $image
            ]);
        }
    }
    
    public function addImage( Request $request)
    {
        if (!$request->hasFile('newImage')) {
            return response()->json([
                "msg" => "There is no image on the request",
            ], 400);
        }

        try {            
            $nowTimestamp = Carbon::now()->timestamp;
            // Sending to cloudinary
            $uploadedFileUrl = $request->file('newImage')
                                       ->storeOnCloudinaryAs('assets', $request->name.$nowTimestamp);
            // dd($uploadedFileUrl->getSecurePath(), $uploadedFileUrl->getPublicId());
           
            // Save on the DB
            $savedImage = Image::create([
                'name' => $request->name,
                'url'  => $uploadedFileUrl->getSecurePath(),
                'public_id' => $uploadedFileUrl->getPublicId(),
            ]);
            
            return response()->json([
                "msg"  => "Great, image saved on database!",
                "data" => $savedImage->getOriginal(),
            ], 200); 
        } catch (\Throwable $th) {
            // dd($th);
            return response()->json([
                "msg" => "Something explode",
                "error" => $th->getMessage(),
            ], 500);
        }

    }

    public function deleteImage($image_id, Request $request){
        
        $imageToDelete = Image::where('id', $image_id)->first(); 
        if(!$imageToDelete){
            return response()->json([
                "msg" => "Image $image_id doesn't exist!.",
            ], 500);
        }

        Cloudinary::admin()->deleteAssets([$imageToDelete->public_id]); // Delete from Cloudinary
        $imageToDelete->delete(); //Delete from DB

        return response()->json([
            "msg" => "Image $image_id deleted succesfully.",
        ], 200);
    }
}
