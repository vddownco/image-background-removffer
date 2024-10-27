<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Storage;
use Codewithkyrian\Transformers\Models\Auto\AutoModel;
use Codewithkyrian\Transformers\Processors\AutoProcessor;
use Codewithkyrian\Transformers\Utils\Image;

class RemoveImageBackground implements ShouldQueue
{
    use Queueable;

    protected string $modelName = 'briaai/RMBG-1.4';

    /**
     * Create a new job instance.
     */
    public function __construct(public string $id)
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $path = base64url_decode($this->id);

        $url = Storage::disk('public')->path($path);

        $model = AutoModel::fromPretrained($this->modelName);
        $processor = AutoProcessor::fromPretrained($this->modelName);

        $image = Image::read($url);

        ['pixel_values' => $pixelValues] = $processor($image);

        ['output' => $output] = $model(['input' => $pixelValues]);

        $mask = Image::fromTensor($output[0]->multiply(255))
            ->resize($image->width(), $image->height());

        $maskedName = pathinfo($path, PATHINFO_FILENAME) . '_masked';
        $maskedPath = "images/$maskedName.png";

        $maskedImage = $image->applyMask($mask);
        $maskedImage->save(Storage::disk('public')->path($maskedPath));
    }
}
