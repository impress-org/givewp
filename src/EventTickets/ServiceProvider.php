<?php

namespace Give\EventTickets;

use Give\BetaFeatures\Facades\FeatureFlag;
use Give\EventTickets\Actions\AddEventTicketsToDonationConfirmationPageDonationTotal;
use Give\EventTickets\Actions\AddEventTicketsToDonationConfirmationPageEventTicketDetails;
use Give\EventTickets\Actions\RegisterEventsMenuItem;
use Give\EventTickets\Actions\RenderDonationFormBlock;
use Give\EventTickets\Actions\UpdateDonationConfirmationPageReceiptDonationAmount;
use Give\EventTickets\Repositories\EventRepository;
use Give\EventTickets\Repositories\EventTicketRepository;
use Give\EventTickets\Repositories\EventTicketTypeRepository;
use Give\Framework\Migrations\MigrationsRegister;
use Give\Helpers\Hooks;
use Give\ServiceProviders\ServiceProvider as ServiceProviderInterface;

/**
 * @since 3.6.0
 */
class ServiceProvider implements ServiceProviderInterface
{
    /**
     * @inheritDoc
     *
     * @since 3.6.0
     */
    public function register(): void
    {
        if (!FeatureFlag::eventTickets()) {
            return;
        }

        global $wpdb;
        $wpdb->give_events = "{$wpdb->prefix}give_events";
        $wpdb->give_event_tickets = "{$wpdb->prefix}give_event_tickets";
        $wpdb->give_event_ticket_types = "{$wpdb->prefix}give_event_ticket_types";

        give()->singleton('events', EventRepository::class);
        give()->singleton('eventTickets', EventTicketRepository::class);
        give()->singleton('eventTicketTypes', EventTicketTypeRepository::class);
    }

    /**
     * @inheritDoc
     *
     * @since 3.6.0
     */
    public function boot(): void
    {
        if (!FeatureFlag::eventTickets()) {
            return;
        }

        $this->registerMigrations();
        $this->registerRoutes();
        $this->registerMenus();
        $this->registerFormExtension();
    }

    /**
     * @since 3.6.0
     */
    private function registerMigrations(): void
    {
        give(MigrationsRegister::class)->addMigrations([
            Migrations\CreateEventsTable::class,
            Migrations\CreateEventTicketTypesTable::class,
            Migrations\CreateEventTicketsTable::class,
            Migrations\AddAmountColumnToEventTicketsTable::class,
        ]);
    }

    /**
     * @since 3.6.0
     */
    private function registerRoutes(): void
    {
        Hooks::addAction('rest_api_init', Routes\CreateEvent::class, 'registerRoute');
        Hooks::addAction('rest_api_init', Routes\CreateEventTicketType::class, 'registerRoute');
        Hooks::addAction('rest_api_init', Routes\DeleteEventsListTable::class, 'registerRoute');
        Hooks::addAction('rest_api_init', Routes\DeleteEventTicketType::class, 'registerRoute');
        Hooks::addAction('rest_api_init', Routes\GetEvents::class, 'registerRoute');
        Hooks::addAction('rest_api_init', Routes\GetEventsListTable::class, 'registerRoute');
        Hooks::addAction('rest_api_init', Routes\GetEventForms::class, 'registerRoute');
        Hooks::addAction('rest_api_init', Routes\GetEventTickets::class, 'registerRoute');
        Hooks::addAction('rest_api_init', Routes\GetEventTicketTypes::class, 'registerRoute');
        Hooks::addAction('rest_api_init', Routes\GetEventTicketTypeTickets::class, 'registerRoute');
        Hooks::addAction('rest_api_init', Routes\UpdateEvent::class, 'registerRoute');
        Hooks::addAction('rest_api_init', Routes\UpdateEventTicketType::class, 'registerRoute');
    }

    /**
     * @since 3.6.0
     */
    private function registerMenus(): void
    {
        Hooks::addAction('admin_menu', RegisterEventsMenuItem::class, '__invoke', 15);
    }

    /**
     * @since 3.6.0
     */
    private function registerFormExtension()
    {
        Hooks::addAction('givewp_form_builder_enqueue_scripts', Actions\EnqueueFormBuilderScripts::class);
        Hooks::addAction('givewp_donation_form_enqueue_scripts', Actions\EnqueueDonationFormScripts::class);
        Hooks::addFilter(
            'givewp_donation_form_block_render_givewp/event-tickets',
            RenderDonationFormBlock::class,
            '__invoke',
            10,
            4
        );

        //TODO: write unit tests for these actions
        Hooks::addAction('givewp_generate_confirmation_page_receipt_before_donation_total', AddEventTicketsToDonationConfirmationPageDonationTotal::class);
        Hooks::addAction('givewp_generate_confirmation_page_receipt_fill_event_ticket_details', AddEventTicketsToDonationConfirmationPageEventTicketDetails::class);
        Hooks::addFilter('givewp_generate_confirmation_page_receipt_detail_donation_amount', UpdateDonationConfirmationPageReceiptDonationAmount::class, '__invoke', 10, 2);
    }
}
