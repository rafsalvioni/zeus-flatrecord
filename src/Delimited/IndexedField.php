<?php

namespace Zeus\FlatRecord\Delimited;

use Attribute;
use Zeus\FlatRecord\AbstractField;
use Zeus\FlatRecord\Decorator\DecoratorInterface as DecoratorInterface;

#[Attribute(Attribute::TARGET_PROPERTY)]
/**
 * Represents a indexed field
 * 
 * A indexed field are fields ordering by index. Theirs positions are mandatory
 * 
 * @author Rafael M.Salvioni
 */
class IndexedField extends AbstractField
{
    /**
     * 
     * @param int $index
     * @param DecoratorInterface|null $decorator
     * @throws \LogicException
     */
    public function __construct(
        /**
         * Field index
         * 
         * @var int
         */
        public readonly int $index,
        /**
         * Field decorator
         * 
         * @var DecoratorInterface|null
         */
        ?DecoratorInterface $decorator = null,
    ) {
        if ($index < 0) {
            throw new \LogicException('Index should be positive');
        }
        parent::__construct($decorator);
    }
}
