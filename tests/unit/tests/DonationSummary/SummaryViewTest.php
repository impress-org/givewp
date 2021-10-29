<?php

use Give\DonationSummary\SummaryView;

final class SummaryViewTest extends Give_Unit_Test_Case {

    public function test_get_form_template_location() {

        $view = self::create_form_summary_view([
            '_give_sequoia_form_template_settings' => [
                'donation_summary' => [
                    'location' => 'give_donation_form_before_submit',
                ],
            ],
        ]);

        $this->assertEquals( 'give_donation_form_before_submit', $view->getFormTemplateLocation() );
    }

    public function test_is_donation_summary_enabled() {

        $view = self::create_form_summary_view([
            '_give_sequoia_form_template_settings' => [
                'donation_summary' => [
                    'enabled' => 'enabled',
                ],
            ],
        ]);

        $this->assertTrue( $view->isDonationSummaryEnabled() );
    }

    public function test_get_summary_heading() {
        $view = self::create_form_summary_view([
            '_give_sequoia_form_template_settings' => [
                'donation_summary' => [
                    'heading' => 'Here\'s what you\'re about to donate',
                ],
            ],
        ]);

        $this->assertEquals( 'Here\'s what you\'re about to donate', $view->getSummaryHeading() );
    }

    protected static function create_form_summary_view( $meta ) {
        $form = Give_Helper_Form::create_simple_form( compact( 'meta' ));

        $view = new SummaryView();
        $view( $form->get_ID() );
        return $view;
    }
}
