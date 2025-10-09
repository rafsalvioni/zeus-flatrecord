<?php

namespace Zeus\FlatRecord\Positional;

use Attribute;
use Zeus\FlatRecord\AbstractField;
use Zeus\FlatRecord\Decorator\DecoratorInterface;

#[Attribute(Attribute::TARGET_PROPERTY)]
/**
 * Represents a positional field
 * 
 * A positional has a position to start, a length, a char to fill the length
 * and direction to fill
 * 
 * If field value are grater than length defined, it will be truncated
 * 
 * @author Rafael M. Salvioni
 */
class PositionalField extends AbstractField
{
    public readonly int $stop;
    /**
     * 
     * @param int $offset
     * @param int $length
     * @param string $padChar
     * @param string $padDir
     * @param DecoratorInterface|null $decorator
     * @throws \LogicException
     */
    public function __construct(
        /**
         * Initial Offset
         * 
         * @var int
         */
        public readonly int $offset,
        /**
         * Length
         * 
         * @var int
         */
        public readonly int $length,
        /**
         * Pad char
         * 
         * @var string
         */
        private string $padChar = ' ',
        /**
         * Pad direction
         * 
         * @var string
         */
        private int $padDir  = \STR_PAD_RIGHT,
        /**
         * Decorator
         * 
         * @var DecoratorInterface|null
         */
        ?DecoratorInterface $decorator = null,
    ) {
        if ($offset < 0) {
            throw new \LogicException('Offset should be positive');
        }
        if ($length < 1) {
            throw new \LogicException('Length should be > 0');
        }
        parent::__construct($decorator);
        $this->stop = $this->offset + $this->length - 1;
    }
    
    /**
     * Fill string length using pad char and direction
     * 
     * @param object $obj
     * @return string
     */
    public function getStringValue(object $obj): string
    {
        $str = parent::getStringValue($obj);
        $str = \str_pad($str, $this->length, $this->padChar, $this->padDir);
        switch ($this->padDir) {
            case \STR_PAD_RIGHT:
                $str = \substr($str, 0, $this->length);
                break;
            case \STR_PAD_LEFT:
                $str = \substr($str, -$this->length);
                break;
            default:
                $str = \substr($str, 0, (int)($this->length / 2));
        }
        return $str;
    }
    
    /**
     * Trim pad chars according pad char and direction
     * 
     * @param object $obj
     * @param string $str
     * @return void
     */
    public function setStringValue(object $obj, string $str): void
    {
        $trim = match($this->padDir) {
            \STR_PAD_RIGHT => 'rtrim',
            \STR_PAD_LEFT  => 'ltrim',
            default        => 'trim',
        };
        $str = $trim($str, $this->padChar);
        parent::setStringValue($obj, $str);
    }
}
