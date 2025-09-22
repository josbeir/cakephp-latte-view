<?php
declare(strict_types=1);

namespace LatteView\Test\TestCase\Exception;

use Cake\TestSuite\TestCase;
use Exception;
use LatteView\Exception\TemplateNotFoundException;
use RuntimeException;

class TemplateNotFoundExceptionTest extends TestCase
{
    public function testExceptionInheritance(): void
    {
        $exception = new TemplateNotFoundException();
        $this->assertInstanceOf(RuntimeException::class, $exception);
    }

    public function testExceptionWithMessage(): void
    {
        $message = 'Template file not found: /path/to/template.latte';
        $exception = new TemplateNotFoundException($message);

        $this->assertSame($message, $exception->getMessage());
    }

    public function testExceptionWithCode(): void
    {
        $code = 404;
        $exception = new TemplateNotFoundException('Template not found', $code);

        $this->assertSame($code, $exception->getCode());
    }

    public function testExceptionWithPrevious(): void
    {
        $previous = new Exception('Previous exception');
        $exception = new TemplateNotFoundException('Template not found', 0, $previous);

        $this->assertSame($previous, $exception->getPrevious());
    }
}
