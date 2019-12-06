<?php

namespace SSVueStorefront\Model;

use SilverShop\Page\Product;
use SilverStripe\Forms\ListboxField;
use SilverStripe\ORM\DataObject;

class AttributeSet extends DataObject
{
    private static $db = [
        'Title' => 'Varchar(255)'
    ];

    private static $many_many = [
        'Attributes' => Attribute::class,
        'Products' => Product::class
    ];

    public function getCMSFields()
    {
        $fields = parent::getCMSFields();

        $fields->removeByName('Products');
        $fields->removeByName('Attributes');
        $fields->addFieldsToTab('Root.Main', [
            ListboxField::create(
                'Attributes',
                'Attributes',
                Attribute::get()->map('ID', 'Title')->toArray()
            )
        ]);
        return $fields;
    }
}
