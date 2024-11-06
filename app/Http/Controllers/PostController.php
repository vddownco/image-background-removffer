<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;

class PostController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('posts.create-post');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        //Validate the data
        $validated = $request->validate([
            'url' => 'required'
        ]);

        //Store the url to the DB
        $post = new Post;
        $post->url = $validated['url'];
        $post->save();

        $postId = $post->id;

        //TODO: Start the post generating process using job

        //Redirect to the result page
        return redirect()->route('post.show', ['post' => $postId]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        return view('dashboard');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
