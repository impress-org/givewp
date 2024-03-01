<?php

namespace Give\EventTickets;

use Give\BetaFeatures\Facades\FeatureFlag;
use Give\EventTickets\Actions\RegisterEventsMenuItem;
use Give\EventTickets\Actions\RenderDonationFormBlock;
use Give\EventTickets\Repositories\EventRepository;
use Give\EventTickets\Repositories\EventTicketRepository;
use Give\EventTickets\Repositories\EventTicketTypeRepository;
use Give\Framework\Migrations\MigrationsRegister;
use Give\Helpers\Hooks;
use Give\ServiceProviders\ServiceProvider as ServiceProviderInterface;

/**
 * @unreleased
 */
class ServiceProvider implements ServiceProviderInterface
{
    /**
     * @inheritDoc
     *
     * @unreleased
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
     * @unreleased
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
     * @unreleased
     */
    private function registerMigrations(): void
    {
        give(MigrationsRegister::class)->addMigrations([
            Migrations\CreateEventsTable::class,
            Migrations\CreateEventTicketTypesTable::class,
            Migrations\CreateEventTicketsTable::class,
        ]);
    }

    /**
     * @unreleased
     */
    private function registerRoutes(): void
    {
        Hooks::addAction('rest_api_init', Routes\CreateEvent::class, 'registerRoute');
        Hooks::addAction('rest_api_init', Routes\CreateEventTicketType::class, 'registerRoute');
        Hooks::addAction('rest_api_init', Routes\DeleteEventsListTable::class, 'registerRoute');
        Hooks::addAction('rest_api_init', Routes\GetEvents::class, 'registerRoute');
        Hooks::addAction('rest_api_init', Routes\GetEventsListTable::class, 'registerRoute');
        Hooks::addAction('rest_api_init', Routes\GetEventForms::class, 'registerRoute');
        Hooks::addAction('rest_api_init', Routes\GetEventTickets::class, 'registerRoute');
        Hooks::addAction('rest_api_init', Routes\GetEventTicketTypes::class, 'registerRoute');
        Hooks::addAction('rest_api_init', Routes\GetEventTicketTypesListTable::class, 'registerRoute');
        Hooks::addAction('rest_api_init', Routes\GetEventTicketTypeTickets::class, 'registerRoute');
        Hooks::addAction('rest_api_init', Routes\UpdateEvent::class, 'registerRoute');
        Hooks::addAction('rest_api_init', Routes\UpdateEventTicketType::class, 'registerRoute');
    }

    /**
     * @unreleased
     */
    private function registerMenus(): void
    {
        Hooks::addAction('admin_menu', RegisterEventsMenuItem::class, '__invoke', 15);
    }

    /**
     * @unreleased
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
    }
}
