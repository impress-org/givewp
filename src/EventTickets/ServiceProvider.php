<?php

namespace Give\EventTickets;

use Give\Framework\Migrations\MigrationsRegister;
use Give\Helpers\Hooks;
use Give\ServiceProviders\ServiceProvider as ServiceProviderInterface;
use Give\EventTickets\Repositories\EventRepository;
use Give\EventTickets\Repositories\EventTicketRepository;
use Give\EventTickets\Repositories\EventTicketTypeRepository;

/**
 * @unreleased
 */
class ServiceProvider implements ServiceProviderInterface
{
    /**
     * @unreleased
     * @inheritDoc
     */
    public function register(): void
    {
        global $wpdb;
        $wpdb->give_events = "{$wpdb->prefix}give_events";
        $wpdb->give_event_tickets = "{$wpdb->prefix}give_event_tickets";
        $wpdb->give_event_ticket_types = "{$wpdb->prefix}give_event_ticket_types";

        give()->singleton('events', EventRepository::class);
        give()->singleton('eventTickets', EventTicketRepository::class);
        give()->singleton('eventTicketTypes', EventTicketTypeRepository::class);
    }

    /**
     * @unreleased
     * @inheritDoc
     */
    public function boot(): void
    {
        give( MigrationsRegister::class )->addMigrations([
            Migrations\CreateEventsTable::class,
            Migrations\CreateEventTicketTypesTable::class,
            Migrations\CreateEventTicketsTable::class,
        ]);

        Hooks::addAction('givewp_form_builder_enqueue_scripts', Actions\EnqueueFormBuilderScripts::class);
        Hooks::addAction('givewp_donation_form_enqueue_scripts', Actions\EnqueueDonationFormScripts::class);

        Hooks::addAction('rest_api_init', Routes\GetEvents::class, 'registerRoute');
        Hooks::addAction('rest_api_init', Routes\GetEventsListTable::class, 'registerRoute');
        Hooks::addAction('rest_api_init', Routes\GetEventTickets::class, 'registerRoute');
        Hooks::addAction('rest_api_init', Routes\GetEventTicketTypes::class, 'registerRoute');
        Hooks::addAction('rest_api_init', Routes\GetEventTicketTypeTickets::class, 'registerRoute');
    }
}
