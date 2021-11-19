<?php

namespace Give\DonorDashboards\Pipeline\Stages;

interface Stage
{

    /**
     * Pipeline stages must define an __invoke method, which accepts and returns $payload
     *
     * @since 2.10.0
     *
     * @param mixed $payload
     *
     * @return mixed
     *
     */
    public function __invoke($payload);

}
