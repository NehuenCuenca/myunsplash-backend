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

use Illuminate\Support\Str;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\File\File;


class ImageController extends Controller
{
    public function getAllImages(Request $request)
    {

        $allImages= Image::orderBy('id', 'DESC')->get();
        //dd($allImages);

        if( $allImages->isEmpty() ){
            return response()->json([
                "msg" => "Error",
                "reason" => "There are no images saved :("
            ]);
        } else {
            return response()->json([
                "msg" => "All images",
                "images" => $allImages
            ]);
        }
    }

    public function getImagesByNameMatch($name_match, Request $request)
    {
        $imagesByNameMatch = Image::where('name', 'like', '%' . $name_match . '%')->get();

        if( $imagesByNameMatch->isEmpty() ){
            $allImages = Image::all();

            return response()->json([
                "msg" => "Error",
                "reason" => "There are no images that match the name: '$name_match'",
                "images" => $allImages
            ], 400);
        }

        return response()->json([
            "msg" => "Images that matches with the name: '$name_match'",
            "images" => $imagesByNameMatch
        ], 200);
    }

    public function getImageById( $image_id, Request $request)
    {

        $image= Image::where('id', '=', $image_id)
                        ->get();
        //dd($image);

        if( $image->isEmpty() ){
            return response()->json([
                "msg" => "Error",
                "reason" => "The image $image_id doesn't exist :("
            ]);
        } else {
            return response()->json([
                "msg" => "Image found",
                "image" => $image
            ]);
        }
    }
    
    public function addImage( Request $request)
    {
        // $out = new \Symfony\Component\Console\Output\ConsoleOutput();
        // $out->writeln('Something');


        // https://stackoverflow.com/questions/58509456/how-to-convert-base64-image-to-uploadedfile-laravel
        $base64File = $request->newImage;

        // decode the base64 file
        $fileData = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $base64File));
            
        // save it to temporary dir first.
        $tmpFilePath = sys_get_temp_dir() . '/' . Str::uuid()->toString();
        file_put_contents($tmpFilePath, $fileData);
            
        // this just to help us get file info.
        $tmpFile = new File($tmpFilePath);
            
        $file = new UploadedFile(
            $tmpFile->getPathname(),
            $tmpFile->getFilename(),
            $tmpFile->getMimeType(),
            0,
            true // Mark it as test, since the file isn't from real HTTP POST.
        );


        if (!$request->newImage) {
            return response()->json([
                "msg" => "Error",
                "reason" => "There is no image on the request",
            ], 400);
        }

        try {            
            $nowTimestamp = Carbon::now()->timestamp;
            // Sending to cloudinary
            $uploadedFileUrl = $file->storeOnCloudinaryAs('assets', $request->name.$nowTimestamp);
            // dd($uploadedFileUrl->getSecurePath(), $uploadedFileUrl->getPublicId());
           
            // Save on the DB
            $savedImage = Image::create([
                'name' => $request->name,
                'url'  => $uploadedFileUrl->getSecurePath(),
                'public_id' => $uploadedFileUrl->getPublicId(),
            ]);
            
            return response()->json([
                "msg"  => "Great, image saved on database!",
                "image" => $savedImage,
            ], 200); 
        } catch (\Throwable $th) {
            // dd($th);
            return response()->json([
                "msg" => "Something explode",
                "reason" => $th->getMessage(),
                "req" => $request
            ], 500);
        }

    }

    public function deleteImage($image_id, Request $request){
        $enviroment = $_ENV;
        $passwordToCompare = $enviroment['MYUNSPLASH_PASSWORD'];

        $imageToDelete = Image::where('id', $image_id)->first(); 
        if(!$imageToDelete){
            return response()->json([
                "msg" => "Error",
                "reason" => "Image $image_id doesn't exist!.",
            ], 500);
        }

        $passwordIsIncorrect = strcmp($request->password , $passwordToCompare ) != 0;
        if( $passwordIsIncorrect ){
            return response()->json([
                "msg" => "Error",
                "reason" => "Password incorrect, try again!",
            ], 500);
        }
        
        Cloudinary::admin()->deleteAssets([$imageToDelete->public_id]); // Delete from Cloudinary
        $imageToDelete->delete(); //Delete from DB

        return response()->json([
            "msg" => "Image $image_id deleted succesfully.",
        ], 200);
    }
}
