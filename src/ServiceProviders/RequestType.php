<?php

namespace Give\ServiceProviders;

/**
 * @since 2.23.1
 */
class RequestType
{
    const ADMIN = 'admin';
    const AJAX = 'ajax';
    const CRON = 'cron';
    const FRONTEND = 'frontend';
    const WPCLI = 'wpcli';
}
