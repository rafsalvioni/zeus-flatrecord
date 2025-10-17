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
     * 
     * @param string $string
     * @param string $type
     * @return mixed
     */
    public function fromString(string $string): mixed
    {
        return match(true) {
            !!\preg_match('/^(true|false)$/i', $string) => \strtolower($string) == 'true',
            empty($string) => null,
            default => $string
        };
    }

    /**
     * Just cast object to string and replace LF CR chars to spaces
     * 
     * @param mixed $value
     * @return string
     */
    public function toString(mixed $value): string
    {
        if (\is_bool($value)) {
            return $value ? 'true' : 'false';
        }
        $string = (string)$value;
        return \preg_replace('/[\r\n]/', ' ', $string);
    }
}
