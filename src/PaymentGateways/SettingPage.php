<?php

namespace Give\PaymentGateways;

/**
 * Interface SettingSection
 * @package Give\Views\Admin\Settings
 *
 * @since 2.9.0
 */
interface SettingPage
{
    /**
     * Provides the section id to be use to render setting page.
     *
     * @since 2.9.0
     *
     * @return string
     */
    public function getId();

    /**
     * Provides the section title to be displayed to the user.
     *
     * @since 2.9.0
     *
     * @return string
     */
    public function getName();

    /**
     * Provides the section settings to be displayed to the user.
     *
     * @since 2.9.0
     *
     * @return array
     */
    public function getSettings();

    /**
     * Boot functionality
     *
     * @since 2.9.0
     */
    public function boot();
}
