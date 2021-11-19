<?php

namespace Give\MultiFormGoals\MultiFormGoal;

use Give\MultiFormGoals\ProgressBar\Model as ProgressBar;

class Model
{

    // Settings for shortcode context
    protected $ids;
    protected $tags;
    protected $categories;
    protected $goal;
    protected $enddate;
    protected $color;
    protected $heading;
    protected $summary;
    protected $imageSrc;

    // Settings for block context
    protected $innerBlocks;

    /**
     * Constructs and sets up setting variables for a new Multi Form Goal model
     *
     * @since 2.9.0
     **@param array $args Arguments for new Multi Form Goal, including 'ids'
     */
    public function __construct(array $args)
    {
        isset($args['ids']) ? $this->ids = $args['ids'] : $this->ids = [];
        isset($args['tags']) ? $this->tags = $args['tags'] : $this->tags = [];
        isset($args['categories']) ? $this->categories = $args['categories'] : $this->categories = [];
        isset($args['goal']) ? $this->goal = $args['goal'] : $this->goal = '1000';
        isset($args['enddate']) ? $this->enddate = $args['enddate'] : $this->enddate = '';
        isset($args['color']) ? $this->color = $args['color'] : $this->color = '#28c77b';
        isset($args['heading']) ? $this->heading = $args['heading'] : $this->heading = 'Example Heading';
        isset($args['summary']) ? $this->summary = $args['summary'] : $this->summary = 'This is a summary.';
        isset($args['imageSrc']) ? $this->imageSrc = $args['imageSrc'] : $this->imageSrc = GIVE_PLUGIN_URL . 'assets/dist/images/onboarding-preview-form-image.min.jpg';
        isset($args['innerBlocks']) ? $this->innerBlocks = $args['innerBlocks'] : $this->innerBlocks = false;
    }

    /**
     * Get output markup for Multi-Form Goal
     *
     * @since 2.9.0
     **@return string
     */
    public function getOutput()
    {
        ob_start();
        $output = '';
        require $this->getTemplatePath();
        $output = ob_get_contents();
        ob_end_clean();

        return $output;
    }

    /**
     * Get image source for MultiFormGoal
     *
     * @since 2.9.0
     **@return string
     */
    public function getImageSrc()
    {
        return $this->imageSrc;
    }

    /**
     * Get heading for MultiFormGoal
     *
     * @since 2.9.0
     **@return string
     */
    public function getHeading()
    {
        return $this->heading;
    }

    /**
     * Get summary for MultiFormGoal
     *
     * @since 2.9.0
     **@return string
     */
    public function getSummary()
    {
        return $this->summary;
    }

    /**
     * Get Progress Bar output
     *
     * @since 2.9.0
     **@return string
     */
    protected function getProgressBarOutput()
    {
        $progressBar = new ProgressBar(
            [
                'ids' => $this->ids,
                'tags' => $this->tags,
                'categories' => $this->categories,
                'goal' => $this->goal,
                'enddate' => $this->enddate,
                'color' => $this->color,
            ]
        );

        return $progressBar->getOutput();
    }

    /**
     * Get template path for Multi-Form Goal component template
     * @since 2.9.0
     **/
    public function getTemplatePath()
    {
        return GIVE_PLUGIN_DIR . '/src/MultiFormGoals/resources/views/multiformgoal.php';
    }

}
