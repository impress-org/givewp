<?php

namespace Give\Framework\FieldsAPI\Concerns;

use BadMethodCallException;
use ReflectionClass;

/**
 * Trait HasGettersAndSetters
 */
trait HasGettersAndSetters
{
    protected $methodsCache = [];

    /**
     * Handle dynamic method calls to the object.
     *
     * @unreleased
     *
     * @return mixed
     *
     * @throws BadMethodCallException
     */
    public function __call($name, $arguments = null)
    {
        $gettersAndSetters = $this->extractGettersAndSetters();

        if (!array_key_exists($name, $gettersAndSetters)) {
            throw new BadMethodCallException(sprintf(__('Method %s does not exist', 'givewp'), $name));
        }

        if (strpos($name, 'get') === 0) {
            $property = lcfirst(substr($name, 3));

            if (!property_exists($this, $property)) {
                throw new BadMethodCallException(sprintf(__('Property %s does not exist', 'givewp'), $property));
            }

            return $this->$property;
        }

        if (!property_exists($this, $name)) {
            throw new BadMethodCallException(sprintf(__('Property %s does not exist', 'givewp'), $name));
        }

        if (empty($arguments)) {
            throw new BadMethodCallException(sprintf(__('No argument provided for %s', 'givewp'), $name));
        }

        $this->$name = $arguments[0];

        return $this;
    }

    /**
     * Extract and cache method annotations from the docblock
     *
     * @unreleased
     */
    private function extractGettersAndSetters(): array
    {
        if (null !== $this->methodsCache) {
            return $this->methodsCache;
        }

        $methods = [];
        $class = new ReflectionClass($this);

        $docComment = $class->getDocComment();
        $docComment = explode("\n", $docComment);
        $docComment = array_filter($docComment, function ($line) {
            return strpos($line, '@method') !== false;
        });

        /** @var array $docComment */
        foreach ($docComment as $line) {
            $pattern = '/@method\s*(\w+)\((\w+\s+\$\w+)?\):?\s*(\w+)?/';
            preg_match($pattern, $line, $matches);

            $methods[$matches[1]] = array_combine(
                ['method', 'params', 'return'],
                array_slice(array_pad($matches, 4, ''), 1)
            );
        }

        $this->methodsCache = $methods;

        return $methods;
    }
}
