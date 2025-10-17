<?php

namespace Zeus\FlatRecord\FixedLength;

use LengthException;
use Zeus\FlatRecord\Exception\FieldExceptionInterface;

/**
 * @author Rafael M. Salvioni
 */
class FieldLenghtException extends LengthException implements
    FieldExceptionInterface
{
}
