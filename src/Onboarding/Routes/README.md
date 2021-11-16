# Onboarding REST Routes

## Extending Settings Endpoints

The default settings endpoint is `onboarding/settings/{setting}` where `{setting}` is the name of the setting being updated.

This endpoint structure can be extended by defining a static route that matches a specific setting.

Using the example of [CurrencyRoute.php](CurrencyRoute.php), updating the currency setting has side effects specific, namely updating the currency separators, symbol placement, and the number of decimals. Extending the `onboarding/settings/{setting}` endpoint by specifying 'onboarding/settings/currency` maintains the expected URL structure for the consuming application, while allowing for the specific setting to extend its own update logic.
