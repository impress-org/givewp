<?php
use Give\PaymentGateways\Stripe\Repositories\Settings;

/**
 * @unreleased
 */
class UpdateStatementDescriptorAjaxRequestControllerTest extends WP_Ajax_UnitTestCase
{
    public function setUp()
    {
        parent::setUp();
    }

    public function testUnAuthorizedUserCanNotProcessAction()
    {
        $this->expectException(WPAjaxDieStopException::class);
        $this->_handleAjax('edit_stripe_account_statement_descriptor');
    }

    public function testReturnErrorOnEmptyStatementDescriptor()
    {
        $this->_setRole('administrator');
        $_GET['statement-descriptor'] = '';
        $_GET['account-slug'] = 'abc';

        global $wp_roles;

        var_dump( $wp_roles );
        $this->assertTrue( wp_get_current_user()->allcaps['manage_give_settings'] );

        try {
            $this->_handleAjax('edit_stripe_account_statement_descriptor');
        } catch (WPAjaxDieContinueException $e) {
        }

        $this->assertEquals(
            '{"success":false,"data":{"errorCode":"INVALID_STRIPE_STATEMENT_DESCRIPTOR"}}',
            $this->_last_response
        );
    }

    public function testReturnErrorOnInValidStripeAccount()
    {
        $this->_setRole('administrator');
        $_GET['statement-descriptor'] = 'edfhij';
        $_GET['account-slug'] = 'abc';

        global $wp_roles;
        $this->assertTrue( get_option( $wp_roles->role_key )[ 'administrator' ][ 'capabilities' ][ 'manage_give_settings' ] );

        try {
            $this->_handleAjax('edit_stripe_account_statement_descriptor');
        } catch (WPAjaxDieContinueException $e) {
        }

        $this->assertEquals(
            '{"success":false,"data":{"errorCode":"INVALID_STRIPE_ACCOUNT_ID"}}',
            $this->_last_response
        );
    }

    public function testStatementDescriptorWillUpdateWhenPassCorrectData()
    {
        $this->_setRole('administrator');
        $this->setUpStripeAccounts();

        $_GET['statement-descriptor'] = 'edfhij';
        $_GET['account-slug'] = 'account_1';

        global $wp_roles;
        $this->assertTrue( get_option( $wp_roles->role_key )[ 'administrator' ][ 'capabilities' ][ 'manage_give_settings' ] );

        try {
            $this->_handleAjax('edit_stripe_account_statement_descriptor');
        } catch (WPAjaxDieContinueException $e) {
        }

        $this->assertEquals(
            '{"success":true,"data":{"newStatementDescriptor":"edfhij"}}',
            $this->_last_response
        );
        $this->assertEquals(
            'edfhij',
            give(Settings::class)
                ->getStripeAccountById('account_1')
                ->statementDescriptor
        );
    }

    private function setUpStripeAccounts()
    {
        give_update_option(
            '_give_stripe_get_all_accounts',
            [
                'account_1' => [
                    'type' => 'manual',
                    'account_name' => 'Account 1',
                    'account_slug' => 'account_1',
                    'account_email' => '',
                    'account_country' => 'BR',
                    'account_id' => 'account_1',
                    'live_secret_key' => 'dummy',
                    'test_secret_key' => 'dummy',
                    'live_publishable_key' => 'dummy',
                    'test_publishable_key' => 'dummy',
                    'statement_descriptor' => get_bloginfo('name'),
                ],
                'account_2' => [
                    'type' => 'manual',
                    'account_name' => 'Account 2',
                    'account_slug' => 'account_2',
                    'account_email' => '',
                    'account_country' => 'US',
                    'account_id' => 'account_2',
                    'live_secret_key' => 'dummy',
                    'test_secret_key' => 'dummy',
                    'live_publishable_key' => 'dummy',
                    'test_publishable_key' => 'dummy',
                    'statement_descriptor' => get_bloginfo('name'),
                ],
            ]
        );
    }
}
