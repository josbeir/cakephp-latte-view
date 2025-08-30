<?php
declare(strict_types=1);

namespace LatteView\TestApp;

use Cake\Http\BaseApplication;
use Cake\Http\MiddlewareQueue;
use Cake\Routing\RouteBuilder;
use LatteView\LatteViewPlugin;
use LatteView\TestPlugin\TestPluginPlugin;

class Application extends BaseApplication
{
    public function middleware(MiddlewareQueue $middlewareQueue): MiddlewareQueue
    {
        return $middlewareQueue;
    }

    public function bootstrap(): void
    {
        //parent::bootstrap();
        $this->addPlugin(LatteViewPlugin::class);
        $this->addPlugin(TestPluginPlugin::class);
    }

    public function routes(RouteBuilder $routes): void
    {
        $routes->connect('/display', ['controller' => 'Pages', 'action' => 'display']);
    }
}
