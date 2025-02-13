<?php

namespace Give\FormBuilder;

class FormBuilderRouteBuilder
{
    const SLUG = 'givewp-form-builder';

    /**
     * @var int|string
     */
    protected $donationFormID;

    /**
     * @since 3.0.0
     *
     * @param  int|string  $donationFormID
     */
    protected function __construct($donationFormID)
    {
        $this->donationFormID = $donationFormID;
    }

    /**
     * @since 3.0.0
     */
    public static function makeCreateFormRoute(): self
    {
        // @todo Refactor create route so as not to mix types for $donationFormID.
        return new self('new');
    }

    /**
     * @since 3.0.0
     */
    public static function makeEditFormRoute(int $donationFormID): self
    {
        return new self($donationFormID);
    }

    /**
     * @since 3.0.0
     */
    public function __toString()
    {
        return $this->getUrl();
    }

    /**
     * @since 3.0.0
     */
    public function getUrl(): string
    {
        $queryArgs = [
            'post_type' => 'give_forms',
            'page' => self::SLUG,
            'donationFormID' => $this->donationFormID,
        ];

        if (isset($_GET['campaignId'])) {
            $queryArgs['campaignId'] = $_GET['campaignId'];
        }

        return add_query_arg(
            [
                $queryArgs,
            ],
            admin_url('edit.php')
        );
    }

    /**
     * @since 3.0.0
     */
    public static function isRoute(): bool
    {
        return isset($_GET['post_type'], $_GET['page']) && $_GET['post_type'] === 'give_forms' && $_GET['page'] === self::SLUG;
    }
}
