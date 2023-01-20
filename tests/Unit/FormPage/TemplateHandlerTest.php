<?php

namespace Give\Tests\Unit\FormPage;

use Give\NextGen\DonationForm\Models\DonationForm;
use Give\NextGen\FormPage\TemplateHandler;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;

class TemplateHandlerTest extends TestCase
{
    use RefreshDatabase;

    public function testNextGenFormsShouldUseNextGenFormTemplate()
    {
        $donationForm = DonationForm::factory()->create();

        $templateHandler = new TemplateHandler(
            get_post($donationForm->id),
            'give-next-gen-form-template.php'
        );

        $this->assertEquals(
            $templateHandler->handle('legacy-form-template.php'),
            'give-next-gen-form-template.php'
        );
    }
}
