<?php

namespace Give\TestData\Factories;

use Give\TestData\Framework\Factory;

/**
 * Class DonationFormFactory
 * @package Give\TestData\Factories
 */
class DonationFormFactory extends Factory
{

    /**
     * @var bool
     */
    private $donationGoal;

    /**
     * @var bool
     */
    private $termsAndConditions;

    /**
     * @var string
     */
    private $template;

    /**
     * @var string[]
     */
    private $templates = ['sequoia', 'legacy'];

    /**
     * @param string $template
     *
     * @return bool
     */
    public function checkFormTemplate($template)
    {
        if ('random' === $template) {
            return true;
        }

        return in_array($template, $this->templates);
    }

    /**
     * @param string $template
     */
    public function setFormTemplate($template)
    {
        $this->template = $template;
    }

    /**
     * @return string
     */
    public function getFormTemplate()
    {
        if ('random' === $this->template) {
            return $this->randomDonationTemplate();
        }

        return $this->template;
    }

    /**
     * @return string
     */
    public function randomDonationTemplate()
    {
        return $this->faker->randomElement($this->templates);
    }

    /**
     * @param bool $generate
     */
    public function setDonationFormGoal($generate)
    {
        $this->donationGoal = (bool)$generate;
    }

    /**
     * @return false|string
     */
    public function getDonationGoal()
    {
        if (is_null($this->donationGoal) || ! $this->donationGoal) {
            return false;
        }

        return $this->randomGoal();
    }

    /**
     * @param bool $generate
     */
    public function setTermsAndConditions($generate)
    {
        $this->termsAndConditions = (bool)$generate;
    }

    /**
     * @return array
     */
    public function getTermsAndConditions()
    {
        if (is_null($this->termsAndConditions) || ! $this->termsAndConditions) {
            return [];
        }

        return [
            'label' => $this->faker->catchPhrase(),
            'text' => $this->faker->text(),
        ];
    }

    /**
     * Donor definition
     *
     * @since 1.0.0
     * @return array
     */
    public function definition()
    {
        $title = $this->faker->catchPhrase();

        return [
            'post_title' => $title,
            'post_name' => sanitize_title($title),
            'post_author' => $this->randomAuthor(),
            'post_date' => date('Y-m-d H:i:s'),
            'donation_goal' => $this->getDonationGoal(),
            'donation_terms' => $this->getTermsAndConditions(),
            'form_template' => $this->getFormTemplate(),
            'random_amount' => $this->randomAmount(),
        ];
    }

}
