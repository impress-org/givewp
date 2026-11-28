<?php

namespace Give\Framework\ListTable\Views;

/**
 * The target of a goal with what it counts, as in "500 donors". Campaign and form goals share the same
 * type values.
 *
 * @since TBD
 */
final class GoalLabel
{
    /**
     * Each label is a whole phrase, so a translation can order the words as it needs.
     *
     * @since TBD
     *
     * @param string $goalType The goal type value, such as "donors".
     * @param int|float|string $target The goal as a number, which picks the plural.
     * @param string $formatted The goal as the text to show.
     */
    public static function make(string $goalType, $target, string $formatted): string
    {
        $count = (int)round((float)$target);

        switch ($goalType) {
            case 'donations':
                /* translators: %s: a goal, as a number of donations */
                $phrase = _n('%s donation', '%s donations', $count, 'give');
                break;
            case 'subscriptions':
                /* translators: %s: a goal, as a number of recurring donations */
                $phrase = _n('%s recurring donation', '%s recurring donations', $count, 'give');
                break;
            case 'donors':
                /* translators: %s: a goal, as a number of donors */
                $phrase = _n('%s donor', '%s donors', $count, 'give');
                break;
            case 'donorsFromSubscriptions':
                /* translators: %s: a goal, as a number of donors who give on a schedule */
                $phrase = _n('%s recurring donor', '%s recurring donors', $count, 'give');
                break;
            case 'amountFromSubscriptions':
                /* translators: %s: a goal, as an amount of money from recurring donations */
                $phrase = __('%s recurring raised', 'give');
                break;
            default:
                /* translators: %s: a goal, as an amount of money */
                $phrase = __('%s raised', 'give');
        }

        return sprintf(esc_html($phrase), $formatted);
    }
}
