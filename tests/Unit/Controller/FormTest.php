<?php

namespace Give\Tests\Unit\Controller;

use Give\Controller\Form;
use Give\Tests\TestCase;

class FormTest extends TestCase
{
    /**
     * @since TBD
     */
    public function testHandleOffSiteCheckoutRedirectReturnsLocationWhenRequestHasNoForm(): void
    {
        $location = home_url('/');

        $this->assertSame($location, (new Form())->handleOffSiteCheckoutRedirect($location));
    }
}
