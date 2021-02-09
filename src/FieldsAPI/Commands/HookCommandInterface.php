<?php

namespace Give\FieldsAPI\Commands;

interface HookCommandInterface {
    public function __invoke( $hook );
}