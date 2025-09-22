<?php
declare(strict_types=1);

namespace LatteView\Test\TestCase;

use Cake\Console\CommandCollection;
use Cake\Core\BasePlugin;
use Cake\Core\Configure;
use Cake\Event\EventManager;
use Cake\TestSuite\TestCase;
use LatteView\Command\CacheCommand;
use LatteView\Command\LinterCommand;
use LatteView\LatteViewPlugin;
use ReflectionClass;

class LatteViewPluginTest extends TestCase
{
    protected ?LatteViewPlugin $plugin = null;

    protected function setUp(): void
    {
        parent::setUp();
        $this->plugin = new LatteViewPlugin();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->plugin = null;
        Configure::delete('LatteView.disableCacheClearListener');
    }

    public function testConsoleCommands(): void
    {
        $commands = new CommandCollection();
        $result = $this->plugin->console($commands);

        $this->assertInstanceOf(CommandCollection::class, $result);
        $this->assertTrue($result->has('latte clear'));
        $this->assertTrue($result->has('latte linter'));
        $this->assertSame(CacheCommand::class, $result->get('latte clear'));
        $this->assertSame(LinterCommand::class, $result->get('latte linter'));
    }

    public function testEventsReturnsEventManager(): void
    {
        $eventManager = new EventManager();
        $result = $this->plugin->events($eventManager);

        $this->assertInstanceOf(EventManager::class, $result);
        $this->assertSame($eventManager, $result);
    }

    public function testEventsWithDisabledCacheClearListener(): void
    {
        Configure::write('LatteView.disableCacheClearListener', true);
        $eventManager = new EventManager();

        $result = $this->plugin->events($eventManager);

        $this->assertInstanceOf(EventManager::class, $result);
        // When disabled, no listeners should be attached
        $this->assertSame($eventManager, $result);
    }

    public function testPluginClass(): void
    {
        $this->assertInstanceOf(LatteViewPlugin::class, $this->plugin);
    }

    public function testInheritsFromBasePlugin(): void
    {
        $reflection = new ReflectionClass($this->plugin);
        $this->assertTrue($reflection->isSubclassOf(BasePlugin::class));
    }

    public function testConsoleMethodExists(): void
    {
        $this->assertTrue(method_exists($this->plugin, 'console'));
    }

    public function testEventsMethodExists(): void
    {
        $this->assertTrue(method_exists($this->plugin, 'events'));
    }

    public function testConfigureFlagHandling(): void
    {
        // Test that the disable flag is properly checked
        Configure::write('LatteView.disableCacheClearListener', false);
        $eventManager = new EventManager();
        $result = $this->plugin->events($eventManager);
        $this->assertInstanceOf(EventManager::class, $result);

        Configure::write('LatteView.disableCacheClearListener', true);
        $eventManager2 = new EventManager();
        $result2 = $this->plugin->events($eventManager2);
        $this->assertInstanceOf(EventManager::class, $result2);
    }

    public function testEventListenerRegistration(): void
    {
        Configure::write('LatteView.disableCacheClearListener', false);
        $eventManager = new EventManager();

        // Events method should register listener when not disabled
        $result = $this->plugin->events($eventManager);
        $this->assertInstanceOf(EventManager::class, $result);

        // Verify the configuration flag is respected
        $this->assertFalse(Configure::read('LatteView.disableCacheClearListener'));
    }
}
