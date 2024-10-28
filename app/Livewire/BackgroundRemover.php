<?php

namespace App\Livewire;

use Livewire\Component;
use App\Jobs\RemoveImageBackground;
use Livewire\WithFileUploads;
use Livewire\Attributes\On;
use Illuminate\Support\Facades\Storage;

class BackgroundRemover extends Component
{
    use WithFileUploads;
    public $image;
    public $maskedImageUrl;
    public $isProcessing = false;

    public function updated($property)
    {
        if ($property === "image") {
            //TODO: validation
            $this->isProcessing = true;
            $name = pathinfo($this->image->getClientOriginalName(), PATHINFO_FILENAME);
            $extension = $this->image->getClientOriginalExtension();
            $uploadName = $name . '_' . time() . '.' . $extension;

            $uploadPath = $this->image->storeAs('images', $uploadName, 'public');

            $imageId = base64url_encode($uploadPath);

            RemoveImageBackground::dispatch($imageId);
        }
    }

    #[On('imageCaptured')]
    public function processWebcamRawImageData($raw_data)
    {
        $this->isProcessing = true;
        $image_binary_data = base64_decode($raw_data);
        $uploadName = uniqid('webcam_', true) . '.png';
        $uploadPath = 'images/' . $uploadName;
        Storage::disk('public')->put($uploadPath, $image_binary_data);

        $imageId = base64url_encode($uploadPath);

        RemoveImageBackground::dispatch($imageId);
    }

    #[On('echo:bg-removed,ImageBackgroundRemoved')]
    public function updateMaskedImageUrl($payload)
    {
        $path = base64url_decode($payload['masked_id']);
        $this->maskedImageUrl = Storage::url($path);
        $this->isProcessing = false;
    }

    public function render()
    {
        return view('livewire.background-remover');
    }
}
