<?php

use Give\Helpers\Call;
use Give\PaymentGateways\Stripe\Controllers\UpdateStatementDescriptorAjaxRequestController;

/**
 * @unreleased
 */
class UpdateStatementDescriptorAjaxRequestControllerTest extends WP_Ajax_UnitTestCase
{
    public function testReturnErrorOnEmptyStatementDescriptor()
    {
        $this->_setRole('administrator');
        $_GET['statement-descriptor'] = '';
        $_GET['account_slug'] = 'abc';

        try {
            $this->_handleAjax('edit_stripe_account_statement_descriptor');
        } catch (WPAjaxDieContinueException $e) {
        }

        $this->assertEquals(
            '{"success":false,"data":{"errorCode":"INVALID_STRIPE_STATEMENT_DESCRIPTOR"}}',
            $this->_last_response
        );
    }
}
