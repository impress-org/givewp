<?php

namespace Give\Framework\Exceptions\Contracts;

interface LoggableException
{
    /**
     * Returns the human-readable message for the log
     *
     * @since 2.11.1
     *
     * @return string
     */
    public function getLogMessage();

    /**
     * Returns an associated array with additional context for the log
     *
     * @since 2.11.1
     *
     * @return array
     */
    public function getLogContext();
}
