<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Image;
use Illuminate\Support\Facades\Storage;

class ImageController extends Controller
{
    public function create(){
        return view('images.create');
    }

    public function store(Request $req){
        $path = $req->file('image')->store('images', 's3');

        $image = Image::create([
            'filename' => basename($path),
            'url'      => Storage::disk('s3')->url($path)
        ]);

        return $image;
    }

    public function show(Image $img){
        
    }
}
