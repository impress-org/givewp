<?php

namespace Give\Log;

use Give\Log\ValueObjects\LogCategory;
use Give\Log\ValueObjects\LogType;

/**
 * Class LogFactory
 * @package Give\Log
 *
 * @since 2.10.0
 */
class LogFactory
{
    /**
     * Make LogModel instance
     *
     * @param string      $type
     * @param string      $message
     * @param string      $category
     * @param string      $source
     * @param array       $context
     * @param int|null    $logId
     * @param string|null $date
     *
     * @return LogModel
     */
    public static function make($type, $message, $category, $source, $context = [], $logId = null, $date = null)
    {
        return new LogModel($type, $message, $category, $source, $context, $logId, $date);
    }

    /**
     * Make LogModel instance from array of data
     *
     * @param array $data
     *
     * @return LogModel
     */
    public static function makeFromArray($data)
    {
        // Get default
        $data = array_merge(static::getDefaults(), $data);

        return new LogModel(
            $data['type'],
            $data['message'],
            $data['category'],
            $data['source'],
            $data['context'],
            $data['id'],
            $data['date']
        );
    }

    /**
     * Get log default fields array
     *
     * @return array
     */
    public static function getDefaults()
    {
        return [
            'type' => LogType::getDefault(),
            'message' => esc_html__('Something went wrong', 'give'),
            'category' => LogCategory::getDefault(),
            'source' => esc_html__('Give Core', 'give'),
            'context' => [],
            'id' => null,
            'date' => date('Y-m-d H:i:s'),
        ];
    }
}
