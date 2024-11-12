<?php

namespace App\Http\Controllers;

use App\Jobs\ScrapeProductDetails;
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
        $user = auth()->user();
        $post = $user->posts()->create([
            'url' => $validated['url']
        ]);

        //Start the post generating process using job
        ScrapeProductDetails::dispatch($validated['url'], $post->id);

        //Redirect to the result page
        return redirect()->route('post.show', ['post' => $post->id]);
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
        return view('posts.show-post');
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
