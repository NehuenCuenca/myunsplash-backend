<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;
use App\Models\Image;


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
            $newImage = $request->file('newImage');    
            
            $environment = $_ENV;
            $preset = $environment['CLOUDINARY_UPLOAD_PRESET'];
            $cloud_name = "de9d1foso";

            $urlCloudinary = "https://api.cloudinary.com/v1_1/$cloud_name/image/upload";

            $imgEncoded = 'data:image/png;base64,'.base64_encode($newImage->get());

            $data = array(
                'upload_preset' => $preset,
                'file' => $imgEncoded,
            );

            $postvars = http_build_query($data) . "\n";

            // Sending to cloudinary
            $ch = curl_init();

            curl_setopt($ch, CURLOPT_URL, $urlCloudinary);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postvars);

            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

            $server_output = curl_exec ($ch);

            $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            
            curl_close ($ch);
            $respDecoded = json_decode($server_output);

            if($httpcode >= 400){
                throw ValidationException::withMessages([
                    'error' => 'Something wrong: '.$respDecoded->error->message
                ]);
            }

            // Save on the DB
            $savedImage = Image::create([
                'name' => $request->name,
                'url' => $respDecoded->secure_url,
            ]);
            
            return response()->json([
                "msg" => "Great, image saved on database!",
                "data" => $savedImage->getOriginal(),
            ], 200); 
        } catch (\Throwable $th) {
            return response()->json([
                "msg" => "Algo exploto",
                "error" => $th->getMessage(),
            ], 500);
        }

    }
}
