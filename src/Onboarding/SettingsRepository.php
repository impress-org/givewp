<?php

namespace Give\Onboarding;

/**
 * @since 2.8.0
 */
class SettingsRepository
{

    /** @var array */
    protected $settings;

    /** @var callable */
    protected $persistCallback;

    /**
     * @since 2.8.0
     *
     * @param callable $persistCallback
     *
     * @param array    $settings
     */
    public function __construct(array $settings, callable $persistCallback)
    {
        $this->settings = $settings;
        $this->persistCallback = $persistCallback;
    }

    /**
     * @since 2.8.0
     *
     * @param string $name The setting name.
     *
     * @return mixed The setting value.
     *
     */
    public function get($name)
    {
        return ($this->has($name))
            ? $this->settings[$name]
            : null;
    }

    /**
     * @since 2.8.0
     *
     * @param mixed  $value The setting value.
     *
     * @param string $name The setting name.
     *
     * @return void
     *
     */
    public function set($name, $value)
    {
        $this->settings[$name] = $value;
    }

    /**
     * @since 2.8.0
     *
     * @param string $name The setting name.
     *
     * @return bool
     *
     */
    public function has($name)
    {
        return isset($this->settings[$name]);
    }

    /**
     * @since 2.8.0
     * @return bool False if value was not updated and true if value was updated.
     *
     */
    public function save()
    {
        return $this->persistCallback->__invoke(
            $this->settings
        );
    }
}
