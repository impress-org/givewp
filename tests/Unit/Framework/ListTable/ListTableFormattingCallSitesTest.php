<?php

namespace Give\Tests\Unit\Framework\ListTable;

use Give\Tests\TestCase;

/**
 * Guards review criterion 1: every list column writes money and dates through MoneyFormatter and DateFormatter,
 * never with an inline call to a formatting helper.
 *
 * @since TBD
 */
class ListTableFormattingCallSitesTest extends TestCase
{
    private const PATTERN = '/formatToLocale|give_currency_filter|give_format_amount|number_format|date_i18n|wp_date|getFormattedDateTime|->format\(\s*\$format/';

    /**
     * Files that may use a helper on purpose. Keep this list short; each entry needs a reason.
     *
     * @since TBD
     *
     * @var string[]
     */
    private const ALLOW_LIST = [];

    /**
     * @since TBD
     */
    public function testNoColumnFormatsMoneyOrDatesInline(): void
    {
        foreach ($this->columnFiles() as $file) {
            $this->assertDoesNotMatchRegularExpression(
                self::PATTERN,
                (string)file_get_contents($file),
                "$file formats money or dates inline. Use MoneyFormatter or DateFormatter."
            );
        }
    }

    /**
     * @since TBD
     */
    public function testOnlyTheFormattersCallTheHelpersInTheFramework(): void
    {
        $files = array_merge(
            glob(GIVE_PLUGIN_DIR . 'src/Framework/ListTable/*.php'),
            glob(GIVE_PLUGIN_DIR . 'src/Framework/ListTable/*/*.php')
        );

        foreach ($files as $file) {
            if (strpos($file, '/Framework/ListTable/Formatting/') !== false || $this->isAllowed($file)) {
                continue;
            }

            $this->assertDoesNotMatchRegularExpression(
                self::PATTERN,
                (string)file_get_contents($file),
                "$file calls a formatting helper outside src/Framework/ListTable/Formatting."
            );
        }
    }

    /**
     * @since TBD
     */
    public function testThePatternCatchesInlineFormatting(): void
    {
        foreach (
            [
                'number_format($amount, 2)',
                "date_i18n('m/d/Y', \$time)",
                '$amount->formatToLocale($locale)',
                'give_currency_filter(give_format_amount($x))',
                '$date->format($format)',
            ] as $line
        ) {
            $this->assertMatchesRegularExpression(self::PATTERN, $line);
        }
    }

    /**
     * @since TBD
     *
     * @return string[]
     */
    private function columnFiles(): array
    {
        $files = [];
        foreach (['Campaigns', 'Donations', 'Donors', 'Subscriptions', 'EventTickets', 'DonationForms/V2'] as $screen) {
            $files = array_merge($files, glob(GIVE_PLUGIN_DIR . "src/$screen/ListTable/Columns/*.php"));
        }

        $this->assertNotEmpty($files);

        return array_values(array_filter($files, function ($file) {
            return ! $this->isAllowed($file);
        }));
    }

    /**
     * @since TBD
     */
    private function isAllowed(string $file): bool
    {
        foreach (self::ALLOW_LIST as $allowed) {
            if (substr($file, -strlen($allowed)) === $allowed) {
                return true;
            }
        }

        return false;
    }
}
