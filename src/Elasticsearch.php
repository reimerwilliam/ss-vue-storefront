<?php

use Elasticsearch\ClientBuilder;
use SilverShop\Page\Product;
use SilverShop\Page\ProductCategory;
use SilverStripe\Versioned\Versioned;

// TODO: Use connection variables from config file
class Elasticsearch
{
    public static function syncCategories()
    {
        $client = ClientBuilder::create()->build();
        $categories = Versioned::get_by_stage(ProductCategory::class, 'Live');

        foreach($categories as $key => $category) {
            $item = [
                'index' => 'vue_storefront_catalog',
                'id' => $category->ID,
                'type' => 'category',
                'body' => [
                    'id' => $category->ID,
                    'parent_id' => $category->ParentID ? $category->ParentID : 0,
                    'name' => $category->Title,
                    'url_key' => $category->URLSegment,
                    'slug' => $category->URLSegment,
                    'url_path' => $category->Link(),
                    'is_active' => true, // Check this somehow?
                    'position' => $key + 1,
                    'level' => 2, // All products will be in the main menu for now
                    'product_count' => 10 // ?
                ]
            ];

            $client->index($item);
        }
    }

    public static function syncProducts()
    {
        $client = ClientBuilder::create()->build();
        $products = Versioned::get_by_stage(Product::class, 'Live');
        foreach($products as $product) {
            $category = ProductCategory::get()->byID($product->ParentID);
            $item = [
                'index' => 'vue_storefront_catalog',
                'id' => $product->ID,
                'type' => 'product',
                'body' => [
                    'id' => $product->ID,
                    'name' => $product->Title,
                    'sku' => $product->ID,
                    'url_key' => $product->URLSegment,
                    'url_path' => preg_replace('/\\?.*/', '', $product->Link()),
                    'type_id' => 'simple',
                    'price' => $product->BasePrice,
                    'final_price' => $product->BasePrice,
                    'price_incl_tax' => $product->BasePrice,
                    'regular_price' => $product->BasePrice,
                    'priceInclTax' => $product->BasePrice,
                    'status' => 1,
                    'visibility' => 4,
                    'category_ids' => [
                        $product->ParentID
                    ],
                    'category' => [
                        [
                            'category_id' => $category->ID,
                            'name' => $category->Title,
                            'path' => $category->Link()
                        ]
                    ],
                    // TODO: fixme
                    'stock' => [[
                        'is_in_stock' => true,
                        'qty' => 10000
                    ]]
                ]
            ];

            $client->index($item);
        }

    }

    public static function migrate()
    {
        self::syncCategories();
        self::syncProducts();
    }
}
