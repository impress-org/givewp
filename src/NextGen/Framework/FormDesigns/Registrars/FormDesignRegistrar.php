<?php

namespace Give\NextGen\Framework\FormDesigns\Registrars;

use Give\Framework\Exceptions\Primitives\InvalidArgumentException;
use Give\Log\Log;
use Give\NextGen\Framework\FormDesigns\Exceptions\OverflowException;
use Give\NextGen\Framework\FormDesigns\FormDesign;

/**
 * @unreleased
 */
class FormDesignRegistrar
{
    /**
     * @var array
     */
    protected $designs = [];

    /**
     * @unreleased
     */
    public function getDesigns(): array
    {
        return $this->designs;
    }

    /**
     * @unreleased
     *
     * @throws InvalidArgumentException
     */
    public function getDesign(string $id): FormDesign
    {
        if (!$this->hasDesign($id)) {
            throw new InvalidArgumentException("No design exists with the ID {$id}");
        }

        /** @var FormDesign $design */
        $design = give($this->designs[$id]);

        return $design;
    }

    /**
     * @unreleased
     */
    public function hasDesign(string $id): bool
    {
        return isset($this->designs[$id]);
    }

    /**
     * @unreleased
     */
    public function registerDesign(string $designClass)
    {
        try {
            $this->register($designClass);
        } catch (InvalidArgumentException $invalidArgumentException) {
            Log::error('Form Design Registration', ['data' => $invalidArgumentException->getMessage()]);
            throw $invalidArgumentException;
        } catch (OverflowException $overflowException) {
            Log::error('Form Design Registration ', ['data' => $overflowException->getMessage()]);
            throw $overflowException;
        }
    }

    /**
     * @unreleased
     */
    public function unregisterDesign(string $designId)
    {
        if (isset($this->designs[$designId])) {
            unset($this->designs[$designId]);
        }
    }

    /**
     * @unreleased
     *
     * @return void
     *
     * @throws OverflowException|InvalidArgumentException
     */
    private function register(string $designClass)
    {
        if (!is_subclass_of($designClass, FormDesign::class)) {
            throw new InvalidArgumentException(
                sprintf(
                    '%1$s must extend %2$s',
                    $designClass,
                    FormDesign::class
                )
            );
        }

        $designId = $designClass::id();

        if ($this->hasDesign($designId)) {
            throw new OverflowException("Cannot register a design with an id that already exists: $designId");
        }

        $this->designs[$designId] = $designClass;

        give()->singleton($designClass);
    }
}
