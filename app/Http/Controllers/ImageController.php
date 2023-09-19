<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\App;

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
            $environment = $_ENV;
            $newImage = $request->file('newImage');    

            $cloud_name = "de9d1foso";
            $preset = $environment['CLOUDINARY_UPLOAD_PRESET'];

            $url = "https://api.cloudinary.com/v1_1/$cloud_name/image/upload";
            $data = ['upload_preset' => "$preset", 'file' => $newImage];

            dd($url, $data);

            // use key 'http' even if you send the request to https://...
            $options = [
                'http' => [
                    'header' => "Content-type: application/x-www-form-urlencoded\r\n",
                    'method' => 'POST',
                    'content' => http_build_query($data),
                ],
            ];

            $context = stream_context_create($options);
            $result = file_get_contents($url, false, $context);
            if ($result === false) {
                /* Handle error */
            }

            var_dump($result);
        } catch (\Throwable $th) {
            //throw $th;
        }

        

        // dd($request);

    }
}
