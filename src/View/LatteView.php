<?php
declare(strict_types=1);

namespace LatteView\View;

use Cake\Core\Configure;
use Cake\I18n\I18n;
use Cake\View\View;
use Latte\Engine;
use Latte\Runtime\Template;
use LatteView\Latte\Extension\CakeExtension;

/**
 * Class LatteView
 */
class LatteView extends View
{
    protected ?Engine $engine = null;

    /**
     * @inheritDoc
     */
    protected string $_ext = '.latte';

    /**
     * Enables caching.
     */
    protected bool $cache = true;

    /**
     * Disables caching.
     */
    protected function disableCache(): void
    {
        $this->cache = false;
    }

    /**
     * Get the Latte engine instance.
     */
    public function getEngine(): Engine
    {
        if (!$this->engine instanceof Engine) {
            $this->engine = new Engine();

            if ($this->cache) {
                $this->engine->setTempDirectory(CACHE . 'latte_view' . DS);
            }

            $this->engine->setAutoRefresh($this->getDebug());
            $this->engine->setLocale(I18n::getLocale());

            $this->engine->addProvider('coreParentFinder', $this->layoutLookup(...));
            $this->engine->addExtension(new CakeExtension());
        }

        return $this->engine;
    }

    /**
     * @inheritDoc
     */
    protected function _evaluate(string $template, array $data): string
    {
        // We need to let Latte handle auto layout.
        // @see self::layoutLookup()
        $this->disableAutoLayout();

        return $this->getEngine()->renderToString($template, $this->prepareData($data));
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
     * Get the debug mode status.
     */
    protected function getDebug(): bool
    {
        return Configure::read('debug');
    }
}
