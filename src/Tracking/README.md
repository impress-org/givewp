# Anonymous Usage Tracking

When a site is opted in to anonymous usage tracking, GiveWP uses various hooks to collect data about how the plugin and related donation forms are configured.

Each grouping of usage data is collected on a corresponding hook in the WordPress request cycle.

During [shutdown](https://developer.wordpress.org/reference/hooks/shutdown/) any registered changes are offloaded to a background task using the [WordPress cron](https://developer.wordpress.org/plugins/cron/) system.

## Sending new tracking data

The data that is collected should belong to one of a few groups:

1. Environment configuration data
2. Plugin configuration data
3. Donation Form configuration data

Each of the groups has one or more related events with a corresponding hook and `TrackingData` class, which can be referenced in [TrackingServiceProvider](TrackingServiceProvider.php).

To add new tracking data, identify the appropriate event and the related `TrackingData`.

The returned array of data will be sent to the appropriate endpoint according the the [EventType](Enum/EventType.php) of the event.
