<?php

namespace App\Livewire;

use Livewire\Component;
use App\Jobs\RemoveImageBackground;
use Livewire\WithFileUploads;
use Livewire\Attributes\On;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Validate;
use Illuminate\Support\Facades\Cache;

class BackgroundRemover extends Component
{
    use WithFileUploads;
    #[Validate(['image' => 'required|image|mimes:jpeg,png,jpg,gif|max:1024'], message: 'Please provide a valid image file')]
    public $image;
    public $imageUrl;
    public $maskedImageUrl = [];
    public $isProcessing = false;

    public function storeInCache()
    {
        Cache::put('masked_image_url', $this->maskedImageUrl, now()->addMinutes(10));
        Cache::put('image_url', $this->imageUrl, now()->addMinutes(10));
    }

    public function retrieveFromCache()
    {
        $this->maskedImageUrl = Cache::get('masked_image_url', []);
        $this->imageUrl = Cache::get('image_url');
    }

    public function mount()
    {
        $this->retrieveFromCache();
    }

    public function updated($property)
    {
        if ($property === "image") {
            $this->validate();
            $this->isProcessing = true;
            $name = pathinfo($this->image->getClientOriginalName(), PATHINFO_FILENAME);
            $extension = $this->image->getClientOriginalExtension();
            $uploadName = $name . '_' . time() . '.' . $extension;

            $uploadPath = $this->image->storeAs('images', $uploadName, 'public');

            $this->imageUrl = Storage::url($uploadPath);
            $this->storeInCache();
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

        $this->imageUrl = Storage::url($uploadPath);
        $this->storeInCache();
        $imageId = base64url_encode($uploadPath);

        RemoveImageBackground::dispatch($imageId);
    }

    #[On('echo:bg-removed,ImageBackgroundRemoved')]
    public function updatedMaskedImageUrl($payload)
    {
        $path = base64url_decode($payload['masked_id']);
        //$this->maskedImageUrl = Storage::url($path);
        array_push($this->maskedImageUrl, Storage::url($path));
        $this->storeInCache();
        $this->isProcessing = false;
    }

    public function render()
    {
        return view('livewire.background-remover');
    }
}
