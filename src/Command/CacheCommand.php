<?php
declare(strict_types=1);

namespace LatteView\Command;

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
    public function execute(): int
    {
        $this->io->out('Clearing Latte cache directory...');
        $cacheDir = $this->getViewClass()->getConfig('cachePath');
        if (is_dir($cacheDir)) {
            $files = glob($cacheDir . '/*') ?: [];
            foreach ($files as $file) {
                if (is_file($file)) {
                    unlink($file);
                }
            }

            $this->io->success('Cache directory cleared successfully.');
        } else {
            $this->io->warning('Cache directory does not exist.');
        }

        return self::CODE_SUCCESS;
    }
}
