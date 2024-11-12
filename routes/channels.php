<?php

use App\Models\Post;
use App\Models\User;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('post-generated.{postId}', function (User $user, string $postId) {
    $post = Post::find($postId);
    return $post && $user->id === $post->user_id;
});
