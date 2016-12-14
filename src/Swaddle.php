<?php

namespace liampm\Swaddle;

use liampm\Swaddle\Exception\SwaddlePropertyDoesNotExistException;

/**
 * Swaddle acts as a wrapper around unknown collections of key-value pairs.
 *
 * It provides some useful helper methods to reduce the amount of checking and defaulting that would normally have to be
 * done for an associative array or stdClass.
 *
 * A Swaddle allows you to:
 * - Check for the existence of properties
 * - Retrieve properties, optionally providing a default value in case the property does not exist.
 * - Remove properties. This can be done in a "silent" way so that it doesn't complain if the property does not exist.
 * - Set properties
 * - Unwrap it and get a stdClass representation when you're done.
 *
 * By default a "deep Swaddle" will be performed. This means that any stdClass properties that are retrieved will be
 * wrapped in a Swaddle before being returned. This is only done for stdClass properties as there is no perfect way to
 * do this for arrays (you wouldn't want to Swaddle a numerically indexed array).
 */
final class Swaddle
{
    /**
     * @var \stdClass
     *
     * The underlying structure representing the collection of key-value pairs.
     */
    private $underlying;

    /**
     * @var bool
     *
     * A flag to indicate whether or not to do a "deep swaddle".
     */
    private $deep;

    private function __construct(\stdClass $underlying, bool $deep)
    {
        $this->underlying = $underlying;
        $this->deep       = $deep;
    }

    /**
     * Wrap an array up in a Swaddle.
     *
     * @param array $config
     * @param bool $deep
     *
     * @return static
     */
    public static function wrapArray(array $config, bool $deep = true)
    {
        return new static((object)$config, $deep);
    }

    /**
     * Wrap an object up in a Swaddle.
     *
     * @param \stdClass  $config
     * @param bool       $deep
     *
     * @return static
     */
    public static function wrapObject(\stdClass $config, bool $deep = true)
    {
        return new static($config, $deep);
    }

    /**
     * Determine whether there is a property with the provided name.
     *
     * @param string $name
     *
     * @return bool
     */
    public function hasProperty(string $name): bool
    {
        return isset($this->underlying->{$name});
    }

    /**
     * Get the property with the provided name.
     *
     * Will use the default if none is found with the provided name.
     * If the property is a \stdClass and the "deep" setting enabled then the property will be wrapped up in a Swaddle.
     *
     * @param string      $name
     * @param mixed|null  $default
     *
     * @return Swaddle|mixed
     *
     * @throws SwaddlePropertyDoesNotExistException  The property does not exist and no default has been provided.
     */
    public function getProperty(string $name, $default = null)
    {
        if ($this->hasProperty($name)) {
            $property = $this->underlying->{$name};
            if ($this->deep && $property instanceof \stdClass) {
                $property = static::wrapObject($property);
            }
            return $property;
        }

        if ($default !== null) {
            return $default;
        }

        throw new SwaddlePropertyDoesNotExistException($name, $this);
    }

    /**
     * Remove a property with the provided name.
     *
     * @param string  $name
     * @param bool    $silent
     *
     * @return void
     *
     * @throws SwaddlePropertyDoesNotExistException  The property does not exist and we're not doing this silently.
     */
    public function removeProperty(string $name, bool $silent = false)
    {
        if ($this->hasProperty($name)) {
            unset($this->underlying->{$name});
            return;
        }

        if (!$silent) {
            throw new SwaddlePropertyDoesNotExistException($name, $this);
        }
    }

    /**
     * Set a property with the provided name and value.
     *
     * @param string $name
     * @param mixed  $value
     *
     * @return void
     */
    public function setProperty(string $name, $value)
    {
        $this->underlying->{$name} = $value;
    }

    /**
     * Unwrap the Swaddle and return the underlying structure.
     *
     * @return \stdClass
     */
    public function unwrap()
    {
        return $this->underlying;
    }
}
