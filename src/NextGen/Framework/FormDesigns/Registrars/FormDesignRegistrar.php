<?php

namespace Give\NextGen\Framework\FormDesigns\Registrars;

use Give\Framework\Exceptions\Primitives\InvalidArgumentException;
use Give\Log\Log;
use Give\NextGen\Framework\FormDesigns\Exceptions\OverflowException;
use Give\NextGen\Framework\FormDesigns\FormDesign;

/**
 * @since 0.1.0
 */
class FormDesignRegistrar
{
    /**
     * @var array
     */
    protected $designs = [];

    /**
     * @since 0.1.0
     */
    public function getDesigns(): array
    {
        return $this->designs;
    }

    /**
     * @since 0.1.0
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
     * @since 0.1.0
     */
    public function hasDesign(string $id): bool
    {
        return isset($this->designs[$id]);
    }

    /**
     * @since 0.1.0
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
     * @since 0.1.0
     */
    public function unregisterDesign(string $designId)
    {
        if ($this->hasDesign($designId)) {
            unset($this->designs[$designId]);
        }
    }

    /**
     * @since 0.1.0
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
