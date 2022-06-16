<?php

/**
 * Give Readme Parser
 *
 * @package     Give
 * @since       2.1.4
 * @copyright   Copyright (c) 2018, GiveWP
 * @license     https://opensource.org/licenses/gpl-license GNU Public License
 * @subpackage  Admin/Readme_Parser
 */
class Give_Readme_Parser
{
    /**
     * Readme file url
     *
     * @since  2.1.4
     * @access private
     * @var
     */
    private $file_url;

    /**
     * Give_Readme_Parser constructor.
     *
     * @param string $file_url
     */
    public function __construct(string $file_url)
    {
        $this->file_url = $file_url;
    }

    /**
     * Get required Give core minimum version for addon
     *
     * @since 2.1.4
     * @access public
     *
     * @return string
     */
    public function requires_at_least()
    {
        // Regex to extract Give core minimum version from the readme.txt file.
        preg_match('/Requires (Give|GiveWP):(.*)/i', $this->get_readme_file_content(), $_requires_at_least);

        if (is_array($_requires_at_least) && 3 === count($_requires_at_least)) {
            $_requires_at_least = trim($_requires_at_least[2]);
        } else {
            $_requires_at_least = null;
        }

        return $_requires_at_least;
    }

    /**
     * @since 2.21.0
     */
    protected function get_readme_file_content(): string
    {
        return wp_remote_retrieve_body(wp_remote_get($this->file_url));
    }
}
