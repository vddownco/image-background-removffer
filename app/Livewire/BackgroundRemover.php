<?php

namespace App\Livewire;

use Livewire\Component;
use App\Jobs\RemoveImageBackground;
use Livewire\WithFileUploads;

class BackgroundRemover extends Component
{
    use WithFileUploads;
    public $image;
    public $maskedImageUrl;

    public function updated($property)
    {
        if ($property === "image") {
            //TODO: validation
            $name = pathinfo($this->image->getClientOriginalName(), PATHINFO_FILENAME);
            $extension = $this->image->getClientOriginalExtension();
            $uploadName = $name . '_' . time() . '.' . $extension;

            $uploadPath = $this->image->storeAs('images', $uploadName, 'public');

            $imageId = base64url_encode($uploadPath);

            RemoveImageBackground::dispatch($imageId);
        }
    }

    public function render()
    {
        return view('livewire.background-remover');
    }
}
