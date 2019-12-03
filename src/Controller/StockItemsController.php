<?php

namespace Controller;

use SilverShop\Page\Product;
use SilverStripe\Control\Controller;
use SilverStripe\Control\HTTPRequest;

class StockItemsController extends Controller
{
    public function index(HTTPRequest $request)
    {
        $productInternalID = $request->params('ItemID');
        $product = Product::get()->filter(['InternalItemID' => $productInternalID])->first();
        if($product) {
            return json_encode([
                'item_id' => $product->ID,
                'product_id' => $product->ID,
                'qty' => 10000,
                // TODO: Actually check if product is in stock
                'is_in_stock' => true
            ]);
        }
        $this->getResponse()->setStatusCode(404);
        $this->getResponse()->setBody('Product does not exist');
        return $this->getResponse();
    }
}
