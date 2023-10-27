<?php

declare(strict_types=1);

namespace Give\Framework\FieldsAPI;

class Authentication extends Group
{
    const TYPE = 'authentication';

    protected $required = false;
    protected $isAuthenticated = false;
    protected $loginRedirect = false;
    protected $loginRedirectUrl;
    protected $loginNotice;
    protected $loginConfirmation;
    protected $lostPasswordUrl;

    public static function make($name)
    {
        return parent::make($name)
            ->append(
                Text::make('login')
                    ->label(__('Username or Email Address', 'give'))
            )
            ->append(
                Password::make('password')
                    ->label(__('Password', 'give'))
            );
    }

    public function required($value = true): self
    {
        $this->required = $value;
        return $this;
    }

    public function isAuthenticated($value = true): self
    {
        $this->isAuthenticated = $value;
        return $this;
    }

    public function loginRedirect($value = true): self
    {
        $this->loginRedirect = $value;
        return $this;
    }

    public function loginRedirectUrl($value): self
    {
        $this->loginRedirectUrl = $value;
        return $this;
    }

    public function loginNotice($value): self
    {
        $this->loginNotice = $value;
        return $this;
    }

    public function loginConfirmation($value): self
    {
        $this->loginConfirmation = $value;
        return $this;
    }


    public function lostPasswordUrl($value): self
    {
        $this->lostPasswordUrl = $value;
        return $this;
    }
}
