<?php

namespace Give\Tests\Unit\Subscriptions\Models;

use DateTime;
use Exception;
use Give\Framework\Models\ModelQueryBuilder;
use Give\Framework\Support\Facades\DateTime\Temporal;
use Give\Subscriptions\Factories\SubscriptionNoteFactory;
use Give\Subscriptions\Models\Subscription;
use Give\Subscriptions\Models\SubscriptionNote;
use Give\Subscriptions\ValueObjects\SubscriptionNoteType;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;

/**
 * @unreleased
 *
 * @coversDefaultClass \Give\Subscriptions\Models\SubscriptionNote
 */
class TestSubscriptionNote extends TestCase
{
    use RefreshDatabase;

    /**
     * @unreleased
     *
     * @return void
     */
    public function testModelInstantiation()
    {
        $subscriptionNote = new SubscriptionNote([
            'subscriptionId' => 123,
            'content' => 'Test content',
            'type' => SubscriptionNoteType::ADMIN(),
        ]);

        $this->assertInstanceOf(SubscriptionNote::class, $subscriptionNote);
        $this->assertEquals(123, $subscriptionNote->subscriptionId);
        $this->assertEquals('Test content', $subscriptionNote->content);
        $this->assertInstanceOf(SubscriptionNoteType::class, $subscriptionNote->type);
        $this->assertEquals(SubscriptionNoteType::ADMIN, $subscriptionNote->type->getValue());
    }

    /**
     * @unreleased
     *
     * @return void
     */
    public function testPropertiesAreCorrectlyTyped()
    {
        $createdAt = Temporal::getCurrentDateTime();

        $subscriptionNote = new SubscriptionNote([
            'id' => 1,
            'content' => 'Test content',
            'subscriptionId' => 123,
            'type' => SubscriptionNoteType::DONOR(),
            'createdAt' => $createdAt,
        ]);

        $this->assertIsInt($subscriptionNote->id);
        $this->assertIsString($subscriptionNote->content);
        $this->assertIsInt($subscriptionNote->subscriptionId);
        $this->assertInstanceOf(SubscriptionNoteType::class, $subscriptionNote->type);
        $this->assertInstanceOf(DateTime::class, $subscriptionNote->createdAt);
    }

    /**
     * @unreleased
     *
     * @return void
     */
    public function testQueryShouldReturnModelQueryBuilder()
    {
        $queryBuilder = SubscriptionNote::query();

        $this->assertInstanceOf(ModelQueryBuilder::class, $queryBuilder);
    }

    /**
     * @unreleased
     *
     * @return void
     *
     * @throws Exception
     */
    public function testFactoryShouldCreateSubscriptionNote()
    {
        $subscriptionNote = SubscriptionNote::factory()->make([
            'subscriptionId' => 123,
            'content' => 'Factory created note',
        ]);

        $this->assertInstanceOf(SubscriptionNote::class, $subscriptionNote);
        $this->assertEquals(123, $subscriptionNote->subscriptionId);
        $this->assertEquals('Factory created note', $subscriptionNote->content);
    }

    /**
     * @unreleased
     *
     * @return void
     */
    public function testFactoryMethodReturnsCorrectFactory()
    {
        $factory = SubscriptionNote::factory();

        $this->assertInstanceOf(SubscriptionNoteFactory::class, $factory);
    }

    /**
     * @unreleased
     *
     * @return void
     */
    public function testFromQueryBuilderObjectShouldCreateSubscriptionNoteFromObject()
    {
        $createdAt = Temporal::getCurrentDateTime();

        $object = (object) [
            'id' => 1,
            'content' => 'Test content from object',
            'subscriptionId' => 123,
            'type' => SubscriptionNoteType::DONOR(),
            'createdAt' => Temporal::getFormattedDateTime($createdAt),
        ];

        $subscriptionNote = SubscriptionNote::fromQueryBuilderObject($object);

        $this->assertInstanceOf(SubscriptionNote::class, $subscriptionNote);
        $this->assertEquals(1, $subscriptionNote->id);
        $this->assertEquals('Test content from object', $subscriptionNote->content);
        $this->assertEquals(123, $subscriptionNote->subscriptionId);
        $this->assertEquals(SubscriptionNoteType::DONOR(), $subscriptionNote->type->getValue());
        $this->assertEquals($createdAt->format('Y-m-d H:i:s'), $subscriptionNote->createdAt->format('Y-m-d H:i:s'));
    }

