<?php

namespace Zeus\FlatRecord\Decorator;

/**
 * Decorator to convert DateTime objects to string
 *
 * @author Rafael M. Salvioni
 */
class DateTimeDecorator implements DecoratorInterface
{
    /**
     * 
     * @param string $format
     */
    public function __construct(
        /**
         * Date format
         * 
         * @var string
         */
        private string $format
    ) {
        
    }

    /**
     * 
     * @param string $string
     * @param string $target
     * @return \DateTime
     */
    public function fromString(string $string, string $target): mixed
    {
        return \DateTime::createFromFormat($this->format, $string);
    }

    /**
     * 
     * @param \DateTime $value
     * @return string
     */
    public function toString(mixed $value): string
    {
        return $value->format($this->format);
    }
}
