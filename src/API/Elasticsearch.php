<?php

namespace SSVueStorefront\API;

use Elasticsearch\ClientBuilder;
use Page;
use SilverShop\Page\Product;
use SilverShop\Page\ProductCategory;
use SilverStripe\Versioned\Versioned;
use SSVueStorefront\Model\Attribute;

// TODO: Use connection variables from config file
class Elasticsearch
{
    private $client;
    private $index = 'vue_storefront_catalog';

    function __construct()
    {
        $this->client = ClientBuilder::create()->build();
    }

    public function syncCategories()
    {
        $categories = Versioned::get_by_stage(ProductCategory::class, 'Live');

        foreach($categories as $key => $category) {
            $item = [
                'index' => $this->index,
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
            $attributeValues = $product->AttributeValues();
            $extraAttributes = [];
            foreach($attributeValues as $value) {
                $code = strtolower(Attribute::get()->byID($value->AttributeTypeID)->AttributeCode);
                $extraAttributes[$code] = $value->Value;
            }
            $item = [
                'index' => $this->index,
                'id' => $product->ID,
                'type' => 'product',
                'body' => array_merge([
                    'id' => $product->ID,
                    'name' => $product->Title,
                    'sku' => $product->InternalItemID,
                    // TODO: image host
                    'image' => $product->Image() ? 'http://silvershop.local/' . $product->Image()->Link() : null,
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
                ], $extraAttributes)
            ];

            $this->client->index($item);
        }

    }

    public function syncAttributes()
    {
        $attributes = Attribute::get();
        foreach($attributes as $attribute) {
            $item = [
                'index' => $this->index,
                'id' => $attribute->ID,
                'type' => 'attribute',
                'body' => [
                    'attribute_code' => strtolower($attribute->AttributeCode),
                    'default_frontend_label' => $attribute->Title,
                    'frontend_input' => 'text', // TODO: Allow for other types
                    'is_visible' => true,
                    'is_required' => $attribute->IsRequired,
                    'is_wysiwyg_enabled' => true,
                    'is_html_allowed_on_front' => true,
                    'used_for_sort' => true,
                    'is_filterable' => true,
                    'is_filterable_in_search' => true,
                    'is_used_in_grid' => true,
                    'is_visible_in_grid' => true,
                    'is_filterable_in_grid' => false,
                    'position' => 0,
                    'apply_to' => [],
                    'is_searchable' => 1,
                    'is_visible_in_advanced_search' => 1,
                    'is_comparable' => '0',
                    'attribute_id' => $attribute->ID,
                    'is_user_defined' => true,
                    'backend_type' => 'text', // TODO: Allow for other types
                    'frontend_class' => '',
                    'is_visible_on_front' => '1'
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
                'index' => $this->index,
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

    public function migrateCatalog()
    {
        $this->syncCategories();
        $this->syncProducts();
        $this->syncAttributes();
    }

    public function migrateCMS()
    {
        $this->syncCMSPages();
    }
}



/*
    attribute_code: 'name',
    default_frontend_label: 'Product Name',
    frontend_input: 'text',
    is_wysiwyg_enabled: false,
    is_visible: true,
    is_html_allowed_on_front: false,
    used_for_sort_by: true,
    is_filterable: false,
    is_filterable_in_search: false,
    is_used_in_grid: false,
    is_visible_in_grid: false,
    is_filterable_in_grid: false,
    position: 0,
    apply_to: [],
    is_searchable: '1',
    is_visible_in_advanced_search: '1',
    is_comparable: '0',
    is_used_for_promo_rules: '0',
    is_visible_on_front: '0',
    used_in_product_listing: '1',
    attribute_id: 73,
    entity_type_id: '4',
    is_required: true,
    options: [],
    is_user_defined: false,
    frontend_labels: [],
    backend_type: 'varchar',
    is_unique: '0',
    frontend_class: 'validate-length maximum-length-255',
    validation_rules: []
 */
