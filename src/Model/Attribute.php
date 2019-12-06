<?php

namespace SSVueStorefront\Model;

use SilverStripe\ORM\DataObject;

class Attribute extends DataObject
{
    private static $db = [
        'Title' => 'Varchar(255)',
        'AttributeCode' => 'Varchar(255)',
        'IsRequired' => 'Boolean'
    ];

    private static $has_many = [
        'Value' => AttributeValue::class
    ];

    private static $belongs_many_many = [
        'AttributeSets' => AttributeSet::class
    ];

    public function getCMSFields()
    {
        $fields = parent::getCMSFields();

        $fields->removeByName('Value');
        $fields->removeByName('AttributeSets');

        return $fields;
    }
}
