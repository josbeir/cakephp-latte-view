<?php
declare(strict_types=1);

namespace LatteView\View;

use Cake\Core\Configure;
use Cake\I18n\I18n;
use Cake\View\View;
use Latte\Engine;
use Latte\Essential\RawPhpExtension;
use Latte\Essential\TranslatorExtension;
use Latte\Runtime\Template;
use Latte\Sandbox\SecurityPolicy;
use LatteView\Latte\Extension\BaseExtension;
use LatteView\Latte\Extension\FilterExtension;
use LatteView\Latte\Extension\Translator;
use LatteView\Latte\Loaders\FileLoader;

/**
 * Class LatteView
 */
class LatteView extends View
{
    protected ?Engine $engine = null;

    protected ?SecurityPolicy $sandboxPolicy = null;

    /**
     * Default configuration settings.
     *
     * Use ViewBuilder::setOption()/setOptions() in your controller to set these options.
     *
     * `cache` - Whether to cache compiled templates. Defaults to true.
     *
     * `autoRefresh` - Whether to check for template updates on each request. Defaults to false.
     *   If not explicitly set and debug is enabled, it will be true.
     *
     * `blocks` - An array of block names to render when auto layout is disabled.
     *   Defaults to ['content'].
     *
     * `cachePath` - The directory path where compiled templates are cached.
     *   Defaults to CACHE . 'latte_view' . DS.
     *
     * `sandbox` - Whether to enable sandbox mode for template security. Defaults to false.
     *
     * @var array<string, mixed>
     */
    protected array $_defaultConfig = [
        'cache' => true,
        'autoRefresh' => null,
        'blocks' => ['content'],
        'cachePath' => CACHE . 'latte_view' . DS,
        'rawphp' => true,
        'sandbox' => false,
        'defaultHelpers' => [
            'Breadcrumbs',
            'Flash',
            'Form',
            'Html',
            'Number',
            'Paginator',
            'Text',
            'Time',
            'Url',
        ],
    ];

    /**
     * @inheritDoc
     */
    protected string $_ext = '.latte';

    /**
     * Get the Latte engine instance.
     */
    public function getEngine(): Engine
    {
        if (!$this->engine instanceof Engine) {
            $translator = new Translator();

            $this->engine = new Engine();
            $this->engine
                ->setAutoRefresh($this->getAutoRefresh())
                ->addProvider('coreParentFinder', $this->layoutLookup(...))
                ->setLoader(new FileLoader())
                ->setLocale(I18n::getLocale())
                ->addExtension(new BaseExtension($this))
                ->addExtension(new TranslatorExtension($translator->translate(...)))
                ->addExtension(new FilterExtension($this, $this->engine->getFilters()))
                ->setSandboxMode($this->getConfig('sandbox', false))
                ->setPolicy($this->getSandboxPolicy());

            if ($this->getConfig('rawphp', true)) {
                $this->engine->addExtension(new RawPhpExtension());
            }

            if ($this->getConfig('cache')) {
                $this->engine->setTempDirectory($this->getConfig('cachePath'));
            }
        }

        return $this->engine;
    }

    /**
     * Get whether auto refresh is enabled.
     *
     * If not explicitly set, it will follow the debug mode.
     */
    public function getAutoRefresh(): bool
    {
        $auto_refresh = $this->getConfig('autoRefresh');
        if ($auto_refresh === null) {
            $auto_refresh = Configure::read('debug');
        }

        return $auto_refresh ?? true;
    }

    /**
     * Renders a template file with the provided data.
     *
     * Note: When auto layout is enabled (default), Latte handles layout resolution automatically.
     * When auto layout is disabled, the template is rendered within the specified fallback block.
     *
     * @param string $templateFile Filename of the template.
     * @param array $dataForView Data to include in rendered view.
     * @return string Rendered output
     */
    protected function _evaluate(string $templateFile, array $dataForView): string
    {
        $dataForView = $this->prepareData($dataForView);

        if ($this->isAutoLayoutEnabled()) {
            $this->disableAutoLayout();

            return $this->getEngine()->renderToString($templateFile, $dataForView);
        }

        $blocks = $this->getConfig('blocks', []);

        $content = '';
        foreach ($blocks as $blockName) {
            $content .= $this->getEngine()->renderToString(
                $templateFile,
                $dataForView,
                $blockName,
            );
        }

        return $content;
    }

    /**
     * Prepare data for the template.
     *
     * If a value in the data array is an instance of `\LatteView\View\Parameter`,
     * it will be instantiated with the its data values as constructor arguments.
     *
     * Examples:
     * 1. Regular data array (returned as-is):
     *    `$data = ['title' => 'Hello', 'content' => 'World'];`
     *    // Returns: `['title' => 'Hello', 'content' => 'World']`
     *
     * 2. Array containing ParameterInterface instance:
     *    `$data = ['user' => new UserParameter('John', 25)];`
     *    // Returns: `UserParameter` instance
     *
     * 3. Array with class name as key and constructor args as value:
     *    `$data = [UserParameter::class => ['John', 25]];`
     *    // Returns: `new UserParameter('John', 25)`
     *
     * @param array $data The data array to prepare.
     * @return array The prepared data array.
     */
    protected function prepareData(array $data): array|Parameters
    {
        foreach ($data as $key => $value) {
            if ($value instanceof Parameters) {
                return $value->setView($this);
            }

            if (
                is_string($key) &&
                is_subclass_of($key, Parameters::class) &&
                is_array($value)
            ) {
                $paramClass = new $key(...array_values($value));

                return $paramClass->setView($this);
            }
        }

        return $data;
    }

    /**
     * Retrieve the default layout.
     *
     * @return string
     */
    protected function layoutLookup(Template $template): ?string
    {
        if (!$template->getReferenceType()) {
            return $this->_getLayoutFileName();
        }

        return null;
    }

    /**
     * Set the security policy for the sandbox.
     */
    public function setSandboxPolicy(SecurityPolicy $policy): self
    {
        $this->sandboxPolicy = $policy;

        return $this;
    }

    /**
     * Get the security policy for the sandbox.
     *
     * Defaults to the safe policy. (see `SecurityPolicy::createSafePolicy()`)
     */
    public function getSandboxPolicy(): SecurityPolicy
    {
        if (!$this->sandboxPolicy instanceof SecurityPolicy) {
            $this->sandboxPolicy = SecurityPolicy::createSafePolicy();
        }

        return $this->sandboxPolicy;
    }
}
