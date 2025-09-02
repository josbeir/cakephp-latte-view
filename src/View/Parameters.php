<?php
declare(strict_types=1);

namespace LatteView\View;

use Cake\View\View;

abstract class Parameters
{
    protected ?View $view = null;

    /**
     * Set the view instance.
     */
    public function setView(View $view): self
    {
        $this->view = $view;

        return $this;
    }

    /**
     * Get the view instance.
     */
    protected function getView(): ?View
    {
        return $this->view;
    }
}
