<?php

namespace Give\Tests\Unit\Controller;

use Give\Controller\Form;
use Give\Tests\TestCase;
use Give_Helper_Form;

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

    /**
     * @since TBD
     */
    public function testEditSuccessPageURIReturnsUrlWhenRequestHasNoForm(): void
    {
        $url = home_url('/donation-confirmation/');

        $this->assertSame($url, Form::editSuccessPageURI($url));
    }

    /**
     * @since TBD
     */
    public function testEditFailedPageURIReturnsUrlWhenRequestHasNoForm(): void
    {
        $url = home_url('/donation-failed/');

        $this->assertSame($url, Form::editFailedPageURI($url));
    }

    /**
     * @since TBD
     */
    public function testSetFailedTransactionErrorPrintsNothingWhenTemplateIsNotRegistered(): void
    {
        $this->useFormWithUnregisteredTemplate();

        $this->expectOutputString('');

        (new Form())->setFailedTransactionError();
    }

    /**
     * @since TBD
     */
    public function testLoadDonationFormViewReturnsWhenTemplateIsNotRegistered(): void
    {
        $this->useFormWithUnregisteredTemplate();
        set_query_var('url_prefix', Give()->routeForm->getBase());

        $this->expectOutputString('');

        (new Form())->loadDonationFormView();
    }

    /**
     * @since TBD
     */
    public function testHandleOffSiteCheckoutRedirectReturnsLocationWhenTemplateIsNotRegistered(): void
    {
        $this->useFormWithUnregisteredTemplate();

        $location = home_url('/');

        $this->assertSame($location, (new Form())->handleOffSiteCheckoutRedirect($location));
    }

    /**
     * @since TBD
     */
    private function useFormWithUnregisteredTemplate(): void
    {
        global $post;

        $form = Give_Helper_Form::create_simple_form();
        give()->form_meta->update_meta($form->ID, '_give_form_template', 'not-a-registered-template');

        $post = get_post($form->ID);
    }
}
