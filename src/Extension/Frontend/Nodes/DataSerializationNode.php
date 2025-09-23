<?php
declare(strict_types=1);

namespace LatteView\Extension\Frontend\Nodes;

use Generator;
use Latte\Compiler\Nodes\Php\Expression\FunctionCallNode;
use Latte\Compiler\Nodes\Php\ExpressionNode;
use Latte\Compiler\Nodes\StatementNode;
use Latte\Compiler\PrintContext;
use Latte\Compiler\Tag;
use LatteView\Extension\Frontend\Serializers\UniversalSerializer;

final class DataSerializationNode extends StatementNode
{
    protected ExpressionNode $data;

    protected string $framework;

    protected ?string $name;

    protected array $frameworkMappings;

    protected bool $jsMode = false;

    /**
     * Create data serialization node.
     *
     * @param \Latte\Compiler\Tag $tag The tag instance.
     * @param string $framework The framework name.
     * @param string|null $name The component name.
     * @param array<string, string> $frameworkMappings Framework mappings.
     * @param bool $jsMode Whether to use JavaScript mode (function calls) or JSON mode (data objects).
     */
    public static function create(
        Tag $tag,
        string $framework,
        ?string $name,
        array $frameworkMappings,
        bool $jsMode = false,
    ): ?static {
        $tag->expectArguments();

        $node = new self();
        $node->framework = $framework;
        $node->name = $name;
        $node->frameworkMappings = $frameworkMappings;
        $node->jsMode = $jsMode;
        $node->data = $tag->parser->parseExpression();
        $node->position = $tag->position;

        // Name is already parsed by FrontendExtension for colon syntax

        // For n: attributes, add to HTML element attributes and return null
        if ($tag->isNAttribute()) {
            $node->position = $tag->position;
            array_unshift($tag->htmlElement->attributes->children, $node);

            return null;
        }

        return $node;
    }

    /**
     * Print the node.
     *
     * @param \Latte\Compiler\PrintContext $context The print context.
     */
    public function print(PrintContext $context): string
    {
        $attributeName = $this->getAttributeName();

        $context->beginEscape()->enterHtmlAttribute(null);

        if ($this->jsMode) {
            // JavaScript mode: Use the expression directly with escapeJs for data interpolation
            // This allows function calls like dropdown($params) where $params gets serialized and escaped
            $result = $context->format(
                'echo \' %raw="\'; echo %escape(%raw) %line; echo \'"\';',
                $attributeName,
                $this->compileJavaScriptExpression($context),
                $this->position,
            );
        } else {
            // JSON mode: Serialize data as JSON object (current behavior)
            // Use escapeHtmlAttr directly on raw JSON (no outer quotes needed for Alpine.js)
            $result = $context->format(
                'echo \' %raw="\'; echo %escape('
                . UniversalSerializer::class . '::serialize(%node)) %line; echo \'"\';',
                $attributeName,
                $this->data,
                $this->position,
            );
        }

        $context->restoreEscape();

        return $result;
    }

    /**
     * Get iterator.
     *
     * @return \Generator<\Latte\Compiler\Nodes\Php\ExpressionNode>
     */
    public function &getIterator(): Generator
    {
        yield $this->data;
    }

    /**
     * Get attribute name.
     */
    private function getAttributeName(): string
    {
        if ($this->framework === 'data') {
            return 'data-json';
        }

        $pattern = $this->frameworkMappings[$this->framework] ?? 'data-json';

        if ($this->name && str_contains($pattern, '{name}')) {
            return str_replace('{name}', $this->name, $pattern);
        }

        return $pattern;
    }

    /**
     * Compile JavaScript function call expression.
     *
     * @param \Latte\Compiler\PrintContext $context The print context
     * @return string The compiled JavaScript expression
     */
    private function compileJavaScriptExpression(PrintContext $context): string
    {
        // For JavaScript mode, we treat the attribute value as raw JavaScript code
        // that can include function calls with serialized parameters

        // Since Latte parses expressions, we need to handle the case where the expression
        // is a function call and convert it to JavaScript function call syntax
        $expression = $this->data;

        if ($expression instanceof FunctionCallNode) {
            $functionName = $expression->name->name ?? 'unknown';
            $args = $expression->args;

            if (count($args) === 1) {
                // Single parameter: functionName(serializedParam)
                return $context->format(
                    "'%raw(' . " . UniversalSerializer::class . "::serialize(%node) . ')'",
                    $functionName,
                    $args[0]->value,
                );
            } elseif (count($args) > 1) {
                // Multiple parameters: functionName(param1, param2, ...)
                $paramCode = '';
                foreach ($args as $i => $arg) {
                    if ($i > 0) {
                        $paramCode .= " . ',' . ";
                    }

                     $paramCode .= UniversalSerializer::class . '::serialize('
                         . $context->format('%node', $arg->value) . ')';
                }

                return "'" . $functionName . "(' . " . $paramCode . " . ')'";
            }
        }

        // Fallback: serialize as JSON data
        return $context->format(UniversalSerializer::class . '::serialize(%node)', $this->data);
    }
}
