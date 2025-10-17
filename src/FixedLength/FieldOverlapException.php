<?php

namespace Zeus\FlatRecord\FixedLength;

use DomainException;
use Zeus\FlatRecord\Exception\FieldExceptionInterface;

/**
 * Exception fired when a field definition overlaps another
 *
 * @author Rafael M. Salvioni
 */
class FieldOverlapException extends DomainException implements
    FieldExceptionInterface
{
}
