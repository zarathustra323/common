<?php
namespace Zarathustra\Common\Component;

use \Iterator;

/**
 * Wraps an associative array as an object.
 * Provides getters, setters, etc and supports getting values by path.
 *
 * @author Jacob Bare <jbare@southcomm.com>
 */
class ParameterBag implements Iterator
{
    /**
     * The configuration values (parameters).
     *
     * @var array
     */
    protected $parameters;

    /**
     * Constructor.
     *
     * @param array $parameters
     */
    public function __construct(array $parameters = array())
    {
        $this->parameters = $parameters;
    }

    /**
     * Static factory method.
     *
     * @param  array  $parameters An array of parameters
     * @return self
     */
    public static function create(array $parameters = array())
    {
        return new self($parameters);
    }

    /**
     * Returns the parameters.
     *
     * @return array An array of parameters
     */
    public function all()
    {
        return $this->parameters;
    }

    /**
     * Returns the parameters as a JSON string.
     *
     * @return  string
     */
    public function toJson()
    {
        return json_encode($this->all());
    }

    /**
     * Returns the parameter keys.
     *
     * @return array An array of parameter keys
     */
    public function keys()
    {
        return array_keys($this->parameters);
    }

    /**
     * Merges additional configuration parameters.
     *
     * @param  array    $parameters
     * @return self
     */
    public function merge(array $parameters)
    {
        $this->parameters = array_replace_recursive($this->parameters, $parameters);
        return $this;
    }

    /**
     * Gets a value based on a path.
     * If the value is an array, it will return the value as an instance of config values. This allows chaining.
     *
     * @param  string|array $path The key path, such as 'foo', 'foo.bar', or ['foo', 'bar']
     * @return mixed|self
     */
    public function get($path, $default = null)
    {
        $this->validate($path);

        $parameters = $this->parameters;
        $keys = $this->explode($path);
        foreach ($keys as $key) {
            if (isset($parameters[$key])) {
                $parameters = $parameters[$key];
            } else {
                return $this->resolveValue($default);
            }
        }
        return $this->resolveValue($parameters);
    }

    /**
     * Sets a value to a key path.
     *
     * @param  string|array $path  The key path, such as 'foo', 'foo.bar', or ['foo', 'bar']
     * @param  mixed        $value The value to set
     * @return self
     * @throws \RuntimeException
     */
    public function set($path, $value)
    {
        $this->validate($path);

        $parameters = &$this->parameters;
        $keys = $this->explode($path);
        while (count($keys) > 0) {
            if (count($keys) === 1) {
                if (is_array($parameters)) {
                    $parameters[array_shift($keys)] = $value;
                } else {
                    throw new \RuntimeException(sprintf('Can not set value at this path (%s) because is not array.', $path));
                }
            } else {
                $key = array_shift($keys);
                if (!isset($parameters[$key])) {
                    $parameters[$key] = [];
                }
                $parameters = &$parameters[$key];
            }
        }
        return $this;
    }

    /**
     * Determines if a path has a values.
     *
     * @param   string  $path
     * @return  bool
     */
    public function has($path)
    {
        return null !== $this->get($path);
    }

    /**
     * Casts a path as Configuration values, if not set.
     *
     * @param   string   $path
     * @return  mixed|Configuration
     */
    public function getAsValues($path)
    {
        return $this->get($path, []);
    }

    /**
     * Resolves a value and ensures arrays are returned as Configuration instances.
     *
     * @param   mixed   $value
     * @return  mixed|Configuration
     */
    protected function resolveValue($value)
    {
        return is_array($value) ? static::create($value) : $value;
    }

    /**
     * Gets all parameters as an array.
     *
     * @see     all()
     * @return  array
     */
    public function getAsArray()
    {
        return $this->all();
    }

    /**
     * Magic get method that dynamically calls the path.
     *
     * @param  string $property
     * @return mixed
     */
    public function __get($property)
    {
        return $this->get($property);
    }

    /**
     * Explodes a path by the path separator, or uses an array of keys.
     *
     * @param  string|array $path The key path, such as 'foo', 'foo.bar', or ['foo', 'bar']
     * @return array
     */
    protected function explode($path)
    {
        return is_array($path) ? $path : explode('.', $path);
    }

    /**
     * Validates a path.
     *
     * @param  string|array $path  The key path, such as 'foo', 'foo.bar', or ['foo', 'bar']
     * @return bool
     * @throws \InvalidArgumentException
     */
    protected function validate($path)
    {
        if (null === $path || is_object($path)) {
            throw new \InvalidArgumentException('Parameter paths must not be null or objects.');
        }
        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function rewind()
    {
        return reset($this->parameters);
    }

    /**
     * {@inheritDoc}
     */
    public function current()
    {
        return $this->resolveValue(current($this->parameters));
    }

    /**
     * {@inheritDoc}
     */
    public function key()
    {
        return key($this->parameters);
    }

    /**
     * {@inheritDoc}
     */
    public function next()
    {
        return $this->resolveValue(next($this->parameters));
    }

    /**
     * {@inheritDoc}
     */
    public function valid()
    {
        return key($this->parameters) !== null;
    }

    /**
     * {@inheritDoc}
     */
    public function count()
    {
        return count($this->parameters);
    }

    /**
     * {@inheritDoc}
     */
    public function isEmpty()
    {
        return $this->count() === 0;
    }
}
