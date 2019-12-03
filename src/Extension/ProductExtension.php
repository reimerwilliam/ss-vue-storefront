<?php

namespace Extension;

use Elasticsearch;
use SilverStripe\Core\Extension;

class ProductExtension extends Extension
{
    public function onAfterWrite()
    {
        Elasticsearch::migrate();
    }
}
