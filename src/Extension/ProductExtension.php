<?php

namespace Extension;

use SSVueStorefront\API\Elasticsearch;
use SilverStripe\Core\Extension;

class ProductExtension extends Extension
{
    public function onAfterWrite()
    {
        $elasticsearch = new Elasticsearch();
        $elasticsearch->migrate();
    }
}
