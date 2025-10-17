<?php

namespace Zeus\FlatRecord\FixedLength;

use Attribute;
use Zeus\FlatRecord\Decorator\NumberDecorator;

/**
 * A fixed-length field configuration specific float numbers
 * 
 * @author Rafael M. Salvioni
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
readonly class FixedLengthFieldFloat extends FixedLengthFieldNumber
{
    /**
     * 
     * @param int $offset Initial offset
     * @param int $length Length
     * @param bool $trunc Truncate?
     * @param int $precision
     */
    public function __construct(
        int $offset, int $length, bool $trunc = false, mixed $empty = 0,
        int $precision = 2
    ) {
        parent::__construct(
            $offset, $length, $trunc, $empty,
            new NumberDecorator($precision)
        );
    }
}
