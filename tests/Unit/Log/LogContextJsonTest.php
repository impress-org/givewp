<?php

namespace Give\Tests\Unit\Log;

use Give\DonationSpam\Akismet\DataTransferObjects\CommentCheckArgs;
use Give\DonationSpam\Akismet\DataTransferObjects\SpamContext;
use Give\Log\LogFactory;
use Give\MigrationLog\MigrationLogModel;
use Give\Tests\TestCase;

/**
 * @since TBD
 */
class LogContextJsonTest extends TestCase
{
    /**
     * @since TBD
     */
    public function testLogContextStoresArraysAsJson()
    {
        $log = LogFactory::make('error', 'Message', 'Payment', 'Test', []);
        $log->addSupplemental('details', ['quote' => "it's \"quoted\"", 'path' => 'a\\b']);

        $context = array_values(array_filter($log->getContext(), 'is_string'));

        $this->assertSame(
            ['quote' => "it's \"quoted\"", 'path' => 'a\\b'],
            json_decode(end($context), true)
        );
    }

    /**
     * @since TBD
     */
    public function testMigrationLogErrorStoresArraysAsJson()
    {
        $model = new MigrationLogModel('migration-id');
        $model->setError(['errors' => ['Table missing']]);

        $this->assertSame(['errors' => ['Table missing']], json_decode($model->getError(), true));
    }

    /**
     * @since TBD
     */
    public function testSpamContextMessageHoldsRequestAndResponseAsJson()
    {
        $args = new CommentCheckArgs();
        $args->comment_author_email = 'donor@example.com';

        $message = (new SpamContext($args, ['body' => 'true']))->formatMessage();

        $this->assertStringContainsString('"comment_author_email": "donor@example.com"', $message);
        $this->assertStringContainsString('"body": "true"', $message);
    }
}
