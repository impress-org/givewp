<?php

namespace Give\FormBuilder;

class FormBuilderRouteBuilder
{
    const SLUG = 'form-builder-next-gen';

    /**
     * @var int|string
     */
    protected $donationFormID;

    /**
     * @unreleased
     *
     * @param  int|string  $donationFormID
     */
    protected function __construct($donationFormID)
    {
        $this->donationFormID = $donationFormID;
    }

    /**
     * @unreleased
     */
    public static function makeCreateFormRoute(): self
    {
        // @todo Refactor create route so as not to mix types for $donationFormID.
        return new self('new');
    }

    /**
     * @unreleased
     */
    public static function makeEditFormRoute(int $donationFormID): self
    {
        return new self($donationFormID);
    }

    /**
     * @unreleased
     */
    public function __toString()
    {
        return $this->getUrl();
    }

    /**
     * @unreleased
     */
    public function getUrl(): string
    {
        return add_query_arg(
            [
                'post_type' => 'give_forms',
                'page' => self::SLUG,
                'donationFormID' => $this->donationFormID,
            ],
            admin_url('edit.php')
        );
    }

    /**
     * @unreleased
     */
    public static function isRoute(): bool
    {
        return isset($_GET['post_type'], $_GET['page']) && $_GET['post_type'] === 'give_forms' && $_GET['page'] === self::SLUG;
    }
}
