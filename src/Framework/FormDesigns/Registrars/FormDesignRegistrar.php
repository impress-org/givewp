<?php

namespace Give\Framework\FormDesigns\Registrars;

use Give\Framework\Exceptions\Primitives\InvalidArgumentException;
use Give\Framework\FormDesigns\Exceptions\OverflowException;
use Give\Framework\FormDesigns\FormDesign;
use Give\Log\Log;

/**
 * @since 3.0.0
 */
class FormDesignRegistrar
{
    /**
     * @var array
     */
    protected $designs = [];

    /**
     * @since 3.0.0
     */
    public function getDesigns(): array
    {
        return $this->designs;
    }

    /**
     * @since 3.0.0
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
     * @since 3.0.0
     */
    public function hasDesign(string $id): bool
    {
        return isset($this->designs[$id]);
    }

    /**
     * @since 3.0.0
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
     * @since 3.0.0
     */
    public function unregisterDesign(string $designId)
    {
        if ($this->hasDesign($designId)) {
            unset($this->designs[$designId]);
        }
    }

    /**
     * @since 3.0.0
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
