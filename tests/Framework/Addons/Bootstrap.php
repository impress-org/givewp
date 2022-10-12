<?php

namespace GiveTests\Framework\Addons;

use GiveTests\Framework\TestHooks;

/**
 * @unreleased
 */
class Bootstrap
{
    /**
     * This is the all-in-one solution for bootstrapping a test environment in an add-on.
     * All you would need to do in tests/bootstrap.php is require the main give autoload file,
     * then call this method.
     *
     * For more advanced use cases, loadAddon and loadGiveWP can be called directly with flexibility of running addHooks..
     *
     * @unreleased
     *
     * @return void
     */
    final public function load(string $pathToMainPluginFile)
    {
        $this->loadAddon($pathToMainPluginFile);
        $this->loadGiveWP();
    }

    /**
     * A declarative method for loading a plugin (GiveWP add-on) before the main WordPress testing environment boots.
     * This will ensure the add-on files will be available within testing.
     *
     * @unreleased
     */
    public function loadAddon(string $pathToPluginFile): Bootstrap
    {
        TestHooks::addFilter('muplugins_loaded', static function () use ($pathToPluginFile) {
            require_once $pathToPluginFile;
        });

        return $this;
    }

    /**
     * This is the public way of loading the main GiveWP bootstrap file.
     * It returns void so will only be called once.
     *
     * @unreleased
     *
     * @return void
     */
    final public function loadGiveWP()
    {
        require_once __DIR__ . '/../../../tests/bootstrap.php';
    }

    /**
     * If the add-on needs to run additional hooks, this method can be used to do so within a callback.
     *
     * @unreleased
     */
    public function addHooks(callable $callback): Bootstrap
    {
        $callback();

        return $this;
    }
}
