<?php declare(strict_types=1);

namespace Rector\Rector\Contrib\Nette\Forms;

use PhpParser\Node;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Identifier;
use Rector\Node\Attribute;
use Rector\Node\NodeFactory;
use Rector\NodeAnalyzer\MethodCallAnalyzer;
use Rector\Rector\AbstractRector;

/**
 * Covers https://forum.nette.org/cs/26672-missing-setrequired-true-false-on-field-abc-in-form
 */
final class FormSetRequiredRector extends AbstractRector
{
    /**
     * @var string
     */
    public const FORM_CLASS = 'Nette\Application\UI\Form';

    /**
     * @var MethodCallAnalyzer
     */
    private $methodCallAnalyzer;

    /**
     * @var NodeFactory
     */
    private $nodeFactory;

    public function __construct(MethodCallAnalyzer $methodCallAnalyzer, NodeFactory $nodeFactory)
    {
        $this->methodCallAnalyzer = $methodCallAnalyzer;
        $this->nodeFactory = $nodeFactory;
    }

    public function isCandidate(Node $node): bool
    {
        if (! $this->methodCallAnalyzer->isTypesAndMethods(
            $node,
            ['Nette\Forms\Controls\TextInput'],
            ['addCondition']
        )
        ) {
            return false;
        }

        /** @var MethodCall $node */
        if (count($node->args) !== 1) {
            return false;
        }

        $arg = $node->args[0];
        if (! $arg->value instanceof ClassConstFetch) {
            return false;
        }

        $classConstFetchNode = $arg->value;

        $argTypes = $classConstFetchNode->class->getAttribute(Attribute::TYPES);
        if (! in_array(self::FORM_CLASS, $argTypes, true)) {
            return false;
        }

        /** @var Identifier $identifierNode */
        $identifierNode = $classConstFetchNode->name;

        return $identifierNode->toString() === 'FILLED';
    }

    /**
     * @param MethodCall $methodCallNode
     */
    public function refactor(Node $methodCallNode): ?Node
    {
        $methodCallNode->name = new Identifier('setRequired');
        $methodCallNode->args = $this->nodeFactory->createArgs([
            false,
        ]);

        return $methodCallNode;
    }
}