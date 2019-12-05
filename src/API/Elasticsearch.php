<?php

namespace SSVueStorefront\API;

use Elasticsearch\ClientBuilder;
use Page;
use SilverShop\Page\Product;
use SilverShop\Page\ProductCategory;
use SilverStripe\Versioned\Versioned;

// TODO: Use connection variables from config file
class Elasticsearch
{
    private $client;

    function __construct()
    {
        $this->client = ClientBuilder::create()->build();
    }

    public function syncCategories()
    {
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

            $this->client->index($item);
        }
    }

    public function syncProducts()
    {
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
                    'sku' => $product->InternalItemID,
                    'url_key' => $product->URLSegment,
                    'url_path' => preg_replace('/\\?.*/', '', $product->Link()),
                    'type_id' => 'simple',
                    'price' => $product->BasePrice,
                    'final_price' => $product->BasePrice,
                    'price_incl_tax' => $product->BasePrice,
                    'regular_price' => $product->BasePrice,
                    'priceInclTax' => $product->BasePrice,
                    'status' => $product->AllowPurchase ? 1 : 2,
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

            $this->client->index($item);
        }

    }

    public function syncCMSPages()
    {
        $pages = Versioned::get_by_stage(Page::class, 'Live')->filter(['SyncToSF' => true]);
        foreach($pages as $page) {
            $item = [
                'index' => 'vue_storefront_catalog',
                'id' => $page->ID,
                'type' => 'cms_page',
                'body' => [
                    'page_id' => $page->ID,
                    'title' => $page->Title,
                    'identifier' => $page->URLSegment,
                    'content' => $page->Content,
                    'content_header' => $page->Title,
                    'meta_description' => $page->MetaDescription,
                    'meta_keywords' => 'something',
                    'store_id' => $page->ID
                ]
            ];

            $this->client->index($item);
        }
    }

    public function migrate()
    {
        $this->syncCategories();
        $this->syncProducts();
    }

    public function migrateCMS()
    {
        $this->syncCMSPages();
    }
}
