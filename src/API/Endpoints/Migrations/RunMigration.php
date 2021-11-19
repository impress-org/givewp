<?php

namespace Give\API\Endpoints\Migrations;

use Exception;
use Give\Framework\Migrations\MigrationsRegister;
use Give\MigrationLog\MigrationLogFactory;
use Give\MigrationLog\MigrationLogStatus;
use WP_REST_Request;
use WP_REST_Response;

/**
 * Class RunMigration
 * @package Give\API\Endpoints\Migrations
 *
 * @since 2.10.0
 */
class RunMigration extends Endpoint
{

    /** @var string */
    protected $endpoint = 'migrations/run-migration';

    /**
     * @var MigrationsRegister
     */
    private $migrationRegister;

    /**
     * @var MigrationLogFactory
     */
    private $migrationLogFactory;

    /**
     * RunMigration constructor.
     *
     * @param MigrationsRegister $migrationsRegister
     * @param MigrationLogFactory $migrationLogFactory
     */
    public function __construct(
        MigrationsRegister $migrationsRegister,
        MigrationLogFactory $migrationLogFactory
    ) {
        $this->migrationRegister = $migrationsRegister;
        $this->migrationLogFactory = $migrationLogFactory;
    }

    /**
     * @inheritDoc
     */
    public function registerRoute()
    {
        register_rest_route(
            'give-api/v2',
            $this->endpoint,
            [
                [
                    'methods' => 'POST',
                    'callback' => [$this, 'handleRequest'],
                    'permission_callback' => [$this, 'permissionsCheck'],
                    'args' => [
                        'id' => [
                            'validate_callback' => function ($param) {
                                return ! empty(trim($param));
                            },
                        ],
                    ],
                ],
                'schema' => [$this, 'getSchema'],
            ]
        );
    }

    /**
     * @return array
     */
    public function getSchema()
    {
        return [
            '$schema' => 'http://json-schema.org/draft-04/schema#',
            'title' => 'logs',
            'type' => 'object',
            'properties' => [
                'id' => [
                    'type' => 'string',
                    'description' => esc_html__('Migration ID', 'give'),
                ],
            ],
        ];
    }

    /**
     * @param WP_REST_Request $request
     *
     * @return WP_REST_Response
     */
    public function handleRequest(WP_REST_Request $request)
    {
        global $wpdb;
        $migrationId = $request->get_param('id');
        $migrationLog = $this->migrationLogFactory->make($migrationId);

        // Begin transaction
        $wpdb->query('START TRANSACTION');

        try {
            $migrationClass = $this->migrationRegister->getMigration($migrationId);
            $migration = give($migrationClass);
            $migration->run();
            // Save migration status
            $migrationLog->setStatus(MigrationLogStatus::SUCCESS);
            $migrationLog->setError(null);
            $migrationLog->save();

            $wpdb->query('COMMIT');

            return new WP_REST_Response(['status' => true]);
        } catch (Exception $exception) {
            $wpdb->query('ROLLBACK');

            $migrationLog->setStatus(MigrationLogStatus::FAILED);
            $migrationLog->setError($exception);
            $migrationLog->save();
        }

        return new WP_REST_Response(
            [
                'status' => false,
                'message' => $exception->getMessage(),
            ]
        );
    }

}
