<?php

namespace Zeus\FlatRecord\Decorator;

/**
 * Implements a number decorator
 * 
 * Sometimes, floats are represented without decimals, like a integer.
 * 
 * Flat fields define what the precision. If precision eq 0, treats as integer
 *
 * @author Rafael M. Salvioni
 */
class NumberDecorator implements DecoratorInterface
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
     * @return float
     */
    public function fromString(string $string): mixed
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
        if ($value === null) {
            return '';
        }
        $num = (string)($value * \pow(10, $this->precision));
        return \preg_replace('/\..+$/', '', $num);
    }
}
