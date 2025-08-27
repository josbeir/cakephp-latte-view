<?php
declare(strict_types=1);

namespace LatteView\View;

use Cake\Core\Configure;
use Cake\View\View;
use Latte\Engine;
use Latte\Essential\TranslatorExtension;
use Latte\Runtime\Template;
use Latte\Sandbox\SecurityPolicy;
use LatteView\Latte\Extension\CakeExtension;
use LatteView\Latte\Extension\CakeTranslator;
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
     * `fallbackBlock` - The name of the fallback block to use when auto layout is disabled.
     *   Defaults to 'content'.
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
        'fallbackBlock' => 'content',
        'cachePath' => CACHE . 'latte_view' . DS,
        'sandbox' => false,
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
            $translator = new CakeTranslator();

            $this->engine = new Engine();
            $this->engine
                ->setAutoRefresh($this->getAutoRefresh())
                ->addProvider('coreParentFinder', $this->layoutLookup(...))
                ->setLoader(new FileLoader())
                ->addExtension(new CakeExtension($this))
                ->addExtension(new TranslatorExtension($translator->translate(...)));

            if ($this->getConfig('cache')) {
                $this->engine->setTempDirectory($this->getConfig('cachePath'));
            }

            if ($this->getConfig('sandbox')) {
                $this->engine->setPolicy($this->getSandboxPolicy());
                $this->engine->setSandboxMode();
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
        $block = $this->getConfig('fallbackBlock');
        if ($this->isAutoLayoutEnabled()) {
            $this->disableAutoLayout();
            $block = null;
        }

        return $this->getEngine()->renderToString(
            $templateFile,
            $dataForView,
            $block,
        );
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
