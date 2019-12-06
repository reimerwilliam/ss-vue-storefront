<?php

namespace SSVueStorefront\Extension;

use SSVueStorefront\API\Elasticsearch;
use SilverStripe\Core\Extension;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\GridField\GridField;
use SilverStripe\Forms\GridField\GridFieldConfig_RelationEditor;
use SilverStripe\Forms\ListboxField;
use SSVueStorefront\Model\Attribute;
use SSVueStorefront\Model\AttributeSet;
use SSVueStorefront\Model\AttributeValue;

class ProductExtension extends Extension
{
    private static $many_many = [
        'Attributes' => Attribute::class,
        'AttributeSets' => AttributeSet::class
    ];

    private static $has_many = [
        'AttributeValues' => AttributeValue::class
    ];

    public function updateCMSFields(FieldList $fields)
    {
        $fields->addFieldsToTab('Root.ExtraAttributes', [
            ListboxField::create(
                'AttributeSets',
                'Attribute sets',
                AttributeSet::get()->map('ID', 'Title')->toArray()
            ),
            ListboxField::create(
                'Attributes',
                'Attributes',
                Attribute::get()->map('ID', 'Title')->toArray()
            ),
            GridField::create(
                'AttributeValues',
                'Values',
                $this->owner->AttributeValues(),
                GridFieldConfig_RelationEditor::create()
            )
        ]);

        return $fields;
    }

    public function onAfterWrite()
    {
        $elasticsearch = new Elasticsearch();
        $elasticsearch->migrateCatalog();
    }
}
