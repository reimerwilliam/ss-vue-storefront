<?php

namespace Controller;

use SilverStripe\Control\Controller;

class DirectoryController extends Controller
{
    private static $allowed_actions = [
        'countries'
    ];

    public function countries()
    {
        return json_encode([]);
    }
}
