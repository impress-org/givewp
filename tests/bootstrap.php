<?php

use TestsNextGen\TestHooks;

// TODO: replace with path to givewp core autoload once TestHooks exists
require __DIR__ . '/../../givewp-next-gen/vendor/autoload.php';

TestHooks::loadPlugin(__DIR__ . '/../give-next-gen.php');

require_once __DIR__ . '/../../give/tests/bootstrap.php';
