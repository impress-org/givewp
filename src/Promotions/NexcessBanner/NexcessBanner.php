<?php

namespace Give\Promotions\NexcessBanner;

use DateTimeImmutable;
use DateTimeZone;
use Give\Vendors\Nexcess\Banners\Banner;
use Give\Vendors\Nexcess\Banners\BannerFields;
use Give\Vendors\Nexcess\Banners\Registry;

/**
 * The Nexcess software packages banner.
 *
 * @since TBD
 */
class NexcessBanner
{
    /**
     * Shared by every Nexcess plugin that shows this banner.
     *
     * @since TBD
     */
    public const ID = 'nexcess-software-packages-2026-10-14';

    /**
     * When the banner starts showing, in New York time.
     *
     * @since TBD
     */
    private const START = '2026-10-14 12:00:00';

    /**
     * @var NexcessBannerAudience
     */
    private $audience;

    /**
     * @since TBD
     */
    public function __construct(NexcessBannerAudience $audience)
    {
        $this->audience = $audience;
    }

    /**
     * Registers the banner.
     *
     * @since TBD
     */
    public function register(): void
    {
        $fields = new BannerFields();
        $fields->heading = __('Changes are coming to your plan at your next renewal.', 'give');
        $fields->body = __('Watch your inbox for details.', 'give');
        $fields->ctaLabel = __('Learn more', 'give');
        $fields->ctaUrl = 'https://explore.nexcess.com/software-packages';
        $fields->dismissLabel = __('Dismiss this banner', 'give');

        $packageUrl = plugins_url('vendor/vendor-prefixed/stellarwp/banners/', GIVE_PLUGIN_FILE);
        $start = new DateTimeImmutable(self::START, new DateTimeZone('America/New_York'));

        (new Registry())->register(new Banner(self::ID, '1.0.0', $fields, $packageUrl, $start, null));
    }

    /**
     * Renders the banner.
     *
     * @since TBD
     */
    public function render(): void
    {
        $registry = new Registry();

        if (!$registry->has(self::ID)) {
            return;
        }

        $registry->get(self::ID)->render([$this->audience, 'shouldDisplay']);
    }
}
