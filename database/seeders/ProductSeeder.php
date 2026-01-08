<?php
namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $products = [
            [
                'name'             => 'Elegant Wooden Chair',
                'slug'             => 'elegant-wooden-chair',
                'images'           => json_encode(['https://via.placeholder.com/300x300?text=Wooden+Chair']),
                'sizes'            => json_encode(['Small', 'Medium', 'Large']),
                'base_price'       => 149.99,
                'attributes'       => json_encode(['Material' => 'Teak Wood', 'Color' => 'Brown']),
                'seo_title'        => 'Elegant Wooden Chair for Home & Office',
                'seo_description'  => 'Premium quality wooden chair crafted from teak wood, perfect for any space.',
                'description'      => 'This elegant wooden chair adds charm to any setting, featuring durable teak wood with a polished finish.',
                'category_id'      => 1,
                'tags'             => 'furniture, chair, wood',
                'is_cod_available' => true,
            ],
            [
                'name'             => 'Wireless Bluetooth Headphones',
                'slug'             => 'wireless-bluetooth-headphones',
                'images'           => json_encode(['https://via.placeholder.com/300x300?text=Bluetooth+Headphones']),
                'sizes'            => json_encode(['One Size']),
                'base_price'       => 79.99,
                'attributes'       => json_encode(['Battery Life' => '20 Hours', 'Color' => 'Black']),
                'seo_title'        => 'Wireless Bluetooth Headphones with Noise Cancellation',
                'seo_description'  => 'Experience immersive sound with our noise-canceling Bluetooth headphones, designed for long listening sessions.',
                'description'      => 'High-quality wireless headphones with deep bass, noise cancellation, and a long battery life of up to 20 hours.',
                'category_id'      => 2,
                'tags'             => 'electronics, headphones, bluetooth',
                'is_cod_available' => false,
            ],
            [
                'name'             => 'Classic Leather Wallet',
                'slug'             => 'classic-leather-wallet',
                'images'           => json_encode(['https://via.placeholder.com/300x300?text=Leather+Wallet']),
                'sizes'            => json_encode(['Standard']),
                'base_price'       => 39.99,
                'attributes'       => json_encode(['Material' => 'Genuine Leather', 'Color' => 'Brown']),
                'seo_title'        => 'Classic Leather Wallet for Men',
                'seo_description'  => 'A premium handcrafted leather wallet with multiple compartments for cash and cards.',
                'description'      => 'Our classic leather wallet is made from genuine leather, featuring a sleek design and ample storage for daily essentials.',
                'category_id'      => 3,
                'tags'             => 'accessories, wallet, leather',
                'is_cod_available' => true,
            ],
        ];

        foreach ($products as $product) {
            Product::create($product);
        }
    }
}
