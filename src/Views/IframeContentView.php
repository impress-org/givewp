<?php
/**
 * Handle form template view skin.
 *
 * @package Give
 */

namespace Give\Views;

/**
 * Class IframeView
 *
 * @package Give
 * @since 2.7.0
 */
class IframeContentView
{
    /**
     * Document page title.
     *
     * This will be use to setup title tag.
     *
     * @since 2.7.0
     * @var string
     */
    protected $title;

    /**
     * Document page body.
     *
     * This will be use to setup content of body tag.
     *
     * @since 2.7.0
     * @var string
     */
    protected $body;

    /**
     * Body classes.
     *
     * This will be use to setup body tag classes.
     *
     * @since 2.7.0
     * @var array
     */
    protected $bodyClasses = ['give-form-templates'];

    /**
     * Set document page title.
     *
     * @param string $title
     *
     * @return IframeContentView $this
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Set document page title.
     *
     * @param string $body
     *
     * @return IframeContentView $this
     */
    public function setBody($body)
    {
        $this->body = $body;

        return $this;
    }

    /**
     * Set document page title.
     *
     * @param array $classes
     *
     * @return IframeContentView $this
     */
    public function setBodyClasses($classes)
    {
        $this->bodyClasses = array_merge($this->bodyClasses, (array)$classes);

        return $this;
    }

    /**
     * Render view.
     *
     * Note: if you want to overwrite this function then do not forget to add action hook in footer and header.
     * We use these hooks to manipulated donation form related actions.
     *
     * @since 2.7.0
     */
    public function render()
    {
        ob_start();
        ?>
        <!DOCTYPE html>
        <html <?php
        language_attributes(); ?>>
        <head>
            <meta charset="utf-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title><?php
                echo apply_filters('the_title', $this->title); ?></title>
            <?php
            /**
             * Fire the action hook in header
             */
            do_action('give_embed_head');
            ?>
        </head>
        <body class="<?php
        echo implode(' ', $this->bodyClasses); ?>">
        <?php
        echo $this->body;

        /**
         * Fire the action hook in footer
         */
        do_action('give_embed_footer');
        ?>
        </body>
        </html>
        <?php
        return ob_get_clean();
    }

    /**
     * Render only body html tag children of view.
     *
     * @since 2.7.0
     */
    public function renderBody()
    {
        return $this->body;
    }
}
