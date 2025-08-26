<?php
declare(strict_types=1);

namespace LatteView\TestApp;

use Cake\Http\BaseApplication;
use Cake\Http\MiddlewareQueue;
use LatteView\TestPlugin\TestPluginPlugin;

class Application extends BaseApplication
{
    public function middleware(MiddlewareQueue $middlewareQueue): MiddlewareQueue
    {
        return $middlewareQueue;
    }

    public function bootstrap(): void
    {
        parent::bootstrap();
        $this->addPlugin(TestPluginPlugin::class);
    }
}
