<?php
declare(strict_types=1);

namespace LatteView\Test\TestCase\View;

use Cake\TestSuite\TestCase;
use Cake\View\View;
use LatteView\View\Parameters;

class ParametersTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }

    public function testSetView(): void
    {
        $view = new View();
        $parameters = new class extends Parameters {
        };

        $result = $parameters->setView($view);

        $this->assertInstanceOf(Parameters::class, $result);
        $this->assertSame($parameters, $result);
    }

    public function testGetView(): void
    {
        $view = new View();
        $parameters = new class extends Parameters {
            public function getViewPublic(): ?View
            {
                return $this->getView();
            }
        };

        $this->assertNull($parameters->getViewPublic());

        $parameters->setView($view);
        $this->assertSame($view, $parameters->getViewPublic());
    }

    public function testFluentInterface(): void
    {
        $view1 = new View();
        $view2 = new View();
        $parameters = new class extends Parameters {
        };

        $result = $parameters->setView($view1)->setView($view2);

        $this->assertSame($parameters, $result);
    }

    public function testConcreteImplementation(): void
    {
        $view = new View();
        $parameters = new class ('test', 123) extends Parameters {
            public function __construct(
                public readonly string $name,
                public readonly int $value,
            ) {
            }

            public function getData(): array
            {
                return [
                    'name' => $this->name,
                    'value' => $this->value,
                    'view' => $this->getView(),
                ];
            }
        };

        $parameters->setView($view);

        $data = $parameters->getData();

        $this->assertSame('test', $data['name']);
        $this->assertSame(123, $data['value']);
        $this->assertSame($view, $data['view']);
    }
}
