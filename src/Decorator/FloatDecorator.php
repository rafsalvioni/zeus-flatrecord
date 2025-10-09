<?php

namespace Zeus\FlatRecord\Decorator;

/**
 * Implements a float decorator
 * 
 * Sometimes, floats are represented without decimals, like a integer.
 * 
 * Flat fields define what the precision
 *
 * @author Rafael M. Salvioni
 */
class FloatDecorator implements DecoratorInterface
{
    /**
     * 
     * @param int $precision
     */
    public function __construct(
        /**
         * Precision
         * 
         * @var int
         */
        private int $precision = 2
    ) {
        
    }

    /**
     * 
     * @param string $string
     * @param string $target
     * @return float
     */
    public function fromString(string $string, string $target): mixed
    {
        $num = (int)$string;
        return ($num / \pow(10, $this->precision));
    }

    /**
     * 
     * @param float $value
     * @return string
     */
    public function toString(mixed $value): string
    {
        $num = $value * \pow(10, $this->precision);
        return (string)$num;
    }
}
