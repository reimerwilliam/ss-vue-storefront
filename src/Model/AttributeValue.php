<?php

namespace SSVueStorefront\Model;

use SilverShop\Page\Product;
use SilverStripe\Forms\DropdownField;
use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\Map;

class AttributeValue extends DataObject
{
    private static $db = [
        'Value' => 'Text' // TODO: Allow for other data types?
    ];

    private static $has_one = [
        'AttributeType' => Attribute::class,
        'Product' => Product::class
    ];

    private static $summary_fields = [
        'AttributeType.Title' => 'Attribute',
        'Value'
    ];

    public function getCMSFields()
    {
        $fields = parent::getCMSFields();

        $product = $this->Product();
        $attrs = $product->Attributes();
        $map = new Map($attrs, 'ID', 'Title');
        $attrsArr = $map->toArray();
        $fields->addFieldsToTab('Root.Main', [
            DropdownField::create(
                'AttributeTypeID',
                'Attribute type',
                $attrsArr
            )
        ]);

        return $fields;
    }
}
