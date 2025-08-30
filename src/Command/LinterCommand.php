<?php
declare(strict_types=1);

namespace LatteView\Command;

use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;
use Cake\Console\ConsoleOptionParser;
use Cake\Core\App;
use Latte\Tools\Linter;

class LinterCommand extends AbstractCommand
{
    /**
     * @inheritDoc
     */
    public static function getDescription(): string
    {
        return 'Lint the latte templates';
    }

    /**
     * @inheritDoc
     */
    protected function buildOptionParser(ConsoleOptionParser $parser): ConsoleOptionParser
    {
        $parser = parent::buildOptionParser($parser);
        $parser->addOption('plugin', [
            'help' => 'Plugin name',
            'short' => 'p',
            'default' => null,
        ]);

        return $parser;
    }

    /**
     * @inheritDoc
     */
    public function execute(Arguments $args, ConsoleIo $io): int
    {
        $engine = $this->getViewClass($args)->getEngine();
        $linter = new Linter($engine);

        $plugin = (string)$args->getOption('plugin');
        $paths = App::path('templates', $plugin ?: null);

        $results = [];
        foreach ($paths as $path) {
            ob_start();
            $results[$path] = $linter->scanDirectory($path);
            $captured_output = ob_get_clean();
            $io->out((string)$captured_output);
        }

        if (in_array(false, $results, true)) {
            return self::CODE_ERROR;
        }

        return self::CODE_SUCCESS;
    }
}
