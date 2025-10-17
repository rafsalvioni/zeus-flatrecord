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
     * @return \DateTime
     */
    public function fromString(string $string): mixed
    {
        if (empty($string)) {
            return '';
        }
        return \DateTime::createFromFormat($this->format, $string);
    }

    /**
     * 
     * @param \DateTime $value
     * @return string
     */
    public function toString(mixed $value): string
    {
        if ($value === null) {
            return '';
        }
        return $value->format($this->format);
    }
}
