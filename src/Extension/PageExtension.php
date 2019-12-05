<?php

namespace SSVueStorefront\Extension;

use SilverStripe\Forms\CheckboxField;
use SilverStripe\ORM\DataExtension;
use SSVueStorefront\API\Elasticsearch;
use SilverStripe\Forms\FieldList;

class PageExtension extends DataExtension
{
    private static $db = [
        'SyncToSF' => 'Boolean'
    ];

    public function updateCMSFields(FieldList $fields)
    {
        $fields->addFieldsToTab('Root.Storefront', [
            CheckboxField::create('SyncToSF', 'Sync CMS page to Vue Storefront')
        ]);

        return $fields;
    }

    public function onAfterWrite()
    {
        $elasticsearch = new Elasticsearch();
        $elasticsearch->migrateCMS();
    }
}
