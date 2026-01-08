<?php
use App\Traits\Toast;
use App\Models\Category;
use Livewire\Volt\Component;
use Livewire\WithFileUploads;
use Illuminate\Validation\Rule;

new class extends Component {
    use Toast, WithFileUploads;

    public string|null $id = null;
    public string $name = '';
    public ?int $parent_category_id = null;
    public $thumbnail;
    public $existingThumbnail;

    function mount(string|null $id = null)
    {
        $this->id = $id;

        if ($this->id) {
            $category = Category::find($this->id);
            if (!$category) {
                abort(404);
            }
            $this->name = $category->name;
            $this->parent_category_id = $category->parent_category_id;
            $this->existingThumbnail = $category->getFirstMediaUrl('thumbnails');
        }
    }

    function save()
    {
        // Validate the input
        $this->validate(
            [
                'name' => 'required|string|max:255',
                'parent_category_id' => [
                    'nullable',
                    Rule::notIn([$this->id]), // Prevent category from being its own parent
                ],
                'thumbnail' => $this->id && Category::find($this->id)?->getFirstMediaUrl('thumbnails') ? 'nullable|image|mimes:png|max:2048' : 'required|image|mimes:png|max:2048',
            ],
            [
                'thumbnail.required' => 'A category thumbnail is required.',
                'thumbnail.image' => 'The thumbnail must be an image.',
                'thumbnail.mimes' => 'Only PNG images are allowed.',
                'thumbnail.max' => 'The thumbnail must be less than 2MB.',
            ],
        );

        $category = $this->id ? Category::find($this->id) : new Category();
        $category->name = $this->name;
        $category->parent_category_id = $this->parent_category_id ?? null;

        if ($this->id !== null && $this->id == $this->parent_category_id) {
            $this->addError('name', 'A category cannot be its own parent.');
            return;
        }

        $category->save();

        // Handle media upload
        if ($this->thumbnail) {
            $category->clearMediaCollection('thumbnails');
            $category->addMedia($this->thumbnail->getRealPath())->toMediaCollection('thumbnails');
        }

        $this->success('Saved', 'Category saved successfully');

        if (!$this->id) {
            $this->redirect(route('admin.categories.edit', ['id' => $category->id]), navigate: true);
        }
    }

    function delete()
    {
        $category = Category::find($this->id);
        if ($category) {
            $category->clearMediaCollection('thumbnails'); // Remove media before deletion
            $category->delete();
            $this->success('Deleted');
        }
        $this->redirect(route('admin.categories.index'), navigate: true);
    }

    function with()
    {
        $categories = [];
        $noParentCategories = Category::where('parent_category_id', null)->get()->map(fn($category) => ['id' => $category->id, 'name' => $category->name])->toArray();

        $categories['No Parent'] = $noParentCategories;

        $rootCategories = Category::where('parent_category_id', null)->get();
        foreach ($rootCategories as $category) {
            $children = $category->children()->get()->map(fn($child) => ['id' => $child->id, 'name' => $child->name])->toArray();

            if (!empty($children)) {
                $categories[$category->name] = $children;
            }
        }

        return compact('categories');
    }
};

?>

<div>
    <x-header :title="$id ? 'Categories / Edit' : 'Categories / Create'">
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
                <x-card title="Category Details">
                    <div class="space-y-3">

                        <x-file wire:model="thumbnail" accept="image/png" crop-after-change>
                            <img src="{{ $id ? Category::find($id)?->getFirstMediaUrl('thumbnails') : '/image-placeholder.png' }}"
                                class="h-40 rounded-lg bg-green-50" />
                        </x-file>


                        <x-input label="Name" wire:model='name' />

                        <x-select-group label="Parent Category" placeholder="Select " placeholder-value="0"
                            :options="$categories" wire:model='parent_category_id' />

                    </div>
                </x-card>
            </div>
        </div>
    </x-form>
</div>
