<?php

namespace Give\Framework\Exceptions\Traits;

trait Loggable
{
    /**
     * Gets the Exception::getMessage() method
     *
     * @since 2.11.1
     */
    abstract public function getMessage();

    /**
     * Returns the human-readable log message
     *
     * @since 2.11.1
     */
    public function getLogMessage(): string
    {
        return $this->getMessage();
    }

    /**
     * Returns an array with the basic context details
     *
     * @unreleased Log meaningful data instead of exception object.
     * @since 2.11.1
     *
     * @return array
     */
    public function getLogContext(): array
    {
        return [
            'category' => 'Uncaught Exception',
            'exception' => [
                'File' => basename($this->getFile()),
                'Line' => $this->getLine(),
                'Massage' => $this->getMessage(),
                'Code' => $this->getCode()
            ]
        ];
    }
}
