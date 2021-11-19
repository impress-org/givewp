<?php

namespace Give\Framework\Exceptions\Traits;

trait Loggable
{
    /**
     * Gets the Exception::getMessage() method
     *
     * @since 2.11.1
     *
     * @return string
     */
    abstract public function getMessage();

    /**
     * Returns the human-readable log message
     *
     * @since 2.11.1
     *
     * @return string
     */
    public function getLogMessage()
    {
        return $this->getMessage();
    }

    /**
     * Returns an array with the basic context details
     *
     * @since 2.11.1
     *
     * @return array
     */
    public function getLogContext()
    {
        return [
            'category' => 'Uncaught Exception',
            'exception' => $this,
        ];
    }
}
