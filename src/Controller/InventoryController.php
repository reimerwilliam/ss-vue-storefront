<?php

namespace Controller;

use SilverShop\Page\Product;
use SilverStripe\Control\Controller;
use SilverStripe\Control\HTTPRequest;

class InventoryController extends Controller
{
    private static $allowed_actions = [
        'is_product_salable'
    ];

    public function is_product_salable(HTTPRequest $request)
    {
        $productInternalID = $request->params('ItemID');
        $product = Product::get()->filter(['InternalItemID' => $productInternalID])->first;
        // TODO: Actually check if product is available
        if($product) {
            return true;
        } else {
            return false;
        }
    }
}
