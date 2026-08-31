<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductCoverImageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->artisan('migrate', [
            '--path' => 'database/migrations/tenant',
        ]);
    }

    public function test_first_image_becomes_primary_automatically(): void
    {
        $product = $this->createProduct();

        $image = $product->images()->create(['image_path' => 'products/a.jpg']);

        $this->assertTrue($image->refresh()->is_primary);
        $this->assertSame($image->id, $product->primaryImage->id);
    }

    public function test_additional_images_are_not_primary(): void
    {
        $product = $this->createProduct();

        $first = $product->images()->create(['image_path' => 'products/a.jpg']);
        $second = $product->images()->create(['image_path' => 'products/b.jpg']);

        $this->assertTrue($first->refresh()->is_primary);
        $this->assertFalse($second->refresh()->is_primary);
    }

    public function test_setting_a_new_primary_unsets_the_previous_one(): void
    {
        $product = $this->createProduct();
        $first = $product->images()->create(['image_path' => 'products/a.jpg']);
        $second = $product->images()->create(['image_path' => 'products/b.jpg']);

        $second->update(['is_primary' => true]);

        $this->assertFalse($first->refresh()->is_primary);
        $this->assertTrue($second->refresh()->is_primary);
        $this->assertSame(1, $product->images()->where('is_primary', true)->count());
    }

    public function test_deleting_primary_promotes_the_oldest_remaining_image(): void
    {
        $product = $this->createProduct();
        $first = $product->images()->create(['image_path' => 'products/a.jpg']);
        $second = $product->images()->create(['image_path' => 'products/b.jpg']);

        $first->delete();

        $this->assertTrue($second->refresh()->is_primary);
    }

    private function createProduct(): Product
    {
        $category = Category::create([
            'name' => 'Tênis',
            'slug' => 'tenis-'.uniqid(),
        ]);

        return Product::create([
            'category_id' => $category->id,
            'title' => 'Tênis Demo',
            'sku' => 'TENIS-'.uniqid(),
            'price' => 199.90,
            'cost_price' => 89.90,
            'stock' => 10,
            'low_stock_threshold' => 5,
            'status' => 'active',
        ]);
    }
}
