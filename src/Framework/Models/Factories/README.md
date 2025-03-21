# Model Factory

## Introduction

Factory classes are used to programmatically create instances of models. This is useful for seeding databases, creating test data, and other situations where you need to create a lot of model instances.

```php
<?php

namespace Give\Campaigns\Factories;

class CampaignFactory extends Give\Framework\Models\Factories\ModelFactory
{
    public function definition(): array
    {
        return [
            'title' => __('GiveWP Campaign', 'give'),
            'description' => $this->faker->paragraph(),
        ];
    }
}
```

Each instance of a model created by a factory class is populated with default values defined in the `definition` method, which can be hard-coded or generated dynamically using integrated the `fakerphp/faker` library.

## Creating Models with Factories

A model can be instantiated using the factory `make()` method, which uses the defaults provided by the `definition()` method to create the model instance.

```php
use Give\Campaigns\Models\Campaign;

$campaign = Campaign::factory()->make();
```

Additionally, multiple model instances can be created using the `count()` method.

```php
use Give\Campaigns\Models\Campaign;

$campaigns = Campaign::factory()->count(3)->make();
```

### Overriding Attributes

You can override these default values by passing an array of attributes to the factory's `make()` or `create()` method.

```php
use Give\Campaigns\Models\Campaign;

$campaign Campaign::factory()->create([
    'title' => 'My Custom Campaign',
]);
```

### Persisting Models

The `create()` method instantiates model instances and persists them to the database using model's `save()` method.

```php
use Give\Campaigns\Models\Campaign;

$campaign Campaign::factory()->create();
```

### Deferred Resolution

Sometimes you may need to defer the resolution of an attribute until the model is being created. Either the attribute definition is not a simple value or is otherwise expensive to instantiate.

Factory attribute definitions can be deferred using `Closure` callbacks instead of a hard-coded or generated value.

```php
    public function definition(): array
    {
        return [
            'title' => __('GiveWP Campaign', 'give'),
            'description' => function() {
                return prompt('Write a short description for a fundraising campaign.')
            },
        ];
    }
```

Additionally, deferred attributes are not resolved when the attribute is overridden, which prevents unnecessary computation when the default value is not used.

```php
$campaign = Campaign::factory()->create([
    'description' => 'My custom description',
]);
```

## Model Relationships with Factories

Attribute definitions can also be other model factories, which can be resolved to a property value, such as an ID, to create required dependencies.

```php
    public function definition(): array
    {
        return [
            'title' => __('GiveWP Campaign', 'give'),
            'formId' => DonationForm::factory()->createAndResolveTo('id'),
        ];
    }
```

This is particularly useful when defining model relationships, where the related model is only instantiated when a model is not explicity provided as an override.

If an existing model (or model ID) is provided as an override, the factory will not instantiate an additional model.

```php
Campaign::factory()->create([
    'formId' => $donationForm->id,
]);
```
