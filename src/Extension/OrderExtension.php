<?php

namespace Extension;

use SilverStripe\Core\Extension;

class OrderExtension extends Extension
{
    public function viewableProducts()
    {
        $items = $this->owner->Items();
        $products = [];
        foreach($items as $item) {
            $product = $item->Product();
            $products[] = [
                'item_id' => $product->ID,
                'sku' => $product->ID,
                'qty' => $item->Quantity,
                'name' => $product->Title,
                'price' => $product->BasePrice,
                'product_type' => 'simple'
            ];
        }

        return $products;
    }
}
