<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WebsiteDetails extends Model
{
    use HasUuids;

    protected $guarded = [];

    /**
     * Get the post that owns the website detail.
     */
    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }
}
