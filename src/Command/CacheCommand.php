<?php
declare(strict_types=1);

namespace LatteView\Command;

use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;

class CacheCommand extends AbstractCommand
{
    /**
     * @inheritDoc
     */
    public static function getDescription(): string
    {
        return 'Clear the latte cache directory';
    }

    /**
     * @inheritDoc
     */
    public function execute(Arguments $args, ConsoleIo $io): int
    {
        $io->out('Clearing Latte cache directory...');
        $cacheDir = $this->getViewClass($args)->getConfig('cachePath');

        if (is_dir($cacheDir)) {
            $files = glob($cacheDir . '/*') ?: [];
            foreach ($files as $file) {
                if (is_file($file)) {
                    unlink($file);
                }
            }

            $io->success('Cache directory cleared successfully.');
        } else {
            $io->warning('Cache directory does not exist.');
        }

        return self::CODE_SUCCESS;
    }
}
