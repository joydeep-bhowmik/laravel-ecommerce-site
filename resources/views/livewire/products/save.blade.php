<?php

use App\Traits\Toast;
use App\Models\Product;
use App\Models\Category;
use App\Models\Attribute;
use Livewire\Volt\Component;
use Livewire\Attributes\Rule;
use Livewire\WithFileUploads;
use Mary\Traits\WithMediaSync;
use Illuminate\Support\Collection;

new class extends Component {
    use WithFileUploads, WithMediaSync, Toast;

    public string|null $id = null;

    #[Rule(['photos.*' => 'image|max:1024'])]
    public array $photos = [];
    public string $name;
    public string $slug;
    public string $seo_title = '';
    public string $seo_description = '';
    public string $base_price;
    #[Rule('required')]
    public Collection $images;
    public string $description;
    public array $tags = [];
    public array $sizes = [];
    public array $productAttributes = []; // Renamed from `$attributes` to avoid conflict
    public Product $product;
    public string|null $category = null;
    public bool $is_cod_available = false;

    function mount(string|null $id = null)
    {
        $this->images = collect([]); // Initialize images as a collection
        $this->id = $id;

        if (!$this->id) {
            return;
        }

        $product = Product::find($this->id);

        if (!$product) {
            abort(404);
        }

        $this->product = $product;

        // Assign all attributes from the product
        $this->name = $product->name;
        $this->slug = $product->slug;
        $this->base_price = $product->base_price;
        $this->description = $product->description ?? '';
        $this->tags = $product->tags ?? [];
        $this->category = $product->category_id;
        $this->is_cod_available = $product->is_cod_available ? true : false;

        // Ensure proper handling of images
        $this->images = $product->images ?? collect([]);

        // Handle `sizes` as an array
        $this->sizes = is_string($product->sizes) ? json_decode($product->sizes, true) ?? [] : $product->sizes;

        // Handle SEO fields
        $this->seo_title = $product->seo_title ?? '';
        $this->seo_description = $product->seo_description ?? '';

        // Handle product attributes
        $this->productAttributes = is_string($product->attributes) ? json_decode($product->attributes, true) ?? [] : $product->attributes;

        // Ensure sizes is always an array
        if (!is_array($this->sizes)) {
            $this->sizes = [];
        }
    }

    function addSize()
    {
        $this->sizes[] = [
            'name' => '',
            'price' => '',
            'quantity' => '',
            'length' => '',
            'width' => '',
            'height' => '',
            'weight' => '',
        ];
    }

    function removeSize($index)
    {
        unset($this->sizes[$index]);
        $this->sizes = array_values($this->sizes);
    }

    function addAttribute()
    {
        $this->productAttributes[] = ['type' => '', 'value' => ''];
    }

    function removeAttribute($index)
    {
        unset($this->productAttributes[$index]);
        $this->productAttributes = array_values($this->productAttributes);
    }

    function save()
    {
        // Validate input data
        $validatedData = $this->validate([
            'name' => 'required|string',
            'slug' => 'required|string|unique:products,slug,' . $this->id,
            'photos.*' => 'image|max:1024',
            'tags' => 'array',
            'sizes' => 'array',
            'base_price' => 'string',
            'sizes.*.name' => 'required|string',
            'sizes.*.price' => 'required|numeric|min:0',
            'sizes.*.quantity' => 'required|integer|min:0',
            'sizes.*.length' => 'nullable|numeric|min:0',
            'sizes.*.width' => 'nullable|numeric|min:0',
            'sizes.*.height' => 'nullable|numeric|min:0',
            'sizes.*.weight' => 'required|numeric|min:0',
            'productAttributes' => 'array',
            'productAttributes.*.type' => 'required|string',
            'productAttributes.*.value' => 'required|string',
            'description' => 'nullable|string',
            'seo_title' => 'nullable|string|max:255',
            'seo_description' => 'nullable|string',
            'category' => 'nullable',
            'is_cod_available' => 'nullable',
        ]);

        // Assign validated data
        $validatedData['sizes'] = $this->sizes; // Already an array, no need to encode
        $validatedData['attributes'] = $this->productAttributes; // Already an array, no need to encode
        $validatedData['category_id'] = $this->category;
        $validatedData['images'] = $this->images->toArray(); // Convert collection to array

        // Save or update the product
        $product = Product::updateOrCreate(['id' => $this->id], $validatedData);

        // Sync media files
        $this->syncMedia($product, library: 'images', files: 'photos', model_field: 'images', disk: 'public');

        // Show success message
        $this->success('Saved');

        // Redirect if creating a new product
        if (!$this->id) {
            $this->redirect(route('admin.products.edit', ['id' => $product->id]), navigate: true);
        }
    }

    function delete()
    {
        return Product::find($this->id)?->delete() && $this->success('Deleted');
    }

    function with()
    {
        $categories = [];
        $noParentCategories = Category::where('parent_category_id', null)
            ->get()
            ->map(function ($category) {
                return ['id' => $category->id, 'name' => $category->name];
            })
            ->toArray();

        $categories['No Parent'] = $noParentCategories;

        $rootCategories = Category::where('parent_category_id', null)->get();
        foreach ($rootCategories as $category) {
            $children = $category
                ->children()
                ->get()
                ->map(function ($child) {
                    return ['id' => $child->id, 'name' => $child->name];
                })
                ->toArray();

            if (!empty($children)) {
                $categories[$category->name] = $children;
            }
        }

        $_attributes = Attribute::all()->mapWithKeys(
            fn($item) => [
                $item->name => array_merge($item->toArray(), ['id' => $item->name]),
            ],
        );

        return compact('_attributes', 'categories');
    }
};
?>

