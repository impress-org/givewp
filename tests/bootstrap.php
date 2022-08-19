<?php
require_once __DIR__ . '/../../give/tests/bootstrap.php';

tests_add_filter('muplugins_loaded', static function () {
    require_once __DIR__ . '/../give-next-gen.php';
});
