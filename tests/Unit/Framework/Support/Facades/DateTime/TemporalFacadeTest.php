<?php

namespace Give\Tests\Unit\Framework\Support\Facades\DateTime;

use DateTime;
use DateTimeImmutable;
use Give\Framework\Support\Facades\DateTime\TemporalFacade;
use Give\Tests\TestCase;

/**
 * @since 4.0.0
 */
final class TemporalFacadeTest extends TestCase
{
    /**
     * @since 4.0.0
     */
    public function testImmutableOrCloneReturnsCloneOfDateTimeObject()
    {
        $dateTime = new DateTime;
        $temporal = new TemporalFacade;

        $newDateTime = $temporal->immutableOrClone($dateTime);

        $this->assertNotSame($dateTime, $newDateTime);
        $this->assertInstanceOf(DateTime::class, $newDateTime);
    }

    /**
     * @since 4.0.0
     */
    public function testImmutableOrCloneReturnsSameImmutableDateTimeObject()
    {
        $dateTime = new DateTimeImmutable;
        $temporal = new TemporalFacade;

        $newDateTime = $temporal->immutableOrClone($dateTime);

        $this->assertSame($dateTime, $newDateTime);
        $this->assertInstanceOf(DateTimeImmutable::class, $newDateTime);
    }

    /**
     * @since 4.0.0
     */
    public function testImmutableStartOfDay()
    {
        $dateTime = new DateTime('2020-01-01 12:34:56');
        $temporal = new TemporalFacade;

        $newDateTime = $temporal->withStartOfDay($dateTime);

        $this->assertNotSame($dateTime, $newDateTime);
        $this->assertEquals('2020-01-01 00:00:00', $newDateTime->format('Y-m-d H:i:s'));
    }

    /**
     * @since 4.0.0
     */
    public function testImmutableEndOfDay()
    {
        $dateTime = new DateTime('2020-01-01 12:34:56');
        $temporal = new TemporalFacade;

        $newDateTime = $temporal->withEndOfDay($dateTime);

        $this->assertNotSame($dateTime, $newDateTime);
        $this->assertEquals('2020-01-01 23:59:59.999999', $newDateTime->format('Y-m-d H:i:s.u'));
    }
}
