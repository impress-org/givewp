<?php

namespace Give\Framework\ListTable\Layouts;

use Give\Framework\ListTable\Actions\RegisterListTableAssets;

/**
 * Puts the signed-in user's own list table layouts in the page, before the shared list table script.
 *
 * This is configuration, not records: column ids, a density word and a page size, for the screens
 * the user can open. WordPress prints inline data only with its handle, so it appears only on list
 * pages. Each record is checked again before it is printed, so a record changed outside the route
 * never reaches the page.
 *
 * @since TBD
 */
class PrintListTableLayouts
{
    /**
     * @var ListTableScreens
     */
    private $screens;

    /**
     * @var ListTableLayoutStore
     */
    private $store;

    /**
     * @var ListTableLayoutValidator
     */
    private $validator;

    /**
     * @since TBD
     */
    public function __construct(
        ListTableScreens $screens,
        ListTableLayoutStore $store,
        ListTableLayoutValidator $validator
    ) {
        $this->screens = $screens;
        $this->store = $store;
        $this->validator = $validator;
    }

    /**
     * @since TBD
     */
    public function __invoke(): void
    {
        if (!is_user_logged_in() || !wp_script_is(RegisterListTableAssets::HANDLE, 'registered')) {
            return;
        }

        $data = [
            'path' => ListTableLayoutController::path(),
            'layouts' => (object)$this->layouts(),
        ];

        wp_add_inline_script(
            RegisterListTableAssets::HANDLE,
            'window.GiveListTableLayouts = ' . wp_json_encode($data) . ';',
            'before'
        );
    }

    /**
     * @since TBD
     *
     * @return array<string, array>
     */
    private function layouts(): array
    {
        $stored = $this->store->allFor(get_current_user_id(), $this->screens->viewableByCurrentUser());
        $layouts = [];

        foreach ($stored as $screen => $layout) {
            $valid = $this->validator->validate($layout);

            if (!is_wp_error($valid)) {
                $layouts[$screen] = $valid;
            }
        }

        return $layouts;
    }
}
