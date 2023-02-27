<?php

namespace Give\Tests\Framework\Addons;

use Give\Tests\Framework\TestHooks;

/**
 * This is our api for bootstrapping GiveWP add-on test suites.
 * It is intended to be used directly in an add-on's test/bootstrap.php file.
 *
 * All you need to do in tests/bootstrap.php is require the main give autoload file,
 * instantiate this class with a path to the main root add-on file, then call the load() method.
 *
 * For more advanced use cases, addHooks can be used before load().
 *
 * @since 2.23.2
 */
class Bootstrap
{
    /**
     * @var string
     */
    protected $addonPath;

    /**
     * @since 2.23.2
     */
    public function __construct(string $pathToMainAddonFile)
    {
        $this->addonPath = $pathToMainAddonFile;
    }

    /**
     * This is the all-in-one solution for bootstrapping a test environment in an add-on.
     * All you need to do in tests/bootstrap.php is require the main give autoload file,
     * instantiate this class, then call this method.
     *
     * For more advanced use cases, addHooks can be called before this method.
     *
     * @since 2.23.2
     *
     * @return void
     */
    final public function load()
    {
        $this->loadAddon();
        $this->loadGiveWPBootstrapFile();
    }

    /**
     * A declarative method for loading a plugin (GiveWP add-on) before the main WordPress testing environment boots.
     * This will ensure the add-on files will be available within testing.
     *
     * @since 2.23.2
     */
    protected function loadAddon(): Bootstrap
    {
        TestHooks::addFilter('muplugins_loaded', function () {
            require_once $this->addonPath;
        });

        return $this;
    }

    /**
     * @since 2.23.2
     *
     * @return void
     */
    final protected function loadGiveWPBootstrapFile()
    {
        require_once __DIR__ . '/../../../tests/bootstrap.php';
    }

    /**
     * If the add-on needs to run additional hooks, this method can be used to do so within a callback.
     *
     * @since 2.23.2
     */
    public function addHooks(callable $callback): Bootstrap
    {
        $callback();

        return $this;
    }
}
