<?php

namespace Give\Framework\ListTable\FilterOptions;

/**
 * The closed list of option sources, and the list table screens that offer each one.
 *
 * @since TBD
 */
final class FilterOptionSources
{
    /**
     * @since TBD
     */
    public function has(string $source): bool
    {
        return array_key_exists($source, $this->screens());
    }

    /**
     * @since TBD
     */
    public function get(string $source): FilterOptionSource
    {
        switch ($source) {
            case 'campaign':
                return new CampaignFilterOptions();
            case 'form':
                return new FormFilterOptions();
            case 'donor':
                return new DonorFilterOptions();
        }

        throw new \InvalidArgumentException(sprintf('Unknown filter option source: %s', esc_html($source)));
    }

    /**
     * @since TBD
     */
    public function isOfferedOn(string $source, string $screen): bool
    {
        return in_array($screen, $this->screens()[$source] ?? [], true);
    }

    /**
     * @since TBD
     *
     * @return string[]
     */
    public function screensFor(string $source): array
    {
        return $this->screens()[$source] ?? [];
    }

    /**
     * @since TBD
     *
     * @return array<string, string[]>
     */
    private function screens(): array
    {
        return [
            'campaign' => ['donation-forms', 'donations', 'donors', 'subscriptions'],
            'form' => ['donations'],
            'donor' => ['donations'],
        ];
    }
}
