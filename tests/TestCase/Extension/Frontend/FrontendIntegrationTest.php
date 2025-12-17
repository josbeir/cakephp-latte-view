<?php
declare(strict_types=1);

namespace LatteView\Test\TestCase\Extension\Frontend;

use Cake\ORM\Entity;
use Cake\TestSuite\TestCase;
use LatteView\Extension\Frontend\FrontendExtension;
use LatteView\TestApp\View\FrontendExtensionAppView;

class FrontendIntegrationTest extends TestCase
{
    protected ?FrontendExtensionAppView $view = null;

    protected function setUp(): void
    {
        parent::setUp();
        $this->view = new FrontendExtensionAppView();
        // Frontend extension is automatically loaded in FrontendExtensionAppView
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->view = null;
    }

    public function testAlpineIntegration(): void
    {
        $user = new Entity([
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ]);

        $this->view->set('user', $user);
        $this->view->set('articles', [
            ['title' => 'Article 1'],
            ['title' => 'Article 2'],
        ]);

        $output = $this->view->render('frontend/alpine_test', false);

        // Test that Alpine x-data attributes are present
        $this->assertStringContainsString('x-data=', $output);

        // Test that data is properly escaped
        $this->assertStringContainsString('John Doe', $output);
        $this->assertStringContainsString('john@example.com', $output);

        // Test that JSON is properly formatted
        $this->assertStringNotContainsString('<script>', $output);
    }

    public function testStimulusIntegration(): void
    {
        $user = new Entity([
            'name' => 'Jane Smith',
            'email' => 'jane@example.com',
        ]);

        $validationRules = ['required' => ['name', 'email']];

        $this->view->set('user', $user);
        $this->view->set('validationRules', $validationRules);
        $this->view->set('items', ['item1', 'item2', 'item3']);

        $output = $this->view->render('frontend/stimulus_test', false);

        // Test that Stimulus data-*-value attributes are present
        $this->assertStringContainsString('data-user-profile-value=', $output);
        $this->assertStringContainsString('data-form-validator-value=', $output);
        $this->assertStringContainsString('data-list-manager-value=', $output);

        // Test that data is properly serialized
        $this->assertStringContainsString('Jane Smith', $output);
        $this->assertStringContainsString('required', $output);
    }

    public function testHtmxIntegration(): void
    {
        $params = ['id' => 123, 'action' => 'update'];
        $formData = ['title' => 'Test Title', 'content' => 'Test Content'];
        $currentPage = 2;
        $filters = ['status' => 'active', 'category' => 'news'];

        $this->view->set(['params' => $params, 'formData' => $formData, 'currentPage' => $currentPage, 'filters' => $filters]);

        $output = $this->view->render('frontend/htmx_test', false);

        // Test that HTMX hx-vals attributes are present
        $this->assertStringContainsString('hx-vals=', $output);

        // Test that data is properly serialized
        $this->assertStringContainsString('123', $output);
        $this->assertStringContainsString('update', $output);
        $this->assertStringContainsString('Test Title', $output);
    }

    public function testGenericDataIntegration(): void
    {
        $user = new Entity(['name' => 'Test User']);
        $config = ['theme' => 'dark', 'locale' => 'en'];
        $state = 'active';
        $message = 'Hello World';

        $this->view->set(['user' => $user, 'config' => $config, 'state' => $state, 'message' => $message]);

        $output = $this->view->render('frontend/generic_data_test', false);

        // Test that generic data-json attributes are present
        $this->assertStringContainsString('data-json=', $output);

        // Test that data is properly serialized
        $this->assertStringContainsString('Test User', $output);
        $this->assertStringContainsString('dark', $output);
        $this->assertStringContainsString('Hello World', $output);
    }

    public function testXSSPrevention(): void
    {
        // Set all variables needed by the template
        $user = new Entity(['name' => 'Test User']);
        $config = ['theme' => 'dark', 'locale' => 'en'];
        $state = 'active';
        $message = '<script>alert("xss")</script>';

        $this->view->set(['user' => $user, 'config' => $config, 'state' => $state, 'message' => $message]);

        $output = $this->view->render('frontend/generic_data_test', false);

        // Test that XSS is prevented
        $this->assertStringNotContainsString('<script>alert("xss")</script>', $output);

        // Test that escaping was applied
        $this->assertStringContainsString('\\u003C', $output); // Escaped angle bracket
    }

