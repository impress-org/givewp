<?php

namespace Give\FormMigration\Contracts;

abstract class FormModelDecorator
{
    protected $form;

    public function __call($name, $arguments)
    {
        return $this->form->{$name}(...$arguments);
    }

    public function __get($name)
    {
        return $this->form->{$name};
    }

    public function __set($name, $value)
    {
        $this->form->{$name} = $value;
    }
}
