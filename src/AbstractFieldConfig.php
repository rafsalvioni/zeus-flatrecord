<?php

namespace Zeus\FlatRecord;

use Zeus\FlatRecord\Decorator\DecoratorInterface;
use Zeus\FlatRecord\Decorator\DefaultDecorator;
use Zeus\FlatRecord\Exception\InvalidIndexException;

/**
 * Abstract field configuration
 *
 * @author Rafael M. Salvioni
 */
abstract readonly class AbstractFieldConfig implements FieldConfigInterface
{
    /**
     * Field's decorator
     * 
     * @var DecoratorInterface
     */
    public readonly DecoratorInterface $decorator;
    
    /**
     * 
     * @param int $index Field's index
     * @param mixed $empty Value to be used when field value is empty
     * @param DecoratorInterface|null $decorator If null, uses DefaultDecorator
     * @throws InvalidIndexException If index is negative
     */
    public function __construct(
        /**
         * Field's index
         * 
         * @var int
         */
        public int $index,
        /**
         * Value to be used when field value is empty
         * 
         * @var mixed
         */
        public mixed $empty = null,
        ?DecoratorInterface $decorator = null
    ) {
        if ($index < 0) {
            throw new InvalidIndexException('Index should be positive');
        }
        $this->decorator = ($decorator ?? new DefaultDecorator());
    }

    /**
     * 
     * @return DecoratorInterface
     */
    public function getDecorator(): DecoratorInterface
    {
        return $this->decorator;
    }
}