    public function testCorrectJSONEscaping(): void
    {
        $user = new Entity([
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ]);
        $articles = [
            ['title' => 'Article 1'],
            ['title' => 'Article 2'],
        ];

        $this->view->set('user', $user);
        $this->view->set('articles', $articles);

        $output = $this->view->render('frontend/alpine_test', false);

        // Test that JSON is properly escaped without outer quotes
        // Should produce: x-data="&#123;&quot;name&quot;:&quot;John Doe&quot;...}"
        // NOT: x-data="&quot;&#123;\&quot;name\&quot;:\&quot;John Doe\&quot;...}&quot;"
        $this->assertStringContainsString('x-data="&#123;&quot;', $output);
        $this->assertStringNotContainsString('x-data="&quot;&#123;', $output);

        // Ensure no double quotes wrapping the JSON
        $this->assertStringNotContainsString('x-data="&quot;{', $output);

        // Test specific expected pattern for user data
        $this->assertStringContainsString('x-data="&#123;&quot;name&quot;:&quot;John Doe&quot;', $output);
    }

    public function testCustomFrameworkMapping(): void
    {
        // Create view and manually load extension with custom framework mapping
        $view = new FrontendExtensionAppView();
        $view->getEngine()->addExtension(new FrontendExtension($view, [
            'custom' => 'data-{name}-props',
            'vue' => ':data',
        ]));

        $view->set('data', ['test' => 'value']);

        // This would require a custom template, but we can test the extension setup
        $extension = new FrontendExtension($view, [
            'custom' => 'data-{name}-props',
            'vue' => ':data',
        ]);

        $tags = $extension->getTags();
        $this->assertArrayHasKey('n:data-custom', $tags);
        $this->assertArrayHasKey('n:data-vue', $tags);
    }

    public function testJavaScriptMode(): void
    {
        $params = ['type' => 'xml', 'count' => 5];
        $user = new Entity(['name' => 'John', 'role' => 'admin']);

        $this->view->set('params', $params);
        $this->view->set('user', $user);

        $output = $this->view->render('frontend/javascript_test', false);

        // Test Alpine.js JavaScript mode
        // Should produce: x-data="dropdown(&#123;&quot;type&quot;:&quot;xml&quot;,&quot;count&quot;:5})"
        $this->assertStringContainsString('x-data="dropdown(&#123;&quot;type&quot;:&quot;xml&quot;', $output);
        $this->assertStringContainsString('&quot;count&quot;:5})"', $output);

        // Test Stimulus JavaScript mode
        // Should produce: data-profile-menu-value="initProfile(&#123;&quot;name&quot;:&quot;John&quot;...})"
        $this->assertStringContainsString('data-profile-menu-value="initProfile(&#123;&quot;name&quot;:&quot;John&quot;', $output);
        $this->assertStringContainsString('&quot;role&quot;:&quot;admin&quot;})"', $output);

        // Test HTMX JavaScript mode
        // Should produce: hx-vals="getFormData(&#123;&quot;type&quot;:&quot;xml&quot;...})"
        $this->assertStringContainsString('hx-vals="getFormData(&#123;&quot;type&quot;:&quot;xml&quot;', $output);

        // Test generic JavaScript mode
        // Should produce: data-json="setupWidget(&#123;&quot;name&quot;:&quot;John&quot;...})"
        $this->assertStringContainsString('data-json="setupWidget(&#123;&quot;name&quot;:&quot;John&quot;', $output);

        // Ensure no double escaping or outer quotes
        $this->assertStringNotContainsString('x-data="&quot;dropdown(', $output);
        $this->assertStringNotContainsString('dropdown(...}&quot;"', $output);
    }

    public function testJavaScriptModeFallback(): void
    {
        // Test JavaScript mode with non-function expressions (fallback to JSON serialization)
        // This covers line 173 in DataSerializationNode::compileJavaScriptExpression()

        $data = ['key' => 'value', 'number' => 42];
        $this->view->set('data', $data);

        $output = $this->view->render('frontend/javascript_fallback_test', false);

        // JavaScript mode with non-function expression should fallback to JSON serialization
        // Should produce: x-data="&#123;&quot;key&quot;:&quot;value&quot;,&quot;number&quot;:42}"
        $this->assertStringContainsString('x-data="&#123;&quot;key&quot;:&quot;value&quot;', $output);
        $this->assertStringContainsString('&quot;number&quot;:42}"', $output);
    }

    public function testFormWithAlpineIntegration(): void
    {
        // Test that x-data attributes work correctly with form n:context
        $action = null;
        $isActive = false;
        $items = [1, 2, 3];

        $this->view->set([
            'action' => $action,
            'isActive' => $isActive,
            'items' => $items,
        ]);

        $output = $this->view->render('frontend/form_with_alpine', false);

        // Test that x-data attribute is present
        $this->assertStringContainsString('x-data=', $output);

        // Test that boolean values are properly serialized
        $this->assertStringContainsString('isActive', $output);
        $this->assertStringContainsString('false', $output);

        // Test that array values are properly serialized
        $this->assertStringContainsString('items', $output);
        $this->assertStringContainsString('[1,2,3]', $output);
    }
}
