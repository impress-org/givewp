<?php

namespace Give\Framework\ListTable\Layouts;

use WP_Error;

/**
 * Checks the shape and bounds of a stored list table layout.
 *
 * A layout holds only column ids, a density word and a page size, never a search term or a record
 * value. Anything outside the rules is rejected as a whole, so nothing partial is ever stored.
 *
 * @since TBD
 */
class ListTableLayoutValidator
{
    public const VERSION = 1;
    public const MAX_ENTRIES = 50;
    public const ID_PATTERN = '/^[A-Za-z0-9_-]{1,64}$/D';
    public const DENSITIES = ['compact', 'balanced', 'comfortable'];
    public const MIN_PER_PAGE = 1;
    public const MAX_PER_PAGE = 100;

    private const KEYS = ['version', 'visibility', 'order', 'density', 'perPage'];

    /**
     * Returns the normalized layout (keys in a fixed order, empty parts left out) or an error.
     *
     * @since TBD
     *
     * @param mixed $layout
     *
     * @return array|WP_Error
     */
    public function validate($layout)
    {
        if (!is_array($layout) || array_diff(array_keys($layout), self::KEYS)) {
            return $this->error();
        }

        if (($layout['version'] ?? null) !== self::VERSION) {
            return $this->error();
        }

        if (array_key_exists('visibility', $layout) && !$this->isValidVisibility($layout['visibility'])) {
            return $this->error();
        }

        if (array_key_exists('order', $layout) && !$this->isValidOrder($layout['order'])) {
            return $this->error();
        }

        if (array_key_exists('density', $layout) && !in_array($layout['density'], self::DENSITIES, true)) {
            return $this->error();
        }

        if (array_key_exists('perPage', $layout) && !$this->isValidPerPage($layout['perPage'])) {
            return $this->error();
        }

        return $this->normalize($layout);
    }

    /**
     * @since TBD
     *
     * @param mixed $visibility
     */
    private function isValidVisibility($visibility): bool
    {
        if (!is_array($visibility) || count($visibility) > self::MAX_ENTRIES) {
            return false;
        }

        if ($visibility && $this->isList($visibility)) {
            return false;
        }

        foreach ($visibility as $id => $visible) {
            if (!$this->isValidId((string)$id) || !is_bool($visible)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @since TBD
     *
     * @param mixed $order
     */
    private function isValidOrder($order): bool
    {
        if (!is_array($order) || count($order) > self::MAX_ENTRIES || !$this->isList($order)) {
            return false;
        }

        foreach ($order as $id) {
            if (!is_string($id) || !$this->isValidId($id)) {
                return false;
            }
        }

        return count(array_unique($order)) === count($order);
    }

    /**
     * @since TBD
     *
     * @param mixed $perPage
     */
    private function isValidPerPage($perPage): bool
    {
        return is_int($perPage) && $perPage >= self::MIN_PER_PAGE && $perPage <= self::MAX_PER_PAGE;
    }

    /**
     * @since TBD
     */
    private function isValidId(string $id): bool
    {
        return (bool)preg_match(self::ID_PATTERN, $id);
    }

    /**
     * @since TBD
     */
    private function isList(array $values): bool
    {
        return !$values || array_keys($values) === range(0, count($values) - 1);
    }

    /**
     * @since TBD
     */
    private function normalize(array $layout): array
    {
        $normalized = ['version' => self::VERSION];

        if (!empty($layout['visibility'])) {
            $normalized['visibility'] = $layout['visibility'];
        }

        if (!empty($layout['order'])) {
            $normalized['order'] = $layout['order'];
        }

        if (isset($layout['density'])) {
            $normalized['density'] = $layout['density'];
        }

        if (isset($layout['perPage'])) {
            $normalized['perPage'] = $layout['perPage'];
        }

        return $normalized;
    }

    /**
     * @since TBD
     */
    private function error(): WP_Error
    {
        return new WP_Error(
            'give_list_table_invalid_layout',
            __('The list table layout is not valid.', 'give'),
            ['status' => 400]
        );
    }
}
