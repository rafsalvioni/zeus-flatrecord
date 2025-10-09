<?php

namespace Zeus\FlatRecord\Decorator;

/**
 * The default decorator
 *
 * @author Rafael M. Salvioni
 */
class DefaultDecorator implements DecoratorInterface
{
    /**
     * Uses PHP's settype()
     * 
     * @param string $string
     * @param string $type
     * @return mixed
     */
    public function fromString(string $string, string $type): mixed
    {
        \settype($string, $type);
        return $string;
    }

    /**
     * Just cast object to string
     * 
     * @param mixed $value
     * @return string
     */
    public function toString(mixed $value): string
    {
        return (string)$value;
    }
}
