<?php

namespace Give\Log\ValueObjects;

/**
 * Class LogType
 * @package Give\Log\ValueObjects
 *
 * @since 2.10.0
 *
 * @method static ERROR()
 * @method static WARNING()
 * @method static NOTICE()
 * @method static SUCCESS()
 * @method static INFO()
 * @method static HTTP()
 * @method static SPAM()
 */
class LogType extends Enum
{
    const ERROR = 'error';
    const WARNING = 'warning';
    const NOTICE = 'notice';
    const SUCCESS = 'success';
    const INFO = 'info';
    const HTTP = 'http';
    const SPAM = 'spam';

    /**
     * @inheritDoc
     */
    public static function getDefault()
    {
        return LogType::ERROR;
    }

    /**
     * Get types translated
     *
     * @return array
     */
    public static function getTypesTranslated()
    {
        return [
            self::ERROR => esc_html__('Error', 'give'),
            self::WARNING => esc_html__('Warning', 'give'),
            self::NOTICE => esc_html__('Notice', 'give'),
            self::SUCCESS => esc_html__('Success', 'give'),
            self::INFO => esc_html__('Info', 'give'),
            self::HTTP => esc_html__('HTTP', 'give'),
            self::SPAM => esc_html__('Spam', 'give'),
        ];
    }
}
