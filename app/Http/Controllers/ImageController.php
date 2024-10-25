<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ImageController extends Controller
{
    public function viewPage()
    {
        return view('image-view');
    }

    public function removeBackground(Request $request)
    {
        //TODO: remove image background using huggingface api
        return null;
    }
}
