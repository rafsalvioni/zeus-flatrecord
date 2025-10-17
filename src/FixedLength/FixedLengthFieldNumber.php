<?php

namespace Zeus\FlatRecord\FixedLength;

use Attribute;
use Zeus\FlatRecord\Decorator\DecoratorInterface;

/**
 * A fixed-length field configuration specific to number
 * 
 * Normally, when a number is represented in a fixe-length field,
 * it uses '0' to pad char and left pad direction. This class encapsulate this
 * 
 * @author Rafael M. Salvioni
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
readonly class FixedLengthFieldNumber extends FixedLengthField
{
    /**
     * 
     * @param int $offset Initial offset
     * @param int $length Length
     * @param bool $trunc Truncate?
     * @param DecoratorInterface|null $decorator
     */
    public function __construct(
        int $offset, int $length, bool $trunc = false, mixed $empty = 0,
        ?DecoratorInterface $decorator = null
    ) {
        parent::__construct($offset, $length, $trunc, '0', PadType::LEFT, $empty, $decorator);
    }
}
