<?php

use App\Traits\Toast;
use App\Models\Banner;
use Livewire\Volt\Component;
use Livewire\WithFileUploads;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

new class extends Component {
    use Toast, WithFileUploads;

    public string|null $id = null;
    public string $name = '';
    public array $images = [];
    public array $existingImages = [];

    function mount(string|null $id = null)
    {
        $this->id = $id;

        if ($this->id) {
            $banner = Banner::find($this->id);
            if (!$banner) {
                abort(404);
            }
            $this->name = $banner->name;

            $this->getExistingImage();
        }
    }

    function save()
    {
        $this->validate(
            [
                'name' => 'required|string|alpha_dash|max:255|unique:banners,name,' . $this->id,
                'images.*' => 'nullable|image|max:2048',
            ],
            [
                'images.*.image' => 'Each file must be an image.',
                'images.*.mimes' => 'Only PNG images are allowed.',
                'images.*.max' => 'Each image must be less than 2MB.',
            ],
        );

        $banner = $this->id ? Banner::find($this->id) : new Banner();
        $banner->name = $this->name;
        $banner->save();

        // Handle multiple images upload
        if (!empty($this->images)) {
            foreach ($this->images as $image) {
                $banner->addMedia($image->getRealPath())->toMediaCollection('banners');
            }
        }

        $this->success('Saved', 'Banner saved successfully');

        if (!$this->id) {
            $this->redirect(route('admin.branding.banners.edit', ['id' => $banner->id]), navigate: true);
        }

        $this->getExistingImage();

        $this->resetErrorBag();
    }

    function reorderImages($items)
    {
        $ids = collect($items)->pluck('value')->toArray();

        Media::whereIn('id', $ids)
            ->get()
            ->each(function ($item) use ($ids) {
                $item->update(['order_column' => array_search($item->id, $ids)]);
            });
        $this->success('Reordered', 'Images reordered successfully.');
    }

    function getExistingImage()
    {
        $banner = Banner::find($this->id);
        if (!$banner) {
            return;
        }
        $this->existingImages = $banner
            ->getMedia('banners')
            ->sortBy('order_column') // Order by 'order_column'
            ->map(
                fn($media) => [
                    'id' => $media->id,
                    'url' => $media->getUrl(),
                ],
            )
            ->toArray();
    }

    function deleteImage($imageId)
    {
        $media = Media::find($imageId);
        if ($media) {
            $media->delete();
            $this->getExistingImage();

            $this->success('Deleted', 'Image removed successfully.');
        }
    }

    function delete()
    {
        $banner = Banner::find($this->id);
        $banner && $banner->delete();

        $this->redirect(route('admin.branding.banners.index'), navigate: true);
    }
};

?>


<div>
    <x-header :title="$id ? 'Banners / Edit' : 'Banners / Create'">
        <x-slot:actions>
            <x-button spinner class="btn-primary" wire:click='save'>Save</x-button>
            @if ($id)
                <x-button spinner class="btn-error" wire:click='delete' wire:confirm='Are you sure?'>Delete</x-button>
            @endif
        </x-slot:actions>
    </x-header>

    <x-form>
        <div class="grid grid-cols-1 lg:grid-cols-[auto_300px] gap-5">
            <div class="space-y-5">
                <x-card title="Banner Details">
                    <div class="space-y-3">
                        <x-input label="Name" wire:model='name' />

                        <x-file multiple label="Upload Images" wire:model="images" />

                        @if ($existingImages && count($existingImages))
                            <div class="mt-4">
                                <h3 class="text-lg font-semibold">Uploaded Images</h3>
                                <ul class="sortable space-y-2" wire:sortable="reorderImages">

                                    @forelse($existingImages as $image)
                                        <li wire:sortable.item="{{ $image['id'] }}"
                                            wire:key="image-{{ $image['id'] }}"
                                            class="flex items-center justify-between bg-gray-100 p-2 rounded-lg">
                                            <img src="{{ $image['url'] }}" class="h-16 w-16 object-cover rounded-lg" />
                                            <x-button spinner type="button" wire:confirm='Are you sure?'
                                                wire:click="deleteImage('{{ $image['id'] }}')"
                                                class="btn-error">Remove</x-button>
                                        </li>
                                    @empty
                                        <center>No images</center>
                                    @endforelse
                                </ul>
                            </div>
                        @endif

                    </div>
                </x-card>
            </div>
        </div>
    </x-form>
</div>
