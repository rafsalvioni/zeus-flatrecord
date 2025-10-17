<?php

namespace Zeus\FlatRecord\FixedLength;

use Attribute;
use Zeus\FlatRecord\Decorator\BooleanDecorator;
use Zeus\FlatRecord\Decorator\DecoratorInterface;

/**
 * A fixed-length field configuration specific to boolean values
 * 
 * @author Rafael M. Salvioni
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
readonly class FixedLengthFieldBool extends FixedLengthField
{
    /**
     * Field length will be the max length from $strTrue and $strFalse
     * 
     * @param int $offset Offset
     * @param string $strTrue "True" string
     * @param string $strFalse "False" string
     * @param mixed $empty Value if string is empty
     * @param string $padChar
     * @param PadType $padType
     */
    public function __construct(
        int $offset, string $strTrue = 'T', string $strFalse = 'F',
        mixed $empty = null, string $padChar = ' ',
        PadType $padType = PadType::RIGHT,
    ) {
        $length = \max(\strlen($strTrue), \strlen($strFalse));
        parent::__construct(
            $offset, $length, false, $padChar, $padType, $empty,
            new BooleanDecorator($strTrue, $strFalse)
        );
    }
}
