<?php

namespace Give\TestData\Commands;

use Exception;
use Give\TestData\Factories\DonationFormFactory;
use Give\TestData\Repositories\DonationFormRepository;
use WP_CLI;

/**
 * Class FormSeedCommand
 * @package Give\TestData\Commands
 *
 * A WP-CLI command to generate Donation Forms
 */
class FormSeedCommand
{

    /**
     * @var DonationFormFactory
     */
    private $donationFormFactory;
    /**
     * @var DonationFormRepository
     */
    private $donationFormRepository;

    public function __construct(
        DonationFormFactory $donationFormFactory,
        DonationFormRepository $donationFormRepository
    ) {
        $this->donationFormFactory = $donationFormFactory;
        $this->donationFormRepository = $donationFormRepository;
    }

    /**
     * Generate Donation Forms
     *
     * ## OPTIONS
     *
     * [--count=<count>]
     * : Number of donations to generate
     * default: 10
     *
     * [--template=<template>]
     * : Form template
     * default: random
     * options:
     *   - sequoia
     *   - legacy
     *   - random
     *
     * [--set-goal=<bool>]
     * : Set donation form goal
     * default: false
     *
     * [--set-terms=<bool>]
     * : Set donation form terms and conditions
     * default: false
     *
     * [--preview=<preview>]
     * : Preview generated data
     * default: false
     *
     * [--consistent=<consistent>]
     * : Generate consistent data
     * default: false
     *
     * ## EXAMPLES
     *
     *     wp give test-donation-form --count=10 --template=legacy --set-goal=true --set-terms=true
     *
     * @when after_wp_load
     */
    public function __invoke($args, $assocArgs)
    {
        global $wpdb;

        // Get CLI args
        $count = WP_CLI\Utils\get_flag_value($assocArgs, 'count', $default = 10);
        $preview = WP_CLI\Utils\get_flag_value($assocArgs, 'preview', $default = false);
        $template = WP_CLI\Utils\get_flag_value($assocArgs, 'template', $default = 'random');
        $setGoal = WP_CLI\Utils\get_flag_value($assocArgs, 'set-goal', $default = false);
        $setTerms = WP_CLI\Utils\get_flag_value($assocArgs, 'set-terms', $default = false);
        $consistent = WP_CLI\Utils\get_flag_value($assocArgs, 'consistent', $default = false);

        // Check form template
        if ( ! $this->donationFormFactory->checkFormTemplate($template)) {
            WP_CLI::error(
                WP_CLI::colorize("Unsupported form template: %g{$template}%n")
            );
        }

        // Factory config
        $this->donationFormFactory->setFormTemplate($template);
        $this->donationFormFactory->setDonationFormGoal($setGoal);
        $this->donationFormFactory->setTermsAndConditions($setTerms);

        // Generate donation forms
        $forms = $this->donationFormFactory->consistent($consistent)->make($count);

        if ($preview) {
            WP_CLI\Utils\format_items(
                'table',
                $forms,
                array_keys($this->donationFormFactory->definition())
            );
        } else {
            $progress = WP_CLI\Utils\make_progress_bar('Generating donation forms', $count);

            // Start DB transaction
            $wpdb->query('START TRANSACTION');

            try {
                foreach ($forms as $form) {
                    $this->donationFormRepository->insertDonationForm($form);
                    $progress->tick();
                }

                $wpdb->query('COMMIT');

                $progress->finish();
            } catch (Exception $e) {
                $wpdb->query('ROLLBACK');

                WP_CLI::error($e->getMessage());
            }
        }
    }

}
