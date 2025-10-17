<?php

namespace Zeus\FlatRecord\Decorator;

/**
 * Implements a boolean decorator
 * 
 * For fields that represents boolean values with other ways
 * 
 * @author Rafael M. Salvioni
 */
class BooleanDecorator implements DecoratorInterface
{
    /**
     * 
     * @param string $strTrue
     * @param string $strFalse
     * @throws DecoratorException If strings are equals
     */
    public function __construct(
        /**
         * Precision
         * 
         * @var int
         */
        private string $strTrue,
        /**
         * Precision
         * 
         * @var int
         */
        private string $strFalse
    ) {
        if ($strTrue == $strFalse) {
            throw new DecoratorException('True and False strings are equal');
        }
    }

    /**
     * 
     * @param string $string
     * @return bool
     */
    public function fromString(string $string): mixed
    {
        return match($string) {
            $this->strTrue  => true,
            $this->strFalse => false,
            default         => null
        };
    }

    /**
     * 
     * @param bool $value
     * @return string
     */
    public function toString(mixed $value): string
    {
        return !!$value ? $this->strTrue : $this->strFalse;
    }
}
