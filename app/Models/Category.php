<?php
namespace App\Models;

use App\Models\Product;
use Spatie\MediaLibrary\HasMedia;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\InteractsWithMedia;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Category extends Model implements HasMedia
{

    use InteractsWithMedia;
    protected $fillable = ['name', 'parent_category_id'];

    protected $appends = ['thumbnailUrl'];

    /**
     * Get the parent category.
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'parent_category_id');
    }

    /**
     * Get the child categories.
     */
    public function children(): HasMany
    {
        return $this->hasMany(Category::class, 'parent_category_id');
    }


    public function getThumbnailUrlAttribute()
    {
        return $this->getFirstMediaUrl('thumbnails');
    }


    public function products()
    {
        return $this->hasMany(Product::class);
    }


}
