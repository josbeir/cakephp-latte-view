<?php
declare(strict_types=1);

namespace LatteView\Latte\Nodes;

use Cake\Error\Debugger;
use Latte\Compiler\Nodes\Php\ExpressionNode;
use Latte\Compiler\PrintContext;
use Latte\Essential\Nodes\DumpNode as LatteDumpNode;

/**
 * {dump [$var]}
 */
class DumpNode extends LatteDumpNode
{
    public ?ExpressionNode $expression = null;

    /**
     * @inheritDoc
     */
    public function print(PrintContext $context): string
    {
        return $this->expression instanceof ExpressionNode
            ? $context->format(
                Debugger::class . '::printVar(%node);',
                $this->expression,
            )
            : $context->format(
                Debugger::class . '::printVar(get_defined_vars());',
            );
    }
}
