<?php

namespace Zeus\FlatRecord\Decorator;

/**
 * Represents a field decorator
 * 
 * Decorators are used to convert data between objects and theirs strings
 * representations
 * 
 * @author Rafael M. Salvioni
 */
interface DecoratorInterface
{
    /**
     * Converts given value to string
     * 
     * @param mixed $value
     * @return string
     */
    public function toString(mixed $value): string;
    
    /**
     * Converts a string to a arbitrary value
     * 
     * @param string $string
     * @param string $target Type target
     * @return mixed
     */
    public function fromString(string $string, string $target): mixed;
}