<div>
    <x-header :title="$id ? 'Products / Edit' : 'Products / Create'">
        <x-slot:actions>
            <x-button spinner class="btn-primary" wire:click='save'>Save</x-button>
            @if ($id)
                <x-button spinner class="btn-error" wire:click='delete' wire:confirm='Are you sure?'>Delete</x-button>
            @endif
        </x-slot:actions>
    </x-header>

    <x-form enctype="multipart/form-data">
        <div class="grid grid-cols-1 lg:grid-cols-[auto_300px] gap-5">
            <div class="space-y-5">
                <x-card title="Basic">
                    <div class="space-y-3">
                        <x-input label="Name" wire:model='name' />

                        <x-input label="Slug" wire:model="slug" prefix="products/"
                            @input="$el.value = $el.value .trim() .toLowerCase() .replace(/[^a-z0-9\s-]/g, '') .replace(/\s+/g, '-') .replace(/-+/g, '-') .replace(/^-+|-+$/g, ''); $el.dispatchEvent(new Event('input')) " />

                        <x-input label="Base Price" wire:model="base_price" prefix="IND" money />
                        <x-markdown label="Description" wire:model="description" />

                        <x-tags label="Tags" wire:model="tags" icon="o-tag" />

                        <x-checkbox label="Is cod available" wire:model='is_cod_available' />
                    </div>
                </x-card>

                <x-card :title="'Sizes' . (count($sizes) ? ' (' . count($sizes) . ')' : '')">
                    <div class="space-y-3 divide-y overflow-y-auto max-h-80">
                        @forelse($sizes as $index => $size)
                            <div class="grid grid-cols-[auto_100px] gap-3 py-5" wire:key='{{ $index }}'>
                                <div class="grid grid-cols-3 gap-3 w-full">
                                    <x-input label="Size" wire:model="sizes.{{ $index }}.name"
                                        placeholder="e.g, M" />
                                    <x-input label="Price" wire:model="sizes.{{ $index }}.price" prefix="INR"
                                        money />
                                    <x-input label="Quantity" wire:model="sizes.{{ $index }}.quantity"
                                        type="number" min="0" placeholder="e.g, 2" />
                                    <x-input label="Length (cm)" wire:model="sizes.{{ $index }}.length"
                                        type="number" min="0" step="0.01" placeholder="e.g, 20cm" />
                                    <x-input label="Width (cm)" wire:model="sizes.{{ $index }}.width"
                                        type="number" min="0" step="0.01" placeholder="e.g, 20cm" />
                                    <x-input label="Height (cm)" wire:model="sizes.{{ $index }}.height"
                                        type="number" min="0" step="0.01" placeholder="e.g, 20cm" />
                                    <x-input label="Weight (kg)" wire:model="sizes.{{ $index }}.weight"
                                        type="number" min="0" step="0.01" placeholder="e.g, 2kg" />
                                </div>
                                <div>
                                    <x-button spinner class="btn-error mt-7" icon="o-trash"
                                        wire:click="removeSize({{ $index }})" />
                                </div>
                            </div>
                        @empty
                            <center>No sizes provided</center>
                        @endforelse
                    </div>
                    <x-slot:menu>
                        <x-button spinner icon="o-plus" wire:click="addSize" />
                    </x-slot:menu>
                </x-card>

                <x-card title="SEO (optional)">
                    <div class="space-y-3">
                        <x-input label="Title" wire:model="seo_title" maxlength="255" />
                        <x-textarea label="Description" wire:model="seo_description" />
                    </div>
                </x-card>
            </div>

            <div class="space-y-5">
                <x-card title="Images" wire:key='imagesssaass'>
                    <x-image-library wire:model="photos" wire:library="images" :preview="$images" hint="Max 1024Kb" />
                </x-card>

                @if ($_attributes && count($_attributes))
                    <x-card title="Attributes">
                        <div class="space-y-3">
                            @forelse($productAttributes as $index => $attribute)
                                <div class="grid grid-cols-[auto_40px] gap-3" wire:key='{{ $index }}'>
                                    <div class="grid grid-cols-2 gap-3 w-full">
                                        <x-select label="Type"
                                            wire:model="productAttributes.{{ $index }}.type" :options="$_attributes"
                                            placeholder="Select " placeholder-value="0" />
                                        <x-input label="Value"
                                            wire:model="productAttributes.{{ $index }}.value" />
                                    </div>
                                    <div>
                                        <x-button spinner class="btn-error mt-7" icon="o-trash"
                                            wire:click="removeAttribute({{ $index }})" />
                                    </div>
                                </div>
                            @empty
                                <center>No attributes provided</center>
                            @endforelse
                        </div>
                        <x-slot:menu>
                            <x-button spinner icon="o-plus" wire:click="addAttribute" />
                        </x-slot:menu>
                    </x-card>
                @endif

                @if ($categories && count($categories))
                    <x-card title="Category">
                        <x-select-group placeholder="Select " placeholder-value="0" :options="$categories"
                            wire:model='category' />
                    </x-card>
                @endif


            </div>
        </div>
    </x-form>
</div>
