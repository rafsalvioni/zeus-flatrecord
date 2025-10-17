<?php

namespace Zeus\FlatRecord\FixedLength;

use Attribute;
use LengthException;
use Zeus\FlatRecord\AbstractFieldConfig;
use Zeus\FlatRecord\Decorator\DecoratorInterface;

/**
 * Represents a field-length field configuration
 * 
 * A fixed-length field has a position to start, a length, a char to fill the length
 * and direction to fill
 * 
 * If field value are greater than length defined, it will be truncated (using fill direction) or
 * throws a exception
 * 
 * @author Rafael M. Salvioni
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
readonly class FixedLengthField extends AbstractFieldConfig
{
    /**
     * Stop offset
     * 
     * @var int
     */
    public int $stop;
   
    /**
     * 
     * @param int $offset Initial Offset
     * @param int $length Field length
     * @param bool $trunc Truncate when exceeded? If not, throws exception
     * @param string $padChar Char to complete field length
     * @param PadType $padType Direction to pad
     * @param mixed $empty Value to empty strings when parse
     * @param DecoratorInterface|null $decorator
     * @throws FieldLenghtException If length < 1
     */
    public function __construct(
        /**
         * Initial Offset
         * 
         * @var int
         */
        int $offset,
        /**
         * Length
         * 
         * @var int
         */
        public int $length,
        /**
         * Truncate when exceeded? If not, throws exception
         * 
         * @var bool
         */
        public bool $trunc  = false,
        /**
         * Pad char
         * 
         * @var string
         */
        public string $padChar = ' ',
        /**
         * Pad type
         * 
         * @var PadType
         */
        public PadType $padType = PadType::RIGHT,
        /**
         * Value to be used when field value is empty
         * 
         * @var mixed
         */
        mixed $empty = null,
        /**
         * Decorator
         * 
         * @var DecoratorInterface|null
         */
        ?DecoratorInterface $decorator = null,
    ) {
        if ($length < 1) {
            throw new FieldLenghtException('Length should be > 0');
        }
        parent::__construct($offset, $empty, $decorator);
        $this->stop = $this->index + $this->length - 1;
    }
}
