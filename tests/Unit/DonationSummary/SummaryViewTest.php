<?php

namespace Give\Tests\Unit\DonationSummary;

use Give\DonationSummary\SummaryView;
use Give_Helper_Form;
use Give\Tests\TestCase;

final class SummaryViewTest extends TestCase
{

    public function test_get_form_template_location()
    {
        $view = self::create_form_summary_view([
            '_give_sequoia_form_template_settings' => [
                'payment_information' => [
                    'donation_summary_location' => 'give_donation_form_before_submit',
                ],
            ],
        ]);

        $this->assertEquals('give_donation_form_before_submit', $view->getFormTemplateLocation());
    }

    public function test_is_donation_summary_enabled() {
        $view = self::create_form_summary_view([
            '_give_sequoia_form_template_settings' => [
                'payment_information' => [
                    'donation_summary_enabled' => 'enabled',
                ],
            ],
        ]);

        $this->assertTrue( $view->isDonationSummaryEnabled() );
    }

    public function test_get_summary_heading() {
        $view = self::create_form_summary_view([
            '_give_sequoia_form_template_settings' => [
                'payment_information' => [
                    'donation_summary_heading' => 'Here\'s what you\'re about to donate',
                ],
            ],
        ]);

        $this->assertEquals( 'Here\'s what you\'re about to donate', $view->getSummaryHeading() );
    }

    protected static function create_form_summary_view( $meta ) {
        $meta[ '_give_form_template' ] = 'sequoia';

        $visualSettings = [
            'decimals_enabled' => 'disabled',
            'primary_color' => '#000',
        ];

        // A backwards compatibility process requires that these be set.
        $meta[ '_give_sequoia_form_template_settings' ] = array_merge([
            'introduction' => $visualSettings,
            'payment_amount' => [],
            'visual_appearance' => $visualSettings,
        ], $meta[ '_give_sequoia_form_template_settings' ] );

        $form = Give_Helper_Form::create_simple_form( compact( 'meta' ));

        $view = new SummaryView();
        $view( $form->get_ID() );
        return $view;
    }
}