    /**
     * @unreleased
     *
     * @return void
     */
    public function testFromQueryBuilderObjectShouldDefaultToAdminTypeWhenTypeIsNull()
    {
        $object = (object) [
            'id' => 1,
            'userId' => 1,
            'content' => 'Test content',
            'subscriptionId' => 123,
            'type' => null,
            'createdAt' => Temporal::getFormattedDateTime(Temporal::getCurrentDateTime()),
        ];

        $subscriptionNote = SubscriptionNote::fromQueryBuilderObject($object);

        $this->assertEquals(SubscriptionNoteType::ADMIN(), $subscriptionNote->type->getValue());
    }

    /**
     * @unreleased
     *
     * @return void
     */
    public function testFromQueryBuilderObjectShouldDefaultToAdminTypeWhenTypeIsEmpty()
    {
        $object = (object) [
            'id' => 1,
            'userId' => 1,
            'content' => 'Test content',
            'subscriptionId' => 123,
            'type' => '',
            'createdAt' => Temporal::getFormattedDateTime(Temporal::getCurrentDateTime()),
        ];

        $subscriptionNote = SubscriptionNote::fromQueryBuilderObject($object);

        $this->assertEquals(SubscriptionNoteType::ADMIN(), $subscriptionNote->type->getValue());
    }

    /**
     * @unreleased
     *
     * @return void
     *
     * @throws Exception
     */
    public function testSubscriptionRelationshipReturnsQueryBuilder()
    {
        $subscription = Subscription::factory()->createWithDonation();
        $subscriptionNote = new SubscriptionNote([
            'subscriptionId' => $subscription->id,
            'content' => 'Test content',
            'type' => SubscriptionNoteType::DONOR(),
        ]);

        $relationshipQuery = $subscriptionNote->subscription();

        $this->assertInstanceOf(ModelQueryBuilder::class, $relationshipQuery);
    }

    /**
     * @unreleased
     *
     * @return void
     */
    public function testModelHasCorrectPropertyDefinitions()
    {
        $subscriptionNote = new SubscriptionNote;

        $reflection = new \ReflectionClass($subscriptionNote);
        $propertiesProperty = $reflection->getProperty('properties');
        $propertiesProperty->setAccessible(true);
        $properties = $propertiesProperty->getValue($subscriptionNote);

        $expectedProperties = [
            'id' => 'int',
            'content' => 'string',
            'subscriptionId' => 'int',
            'type' => SubscriptionNoteType::class,
            'createdAt' => DateTime::class,
        ];

        $this->assertEquals($expectedProperties, $properties);
    }

    /**
     * @unreleased
     *
     * @return void
     */
    public function testModelHasCorrectRelationshipDefinitions()
    {
        $subscriptionNote = new SubscriptionNote;

        $reflection = new \ReflectionClass($subscriptionNote);
        $relationshipsProperty = $reflection->getProperty('relationships');
        $relationshipsProperty->setAccessible(true);
        $relationships = $relationshipsProperty->getValue($subscriptionNote);

        $this->assertArrayHasKey('subscription', $relationships);
        $this->assertEquals('belongs-to', $relationships['subscription']);
    }

    /**
     * @unreleased
     *
     * @return void
     */
    public function testTypeEnumValues()
    {
        $adminType = SubscriptionNoteType::ADMIN();
        $donorType = SubscriptionNoteType::DONOR();

        $this->assertEquals('admin', $adminType->getValue());
        $this->assertEquals('donor', $donorType->getValue());
        $this->assertTrue($adminType->isAdmin());
        $this->assertTrue($donorType->isDonor());
        $this->assertFalse($adminType->isDonor());
        $this->assertFalse($donorType->isAdmin());
    }
}
