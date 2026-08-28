<?php

namespace Give\TestData\Framework;

trait ProviderForwarder
{

    /** @var array */
    protected $loadedProviders = [];

    /**
     * Forward calls to a provider class.
     *
     * @since 4.16.7.2 Verify the resolved provider implements the Provider contract.
     *
     * @param string $name
     * @param array  $arguments
     *
     * @return mixed
     */
    public function __call($name, $arguments)
    {
        $provider = isset($this->loadedProviders[$name]) ? $this->loadedProviders[$name] : $this->loadProvider($name);

        if ( ! $provider instanceof Contract\Provider ) {
            return null;
        }

        return call_user_func_array($this->loadedProviders[$name], $arguments);
    }

    /**
     * Load a provider by class name, adjusted for case.
     *
     * @param string $name
     *
     * @return Contract\Provider
     */
    protected function loadProvider($name)
    {
        $providerClass = sprintf('%s\%s\%s', __NAMESPACE__, 'Provider', ucfirst($name));

        return $this->loadedProviders[$name] = give()->make($providerClass);
    }
}
