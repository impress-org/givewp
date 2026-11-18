<?php

namespace Give\Framework\ListTable\Layouts;

/**
 * Keeps one layout record per user, per list table screen, per site.
 *
 * Records are user options, so WordPress prefixes each key with the site's table prefix and a
 * multisite network keeps a separate record on each site.
 *
 * @since TBD
 */
class ListTableLayoutStore
{
    public const OPTION_PREFIX = 'give_list_table_layout_';

    /**
     * @since TBD
     */
    public function get(int $userId, string $screen): ?array
    {
        $layout = get_user_option($this->optionName($screen), $userId);

        return is_array($layout) ? $layout : null;
    }

    /**
     * Replaces the whole record.
     *
     * @since TBD
     */
    public function save(int $userId, string $screen, array $layout): void
    {
        update_user_option($userId, $this->optionName($screen), $layout);
    }

    /**
     * @since TBD
     */
    public function delete(int $userId, string $screen): void
    {
        delete_user_option($userId, $this->optionName($screen));
    }

    /**
     * The records for the given screens, keyed by screen. Screens without a record are left out.
     *
     * @since TBD
     *
     * @param string[] $screens
     *
     * @return array<string, array>
     */
    public function allFor(int $userId, array $screens): array
    {
        $layouts = [];

        foreach ($screens as $screen) {
            $layout = $this->get($userId, $screen);

            if ($layout !== null) {
                $layouts[$screen] = $layout;
            }
        }

        return $layouts;
    }

    /**
     * @since TBD
     */
    private function optionName(string $screen): string
    {
        return self::OPTION_PREFIX . $screen;
    }
}
