<?php

namespace Give\Form\LegacyConsumer;

class FilterCallbackCollection
{

    /**
     * @param array $callbacks
     */
    public function __construct(array $callbacks)
    {
        $this->callbacks = $callbacks;
    }

    /**
     * @param array $callbacks
     *
     * @return FilterCallbackCollection
     */
    public static function make(array $callbacks)
    {
        return new self($callbacks);
    }

    /**
     * @return FilterCallbackCollection
     */
    public function flatten()
    {
        $callbacks = array_reduce(
            $this->callbacks,
            function ($carry, $callbacks) {
                return array_merge($carry, $callbacks);
            },
            []
        );

        return new self($callbacks);
    }

    /**
     * @param string $prefix
     *
     * @return FilterCallbackCollection
     */
    public function withoutPrefix($prefix)
    {
        $callbacks = array_filter(
            $this->callbacks,
            function ($callback) use ($prefix) {
                if (is_string($callback['function'])) {
                    return $prefix !== substr($callback['function'], 0, strlen($prefix));
                }

                return true;
            }
        );

        return new self($callbacks);
    }

    /**
     * @return int
     */
    public function count()
    {
        return count($this->callbacks);
    }
}
