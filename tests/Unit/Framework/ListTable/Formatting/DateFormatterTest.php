<?php

namespace Give\Tests\Unit\Framework\ListTable\Formatting;

use DateTime;
use DateTimeImmutable;
use DateTimeZone;
use Give\Framework\ListTable\Formatting\DateFormatter;
use Give\Tests\TestCase;

/**
 * @since TBD
 */
class DateFormatterTest extends TestCase
{
    /**
     * @since TBD
     */
    public function tearDown(): void
    {
        foreach (['date_format', 'time_format', 'timezone_string', 'gmt_offset'] as $option) {
            delete_option($option);
        }

        parent::tearDown();
    }

    /**
     * @since TBD
     */
    public function testDateTimeUsesTheSiteFormatsAndTimezone(): void
    {
        update_option('date_format', 'F j, Y');
        update_option('time_format', 'g:i a');
        update_option('timezone_string', 'America/Sao_Paulo');

        $date = new DateTimeImmutable('2025-11-29 19:16:00', new DateTimeZone('UTC'));

        $this->assertSame('November 29, 2025 at 4:16 pm', DateFormatter::dateTime($date));
    }

    /**
     * A model date built in UTC while the site is in Sao Paulo shows Sao Paulo wall time.
     *
     * @since TBD
     */
    public function testConvertsAUtcDateToTheSiteTimezone(): void
    {
        update_option('timezone_string', 'America/Sao_Paulo');
        update_option('date_format', 'Y-m-d');
        update_option('time_format', 'H:i');

        $this->assertSame(
            '2025-11-29 at 21:00',
            DateFormatter::dateTime(new DateTime('2025-11-30 00:00:00', new DateTimeZone('UTC')))
        );
    }

    /**
     * @since TBD
     */
    public function testUtcOffsetTimezoneStillConverts(): void
    {
        update_option('timezone_string', '');
        update_option('gmt_offset', -3);
        update_option('date_format', 'Y-m-d');
        update_option('time_format', 'H:i');

        $this->assertSame(
            '2025-11-29 at 21:00',
            DateFormatter::dateTime(new DateTime('2025-11-30 00:00:00', new DateTimeZone('UTC')))
        );
    }

    /**
     * The word between the date and the time goes through translation, so a site in another
     * language does not show the English "at".
     *
     * @since TBD
     */
    public function testTheDateTimeSeparatorIsTranslatable(): void
    {
        update_option('date_format', 'Y-m-d');
        update_option('time_format', 'H:i');
        update_option('timezone_string', 'UTC');

        $translate = static function ($translation, $text, $context, $domain) {
            if ($domain === 'give' && $text === '%1$s at %2$s') {
                return '%1$s à %2$s';
            }

            return $translation;
        };

        add_filter('gettext_with_context', $translate, 10, 4);

        try {
            $this->assertSame(
                '2025-11-29 à 19:16',
                DateFormatter::dateTime(new DateTimeImmutable('2025-11-29 19:16:00', new DateTimeZone('UTC')))
            );
        } finally {
            remove_filter('gettext_with_context', $translate, 10);
        }
    }

    /**
     * @since TBD
     */
    public function testDateOnlyUsesTheDateFormat(): void
    {
        update_option('date_format', 'd/m/Y');

        $this->assertSame(
            '29/11/2025',
            DateFormatter::date(new DateTimeImmutable('2025-11-29 12:00:00', wp_timezone()))
        );
    }

    /**
     * @since TBD
     */
    public function testNullOrZeroDateIsTheDash(): void
    {
        $this->assertStringContainsString('No date', DateFormatter::dateTime(null));
        $this->assertStringContainsString('givewp-list-table-dash', DateFormatter::date(new DateTime('@0')));
    }
}
