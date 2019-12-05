<?php

namespace Extension;

use SilverShop\Model\Address;
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
                'sku' => $product->InternalItemID,
                'qty' => $item->Quantity,
                'name' => $product->Title,
                'price' => $product->BasePrice,
                'product_type' => 'simple'
            ];
        }

        return $products;
    }

    public function viewableTotals()
    {
        return [
            'grand_total' => $this->owner->SubTotal(),
            'base_currency_code' => 'USD',
            'quote_currency_code' => 'USD',
            'items_qty' => $this->owner->Items()->count(),
            'items' => $this->owner->viewableProducts(),
            'total_segments' => [
                [
                    'code' => 'subtotal',
                    'title' => 'Subtotalhejsanhejsan',
                    'value' => $this->owner->SubTotal()
                ],
                [
                    'code' => 'greet',
                    'title' => 'Hejsan',
                    'value' => 'Hallå'
                ],
                [
                    'code' => 'tax',
                    'title' => 'Skatter och skit',
                    'value' => '3 siljoners siljarder'
                ],
                [
                    'code' => 'grand_total',
                    'title' => 'Grand Totalish',
                    'value' => $this->owner->Total(),
                    'area' => 'footer'
                ]
            ]
        ];
    }

    public function setBillingAddress($data)
    {
        $address = Address::create();
        $address->City = $data->city;
        $address->PostalCode = $data->postcode;
        $address->Address = implode(' ', $data->street);
        $address->FirstName = $data->firstname;
        $address->Surname = $data->lastname;
        $address->Phone = $data->telephone;
        $address->Country = $data->countryId;
        $address->State = '-';

        $addressID = $address->write();

        $this->owner->BillingAddressID = $addressID;
        $this->owner->write();
    }
}
