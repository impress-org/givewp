<?php

namespace Give\Tests\Unit\Framework\ListTable\Layouts;

use Give\Campaigns\ListTable\CampaignsListTable;
use Give\DonationForms\V2\ListTable\DonationFormsListTable;
use Give\Donations\ListTable\DonationsListTable;
use Give\Donors\ListTable\DonorsListTable;
use Give\EventTickets\ListTable\EventTicketsListTable;
use Give\Framework\ListTable\Layouts\ListTableLayoutController;
use Give\Subscriptions\ListTable\SubscriptionsListTable;
use Give\Tests\TestCase;
use WP_REST_Server;

/**
 * The layout feature only adds to the server contract: the column schema and the list routes stay
 * as they were, and the layout route is the one new route.
 *
 * @since TBD
 */
class AdditiveContractTest extends TestCase
{
    /**
     * @since TBD
     */
    public function tearDown(): void
    {
        global $wp_rest_server;
        $wp_rest_server = null;

        parent::tearDown();
    }

    /**
     * @since TBD
     */
    public function testTheLayoutRouteIsTheOnlyNewRoute(): void
    {
        $disableLayoutRoute = static function (): bool {
            return true;
        };
        $hook = 'give_disable_hook-rest_api_init:' . ListTableLayoutController::class . '@registerRoutes';

        add_filter($hook, $disableLayoutRoute);
        $without = $this->routes();
        remove_filter($hook, $disableLayoutRoute);

        $with = $this->routes();

        $this->assertSame(
            ['/givewp/v3/list-table-layouts/(?P<screen>[a-z][a-z0-9-]{0,39})'],
            array_values(array_diff($with, $without))
        );
        $this->assertSame([], array_diff($without, $with));
    }

    /**
     * @since TBD
     */
    public function testTheListRoutesAreStillRegistered(): void
    {
        $routes = $this->routes();

        foreach (['donations', 'donors', 'forms'] as $list) {
            $this->assertContains("/give-api/v2/admin/{$list}", $routes);
        }
    }

    /**
     * @since TBD
     */
    public function testTheColumnSchemaKeepsItsKeys(): void
    {
        $tables = [
            CampaignsListTable::class,
            DonationFormsListTable::class,
            DonationsListTable::class,
            DonorsListTable::class,
            EventTicketsListTable::class,
            SubscriptionsListTable::class,
        ];

        foreach ($tables as $table) {
            $schema = give($table)->toArray();

            $this->assertSame(['id', 'columns'], array_keys($schema), $table);
            $this->assertNotEmpty($schema['columns'], $table);

            foreach ($schema['columns'] as $column) {
                $this->assertSame(['id', 'label', 'sortable', 'visible'], array_keys($column), $table);
            }
        }
    }

    /**
     * @since TBD
     *
     * @return string[]
     */
    private function routes(): array
    {
        global $wp_rest_server;

        $wp_rest_server = new WP_REST_Server();
        do_action('rest_api_init', $wp_rest_server);

        return array_keys($wp_rest_server->get_routes());
    }
}
