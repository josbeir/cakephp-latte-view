<?php
declare(strict_types=1);

namespace LatteView\Latte\Nodes\Form;

use Generator;
use Latte\CompileException;
use Latte\Compiler\Nodes\AreaNode;
use Latte\Compiler\Nodes\AuxiliaryNode;
use Latte\Compiler\Nodes\Php\Expression\ArrayNode;
use Latte\Compiler\Nodes\Php\ExpressionNode;
use Latte\Compiler\Nodes\StatementNode;
use Latte\Compiler\PrintContext;
use Latte\Compiler\Tag;
use LatteView\Latte\Nodes\AttributeParserTrait;

/**
 * <input n:name>, <select n:name>, <textarea n:name>, <label n:name> and <button n:name>
 */
final class PostableNnameNode extends StatementNode
{
    use AttributeParserTrait;

    protected ExpressionNode $link;

    protected AreaNode $content;

    protected ArrayNode $args;

    /**
     * @inheritDoc
     */
    public static function create(Tag $tag): Generator
    {
        $tag->outputMode = Tag::OutputKeepIndentation;
        $tag->expectArguments();

        $node = new self();
        $node->link = $tag->parser->parseUnquotedStringOrExpression(colon: false);

        $tag->parser->stream->tryConsume(',');
        $node->args = $tag->parser->parseArguments();

        [$node->content] = yield;

        $node->init($tag);

        return $node;
    }

    /**
     * @inheritDoc
     */
    public function print(PrintContext $context): string
    {
        return $this->content->print($context);
    }

    /**
     * Decorate the tag.
     */
    private function init(Tag $tag): void
    {
        $el = $tag->htmlElement;
        $elName = strtolower($el->name);

        if (!in_array($elName, ['a', 'button'])) {
            throw new CompileException('n:post is only allowed on <a> and <button>', $tag->position);
        }

        $attributes = $this->getAttributesNode($el);
        $method = 'postLink';
        if ($elName === 'button') {
            $method = 'postButton';
        }

        $print_config = [
            <<<'XX'
                ob_start(); %node; $__c_form_content = ob_get_clean();
                $__c_form_args = array_merge(%node, %node);
                if ('%3.raw' == 'postButton') {
                    $__c_form_args['escapeTitle'] = false;
                } else {
                    $__c_form_args['escape'] = false;
                }
                echo $this->global->cakeView->Form->%raw($__c_form_content, %node, $__c_form_args) %line;
            XX,
            $el->content,
            $this->args,
            $attributes,
            $method,
            $this->link,
            $this->position,
        ];

        $el->captureTagName = true;
        $el->selfClosing = true;
        $el->content = null;
        $el->attributes = null;
        $el->tagNode = new AuxiliaryNode(
            fn(PrintContext $context): string => $context->format(...$print_config),
        );
    }

    /**
     * @inheritDoc
     */
    public function &getIterator(): Generator
    {
        yield $this->link;
        yield $this->content;
    }
}
