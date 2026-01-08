<?php
namespace App\Models;

use Parsedown;
use App\Models\Category;
use App\Traits\HasCarts;
use Illuminate\Support\Arr;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Product extends Model
{
    use HasCarts, HasFactory;

    protected $fillable = [
        'slug',
        'name',
        'images',
        'description',
        'similiar_products',
        'sizes',
        'tags',
        'category_id',
        'base_price',
        'attributes',
        'is_cod_available',
        'seo_title',
        'seo_description',
    ];

    protected $casts = [
        'images' => 'collection',
        'sizes' => 'array',
        'similiar_products' => 'array',
        'tags' => 'array',
        'attributes' => 'array',
    ];

    protected $appends = ['firstImageUrl'];

    public function getFirstImageUrlAttribute(): string|null
    {
        return Arr::first($this->images)['url'] ?? null;
    }

    public function getImageAttribute(): string|null
    {
        return $this->getFirstImageUrlAttribute();
    }
    public function getTagsAttribute(): array
    {
        return json_decode($this->attributes['tags']);
    }

    public function getIsCodAvailableAttribute(): bool
    {
        return $this->attributes['is_cod_available'] ? true : false;
    }

    public function getHtmlDescription(): string
    {
        $Parsedown = new Parsedown();

        return $Parsedown->text($this->attributes['description']);
    }

    public function getSummary(int $limit = 50): string
    {
        $text = strip_tags($this->getHtmlDescription()); // Remove HTML tags
        return strlen($text) > $limit ? substr($text, 0, $limit) . '...' : $text;
    }


    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

}
