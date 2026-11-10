<?php

namespace Give\Tests\Unit\Promotions\InPluginUpsells\Endpoints;

use Give\Promotions\InPluginUpsells\Endpoints\ProductRecommendationsRoute;
use Give\Tests\TestCase;
use WP_Error;
use WP_REST_Request;
use WP_REST_Response;

/**
 * @since TBD
 */
class ProductRecommendationsRouteTest extends TestCase
{
    private const OPTION = 'givewp_donations_recurring_recommendation_dismissed';

    /**
     * @since TBD
     */
    public function tearDown(): void
    {
        delete_option(self::OPTION);
        remove_all_filters('pre_update_option_' . self::OPTION);

        parent::tearDown();
    }

    /**
     * @since TBD
     */
    public function testItSavesTheDismissal(): void
    {
        $response = (new ProductRecommendationsRoute())->handleRequest($this->request());

        $this->assertInstanceOf(WP_REST_Response::class, $response);
        $this->assertSame(['option_updated' => self::OPTION], $response->get_data());
        $this->assertNotEmpty(get_option(self::OPTION));
    }

    /**
     * @since TBD
     */
    public function testItSucceedsWhenTheDismissalWasAlreadySaved(): void
    {
        update_option(self::OPTION, time());

        $response = (new ProductRecommendationsRoute())->handleRequest($this->request());

        $this->assertInstanceOf(WP_REST_Response::class, $response);
    }

    /**
     * @since TBD
     */
    public function testItReturnsAnErrorWhenTheDismissalIsNotSaved(): void
    {
        // Keeping the old value stops the save.
        add_filter('pre_update_option_' . self::OPTION, static function ($value, $oldValue) {
            return $oldValue;
        }, 10, 2);

        $response = (new ProductRecommendationsRoute())->handleRequest($this->request());

        $this->assertInstanceOf(WP_Error::class, $response);
        $this->assertSame('rest_option_update_failed', $response->get_error_code());
    }

    /**
     * @since TBD
     */
    private function request(): WP_REST_Request
    {
        $request = new WP_REST_Request('POST', '/give-api/v2/admin/recommended-options');
        $request->set_param('option', self::OPTION);

        return $request;
    }
}
