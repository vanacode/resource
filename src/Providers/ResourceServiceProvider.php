<?php

namespace Vanacode\Resource\Providers;

use Vanacode\Resource\RequestHelper;
use Vanacode\Resource\ResourceRoute;
use Vanacode\Support\VnServiceProvider;

class ResourceServiceProvider extends VnServiceProvider
{
    public function register()
    {
        $this->mergeAndPublishConfigBy(__DIR__);
        $this->registerAliases(
            [
                'ResourceRoute' => ResourceRoute::class,
                'RequestHelper' => RequestHelper::class,
            ]
        );
    }
}
