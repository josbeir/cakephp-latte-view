<?php
declare(strict_types=1);

namespace LatteView\Latte\Extension;

use Cake\I18n\I18n;
use InvalidArgumentException;

class CakeTranslator
{
    /**
     * Constructor.
     *
     * @return void
     */
    public function __construct()
    {
        I18n::setDefaultFormatter('sprintf');
    }

    /**
     * Translate a message.
     */
    public function translate(string $message, mixed ...$args): string
    {
        $domain = 'default';
        $count = null;
        $singular = null;

        if (isset($args['domain'])) {
            $domain = $args['domain'];
            unset($args['domain']);
        }

        if (isset($args['singular'])) {
            $singular = $args['singular'];
            unset($args['singular']);
            $count = $args[0] ?? null;
        }

        $translator = I18n::getTranslator($domain);

        // Handle plural translation - first arg is count when using plural mode
        if ($count !== null && $singular !== null) {
            if (!is_numeric($count)) {
                throw new InvalidArgumentException('Count argument must be numeric when using singular argument');
            }

            $args = ['_count' => $count, '_singular' => $singular] + $args;
        }

        // Handle regular translation
        return $translator->translate($message, $args);
    }
}
