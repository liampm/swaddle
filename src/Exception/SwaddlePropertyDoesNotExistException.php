<?php

namespace liampm\Swaddle\Exception;

use liampm\Swaddle\Swaddle;

/**
 * Is thrown when a property does not exist in a Swaddle.
 */
class SwaddlePropertyDoesNotExistException extends \OutOfBoundsException implements SwaddleException
{

    /**
     * @param string  $propertyName  The name of the property that does not exist.
     * @param Swaddle $swaddle       The Swaddle that was being asked to provide the property.
     */
    public function __construct($propertyName, Swaddle $swaddle)
    {
        $message = sprintf(
            'There is no property with the name "%s" in this Swaddle. Available properties are: "%s".',
            $propertyName,
            implode('", "', array_keys(get_object_vars($swaddle->unwrap())))
        );

        parent::__construct($message);
    }
}