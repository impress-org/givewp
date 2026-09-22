<?php

namespace Give\DonationForms\Actions;

use Give\Framework\Views\View;

/**
 * Renders a grey sketch of a form, drawn from GetFormSkeletonData, that holds the form's space
 * while the real one loads. The block prints it in the page inside its root element, and the form
 * view prints it inside the iframe, so the on-site embed and the external embed draw the same
 * markup from one place without shipping a renderer to the client.
 *
 * The markup lives in the DonationForms views under form-skeleton. Classic shows the header and
 * every section. Multi-step shows only its first step, which is the header when the form has one.
 * Two-panel puts the header beside the first section. Any other design id, including one an
 * add-on registers, renders nothing, and the embed shows a spinner.
 *
 * @since 4.17.0
 */
class RenderFormSkeleton
{
    /**
     * The core designs the sketch knows the spacing of.
     *
     * @since 4.17.0
     */
    private const KNOWN_DESIGNS = ['classic', 'multi-step', 'two-panel-steps'];

    /**
     * Blocks whose real height is far from a single input, so the section template gives them
     * their own sketch. Anything else is drawn as a label and one input row.
     *
     * @since 4.17.0
     */
    private const BLOCK_VARIANTS = [
        'givewp/donation-amount' => 'amount',
        'givewp/payment-gateways' => 'gateways',
        'givewp/donation-summary' => 'summary',
    ];

    /**
     * @since 4.17.0
     *
     * @param array $data The array GetFormSkeletonData returns.
     *
     * @return string Empty for a design the skeleton cannot sketch.
     */
    public function __invoke(array $data): string
    {
        $design = (string)($data['design'] ?? '');

        if (!in_array($design, self::KNOWN_DESIGNS, true)) {
            return '';
        }

        $sections = array_map(static function ($blocks): array {
            return array_map('strval', is_array($blocks) ? $blocks : []);
        }, array_values(is_array($data['sections'] ?? null) ? $data['sections'] : []));

        return trim(View::load('DonationForms.form-skeleton', [
            'design' => $design,
            'header' => !empty($data['header']),
            'goal' => !empty($data['goal']),
            'image' => !empty($data['image']),
            'sections' => $sections,
            'gateways' => (int)($data['gateways'] ?? 0),
            'variants' => self::BLOCK_VARIANTS,
        ]));
    }

    /**
     * The skeleton's stylesheet, raw, for the caller to inline however its page allows. It has to
     * travel with the markup because the markup must paint before any enqueued stylesheet arrives:
     * the block prints a style element next to it in the content, since anything it enqueued would
     * print in the footer, and the form view adds it to its head through wp_add_inline_style().
     *
     * @since 4.17.0
     */
    public function css(): string
    {
        static $css = null;

        if ($css === null) {
            $file = GIVE_PLUGIN_DIR . 'build/donationFormSkeletonCss.css';
            $css = is_readable($file) ? (string)file_get_contents($file) : '';
        }

        return $css;
    }
}
