<?php declare(strict_types=1);

namespace Rector\NodeAnalyzer;

use PhpParser\Node;
use PhpParser\Node\Expr\PropertyFetch;
use PHPStan\Analyser\Scope;
use PHPStan\Broker\Broker;
use PHPStan\Type\ObjectType;
use Rector\NodeTypeResolver\Node\Attribute;
use Rector\NodeTypeResolver\NodeTypeResolver;

/**
 * Read-only utils for PropertyFetch Node:
 * "$this->property"
 */
final class PropertyFetchAnalyzer
{
    /**
     * @var NodeTypeResolver
     */
    private $nodeTypeResolver;

    /**
     * @var Broker
     */
    private $broker;

    public function __construct(NodeTypeResolver $nodeTypeResolver, Broker $broker)
    {
        $this->nodeTypeResolver = $nodeTypeResolver;
        $this->broker = $broker;
    }

    public function isMagicOnType(Node $node, string $type): bool
    {
        if (! $node instanceof PropertyFetch) {
            return false;
        }

        $varNodeTypes = $this->nodeTypeResolver->resolve($node->var);
        if (! in_array($type, $varNodeTypes, true)) {
            return false;
        }

        return ! $this->hasPublicProperty($node, (string) $node->name);
    }

    private function hasPublicProperty(PropertyFetch $node, string $propertyName): bool
    {
        /** @var Scope $nodeScope */
        $nodeScope = $node->getAttribute(Attribute::SCOPE);

        $propertyFetchType = $nodeScope->getType($node->var);
        if ($propertyFetchType instanceof ObjectType) {
            $propertyFetchType = $propertyFetchType->getClassName();
        }

        if (! $this->broker->hasClass($propertyFetchType)) {
            return false;
        }

        $classReflection = $this->broker->getClass($propertyFetchType);
        if (! $classReflection->hasProperty($propertyName)) {
            return false;
        }

        $propertyReflection = $classReflection->getProperty($propertyName, $nodeScope);

        return $propertyReflection->isPublic();
    }
}
