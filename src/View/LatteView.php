<?php
declare(strict_types=1);

namespace LatteView\View;

use Cake\Core\Configure;
use Cake\I18n\I18n;
use Cake\View\View;
use Latte\Engine;
use Latte\Runtime\Template;
use Latte\Sandbox\SecurityPolicy;
use LatteView\Latte\Extension\CakeExtension;

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
            $this->engine = new Engine();
            $this->engine
                ->setAutoRefresh($this->getAutoRefresh())
                ->setLocale(I18n::getLocale())
                ->addProvider('coreParentFinder', $this->layoutLookup(...))
                ->addExtension(new CakeExtension());

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
    protected function getAutoRefresh(): bool
    {
        $auto_refresh = $this->getConfig('autoRefresh');
        if ($auto_refresh === null) {
            $auto_refresh = Configure::read('debug');
        }

        return $auto_refresh;
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
            $this->prepareData($dataForView),
            $block,
        );
    }

    /**
     * Prepare data for rendering.
     *
     * @param array $data The data to be passed to the template.
     * @return array The prepared data.
     */
    protected function prepareData(array $data): array
    {
        $data['View'] = $this;

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
