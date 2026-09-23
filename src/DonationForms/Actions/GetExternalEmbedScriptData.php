<?php

namespace Give\DonationForms\Actions;

use Give\DonationForms\Models\DonationForm;
use Give\Framework\Routes\Route;

/**
 * Data the external embed script reads from window.givewpDonationFormEmbed.
 * Runs when the script is requested, so the strings are in the site's locale
 * and the URLs reflect the site's current home URL.
 *
 * Everything the script needs to know about the WordPress site travels here
 * rather than being hardcoded in the bundle: the home URL, the routes the
 * iframe loads, the form's own page, and the parameters an offsite gateway
 * return carries. The script only appends per-embed values such as the form
 * id.
 *
 * When the script URL names a form (`?form-id=42`, or the `42` path segment),
 * the response also carries that form's server-rendered skeleton, keyed by
 * id, so the embed can draw the form's shape before the form page answers.
 * Only published forms are included, and the id never selects which form the
 * embed loads: the element looks up its own `form-id` attribute in the map.
 *
 * @since 4.17.0
 */
class GetExternalEmbedScriptData
{
    /**
     * Upper bound on form ids honored per request, so a long list cannot turn
     * one script request into many form loads.
     *
     * @since 4.17.0
     */
    private const MAX_FORMS = 10;

    /**
     * @since 4.17.0
     *
     * @param array $request The query arguments and path id the router matched.
     */
    public function __invoke(array $request = []): array
    {
        return [
            'homeUrl' => home_url('/'),
            'formViewUrl' => esc_url_raw(Route::url('donation-form-view')),
            'receiptViewUrl' => esc_url_raw(Route::url('donation-confirmation-receipt-view')),
            'formPageUrl' => (new GenerateDonationFormPageUrl())(),
            /*
             * The offsite gateway return flow, as GenerateDonationConfirmationReceiptUrl
             * builds it: the listener params that must match, and the params carrying
             * the embed and receipt ids.
             */
            'receiptReturn' => [
                'match' => [
                    'givewp-event' => 'donation-completed',
                    'givewp-listener' => 'show-donation-confirmation-receipt',
                ],
                'embedIdParam' => 'givewp-embed-id',
                'receiptIdParam' => 'givewp-receipt-id',
            ],
            'i18n' => [
                'donate' => __('Donate', 'give'),
                'loading' => __('Loading', 'give'),
                'formTitle' => __('Donation Form', 'give'),
                'openForm' => __('Open donation form', 'give'),
                'close' => __('Close', 'give'),
            ],
            // An object even when empty, so the script always sees a map.
            'skeletons' => (object)$this->skeletons($this->formIds($request)),
        ];
    }

    /**
     * The form ids the request names: a comma list in `form-id` and the path
     * segment the router returns as `id`. Anything but a plain positive integer
     * is dropped, then deduplicated and capped.
     *
     * @since 4.17.0
     *
     * @return int[]
     */
    private function formIds(array $request): array
    {
        $ids = [];

        if (isset($request['form-id'])) {
            $ids = is_array($request['form-id']) ? $request['form-id'] : explode(',', (string)$request['form-id']);
        }

        if (!empty($request['id'])) {
            $ids[] = $request['id'];
        }

        // Only plain positive integers: absint() would read "-42" and "42abc" as 42.
        $ids = array_filter(array_map('trim', array_map('strval', $ids)), 'ctype_digit');
        $ids = array_values(array_unique(array_filter(array_map('intval', $ids))));

        return array_slice($ids, 0, self::MAX_FORMS);
    }

    /**
     * Skeleton markup by form id for the published forms among the ids. A form
     * whose design the skeleton cannot sketch gets no entry, and the embed
     * shows a spinner for it.
     *
     * @since 4.17.0
     *
     * @param int[] $ids
     *
     * @return array<int, string>
     */
    private function skeletons(array $ids): array
    {
        $renderer = new RenderFormSkeleton();
        $skeletonData = new GetFormSkeletonData();
        $skeletons = [];

        foreach ($ids as $id) {
            /** @var DonationForm|null $form */
            $form = DonationForm::find($id);

            if (!$form || !$form->status->isPublished()) {
                continue;
            }

            $markup = $renderer($skeletonData($form));

            if ($markup) {
                $skeletons[$id] = $markup;
            }
        }

        return $skeletons;
    }
}
