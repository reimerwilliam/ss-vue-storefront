<?php

namespace Controller;

use SilverStripe\GraphQL\Controller;
use SilverStripe\Control\HTTPRequest;
use SilverShop\Cart\ShoppingCart;
use SilverShop\Model\Order;
use SilverShop\ORM\Filters\MatchObjectFilter;
use SilverShop\Page\Product;
use SilverStripe\Core\Config\Config;
use SilverStripe\ORM\DataObject;
use SilverStripe\Security\Member;
use SilverStripe\Security\Security;
use SilverStripe\Versioned\Versioned;

class GuestCartController extends Controller
{
    public function index(HTTPRequest $request)
    {
        $orderID = $request->param('OrderID');
        $action = $request->param('Action');
        $method = $request->httpMethod();

        $response = $this->getResponse();

        if(!$orderID && !$action) {
            $order = ShoppingCart::curr();
            if(!$order) {
                $order = Order::create();
                if (Member::config()->login_joins_cart && ($member = Security::getCurrentUser())) {
                    $order->MemberID = $member->ID;
                }

                $order->write();
                $order->extend('onStartOrder');
            }
            $response->setBody($order->ID);
            return $response;
        }

        if(!$action) {
            $response->setStatusCode(400);
            $response->setBody('No action provided');
            return $response;
        }

        switch($action) {
            case 'items':
                if($method == 'GET') {
                    $order = Order::get()->byID($orderID);
                    return json_encode($order->viewableProducts());
                } else if($method == 'POST') {
                    $body = json_decode($request->getBody());
                    $cart = ShoppingCart::singleton();

                    $itemIInternalId = $body->cartItem->sku;
                    $quantity = $body->cartItem->qty;

                    $order = Order::get()->byID($orderID);

                    $buyableclass = Product::class;
                    $buyable = $buyableclass::has_extension(Versioned::class)
                        ? Versioned::get_by_stage($buyableclass, 'Live')->filter(['InternalItemID' => $itemIInternalId])->first()
                        : DataObject::get($buyableclass)->filter(['InternalItemID' => $itemIInternalId])->first();

                    $buyable = $cart->getCorrectBuyable($buyable);
                    $filter = [
                        'OrderID' => $order->ID,
                    ];
                    $itemclass = Config::inst()->get(get_class($buyable), 'order_item');
                    $relationship = Config::inst()->get($itemclass, 'buyable_relationship');
                    $filter[$relationship . 'ID'] = $buyable->ID;
                    $required = ['OrderID', $relationship . 'ID'];
                    if (is_array($itemclass::config()->required_fields)) {
                        $required = array_merge($required, $itemclass::config()->required_fields);
                    }
                    $query = new MatchObjectFilter($itemclass, array_merge([], $filter), $required);
                    $item = $itemclass::get()->where($query->getFilter())->first();

                    if(!$item) {
                        $item = $buyable->createItem($quantity);
                        $item->OrderID = $order->ID;
                        $item->write();
                        $order->Items()->add($item);
                        $item->_brandnew = true;
                    }

                    $item->Quantity = $quantity;

                    $item->write();

                    return json_encode([
                        'item_id' => $buyable->ID,
                        'sku' => $buyable->InternalItemID,
                        'qty' => $item->Quantity,
                        'price' => $buyable->BasePrice,
                        'product_type' => 'simple'
                    ]);
                }
                break;
            case 'estimate-shipping-methods':
                // TODO: Get shipping methods from CMS
                if($method == 'POST') {
                    $response->setBody(json_encode([
                        'carrier_code' => 'postnord',
                        'method_code' => 'postnord',
                        'carrier_title' => 'Postnord',
                        'method_title' => 'Postnord',
                        'amount' => 10,
                        'base_amount' => 10,
                        'available' => true,
                        'error_message' => '',
                        'price_excl_tax' => 10,
                        'price_incl_tax' => 10
                    ]));
                    return $response;
                }
                break;
            case 'payment-methods':
                // TODO: Get shipping methods from CMS/config
                if($method == 'GET') {
                    $response->setBody(json_encode([
                        [
                            'code' => 'cashondelivery',
                            'title' => 'Cash On Delivery'
                        ],
                        [
                            'code' => 'klarna',
                            'title' => 'Klarna'
                        ]
                    ]));
                    return $response;
                }
                break;
            case 'totals':
                if($method == 'GET') {
                    $order = Order::get()->byID($orderID);
                    $order->calculate();
                    $response->setBody(json_encode($order->viewableTotals()));
                }
                break;
            case 'shipping-information':
                // Maybe store some shipping information one the order here?
                if($method == 'POST') {
                    $body = json_decode($request->getBody());
                    $order = Order::get()->byID($orderID);
                    $order->calculate();
                    $response->setBody(json_encode($order->viewableTotals()));
                }
                break;
            case 'billing-address':
                if($method == 'POST') {
                    $data = json_decode($request->getBody())->address;
                    $order = Order::get()->byID($orderID);
                    $order->setBillingAddress($data);
                    $response->setBody($orderID);
                }
                break;
            case 'order':
                if($method == 'PUT') {
                    // Handle payment
                    $body = json_decode($request->getBody());
                    $response->setBody(json_encode($orderID));
                }
                break;
            default:
                $response->setStatusCode(404);
                $response->setBody('No handler found for action: "' . $action . '"');
                break;
        }

        return $response;
    }
}
