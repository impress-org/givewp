<?php

namespace Give\Tests\Unit\Framework\FieldsAPI;

use Give\Form\LegacyConsumer\UniqueIdAttributeGenerator;
use Give\Tests\TestCase;

final class UniqueIdAttributeGeneratorTest extends TestCase
{
    public function testGetUniqueIdForEachDonationForm()
    {
        $idFirst = give(UniqueIdAttributeGenerator::class)->getId(16, 'checkbox');
        $idSecond = give(UniqueIdAttributeGenerator::class)->getId(16, 'radio');
        $idFirstNewForm = give(UniqueIdAttributeGenerator::class)->getId(17, 'checkbox');

        $this->assertEquals('give-checkbox-16-1', $idFirst);
        $this->assertEquals('give-radio-16-1', $idSecond);
        $this->assertEquals('give-checkbox-17-1', $idFirstNewForm);
        $this->assertNotSame($idFirst, $idFirstNewForm);
    }
}
