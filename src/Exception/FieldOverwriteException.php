<?php

namespace Zeus\FlatRecord\Exception;

use DomainException;

/**
 * @author Rafael M. Salvioni
 */
class FieldOverwriteException extends DomainException implements
    RecordExceptionInterface
{
}
