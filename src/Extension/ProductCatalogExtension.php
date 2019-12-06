<?php

namespace SSVueStorefront\Extension;

use SilverStripe\Core\Extension;
use SSVueStorefront\Model\Attribute;
use SSVueStorefront\Model\AttributeSet;

class ProductCatalogExtension extends Extension
{
    private static $managed_models = [
        AttributeSet::class,
        Attribute::class
    ];
}
