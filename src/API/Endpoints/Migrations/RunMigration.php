<?php

namespace Give\API\Endpoints\Migrations;

use Exception;
use Give\Framework\Database\DB;
use Give\Framework\Migrations\Contracts\BatchMigration;
use Give\Framework\Migrations\Contracts\Migration;
use Give\Framework\Migrations\Contracts\ReversibleMigration;
use Give\Framework\Migrations\Controllers\BatchMigrationRunner;
use Give\Framework\Migrations\MigrationsRegister;
use Give\MigrationLog\MigrationLogFactory;
use Give\MigrationLog\MigrationLogStatus;
use WP_REST_Request;
use WP_REST_Response;

/**
 * Class RunMigration
 * @package    Give\API\Endpoints\Migrations
 *
 * @since      4.0.0 run batch migrations
 * @since      2.10.0
 */
class RunMigration extends Endpoint
{
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
     * @param MigrationsRegister  $migrationsRegister
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
            'migrations/run-migration',
            [
                [
                    'methods' => 'POST',
                    'callback' => [$this, 'runMigration'],
                    'permission_callback' => [$this, 'permissionsCheck'],
                    'args' => [
                        'id' => [
                            'type' => 'string',
                            'required' => true,
                        ],
                    ],
                ],
                'schema' => [$this, 'getSchema'],
            ]
        );

        register_rest_route(
            'give-api/v2',
            'migrations/run-batch-migration',
            [
                [
                    'methods' => 'POST',
                    'callback' => [$this, 'runBatchMigration'],
                    'permission_callback' => [$this, 'permissionsCheck'],
                    'args' => [
                        'id' => [
                            'type' => 'string',
                            'required' => true,
                        ],
                    ],
                ],
            ]
        );

        register_rest_route(
            'give-api/v2',
            'migrations/reschedule-failed-actions',
            [
                [
                    'methods' => 'POST',
                    'callback' => [$this, 'rescheduleFailedActions'],
                    'permission_callback' => [$this, 'permissionsCheck'],
                    'args' => [
                        'id' => [
                            'type' => 'string',
                            'required' => true,
                        ],
                    ],
                ],
            ]
        );

        /**
         * @since 4.3.0
         */
        register_rest_route(
            'give-api/v2',
            'migrations/rollback-migration',
            [
                [
                    'methods' => 'POST',
                    'callback' => [$this, 'rollbackMigration'],
                    'permission_callback' => [$this, 'permissionsCheck'],
                    'args' => [
                        'id' => [
                            'type' => 'string',
                            'required' => true,
                            'validate_callback' => function ($param) {
                                $migrationClass = $this->migrationRegister->getMigration($param);

                                return is_subclass_of($migrationClass, ReversibleMigration::class);
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
    public function runMigration(WP_REST_Request $request): WP_REST_Response
    {
        $migrationId = $request->get_param('id');
        $migrationLog = $this->migrationLogFactory->make($migrationId);

        // Begin transaction
        DB::beginTransaction();

        try {
            $migrationClass = $this->migrationRegister->getMigration($migrationId);
            /**
             * @var Migration $migration
             */
            $migration = give($migrationClass);
            $migration->run();
            // Save migration status
            $migrationLog
                ->setStatus(MigrationLogStatus::SUCCESS)
                ->setError(null)
                ->save();

            DB::commit();

            return new WP_REST_Response(['status' => true]);
        } catch (Exception $exception) {
            DB::rollback();

            $migrationLog
                ->setStatus(MigrationLogStatus::FAILED)
                ->setError([
                    'status' => __('Migration failed', 'give'),
                    'error' => [
                        'message' => $exception->getMessage(),
                        'code' => $exception->getCode(),
                        'file' => $exception->getFile(),
                        'line' => $exception->getLine(),
                    ],
                ])
                ->save();
        }

        return new WP_REST_Response(
            [
                'status' => false,
                'message' => $exception->getMessage(),
            ]
        );
    }


    /**
     * Run batch migration
     *
     * @since 4.0.0
     */
    public function runBatchMigration(WP_REST_Request $request): WP_REST_Response
    {
        $migrationId = $request->get_param('id');
        $migrationClass = $this->migrationRegister->getMigration($migrationId);

        if ( ! is_subclass_of($migrationClass, BatchMigration::class)) {
            return new WP_REST_Response([
                'status' => false,
                'message' => 'Migration is not an instance of ' . BatchMigration::class,
            ]);
        }

        try {
            // We are not running migration directly,
            // we just have to set migration status to PENDING and Migration Runner will handle it
            $migrationLog = $this->migrationLogFactory->make($migrationId);
            $migrationLog->setStatus(MigrationLogStatus::PENDING);
            $migrationLog->save();
        } catch (Exception $e) {
            return new WP_REST_Response([
                'status' => false,
                'message' => $e->getMessage(),
            ]);
        }

        return new WP_REST_Response(['status' => true]);
    }

    /**
     * Reschedule failed actions
     *
     * @since 4.0.0
     */
    public function rescheduleFailedActions(WP_REST_Request $request): WP_REST_Response
    {
        $migrationId = $request->get_param('id');
        $migrationClass = $this->migrationRegister->getMigration($migrationId);
        $migration = give($migrationClass);

        if ( ! is_subclass_of($migration, BatchMigration::class)) {
            return new WP_REST_Response([
                'status' => false,
                'message' => 'Migration is not an instance of ' . BatchMigration::class,
            ]);
        }

        try {
            (new BatchMigrationRunner($migration))->rescheduleFailedActions();
        } catch (Exception $e) {
            return new WP_REST_Response([
                'status' => false,
                'message' => $e->getMessage(),
            ]);
        }

        return new WP_REST_Response(['status' => true]);
    }


    /**
     * @since 4.3.0
     */
    public function rollbackMigration(WP_REST_Request $request): WP_REST_Response
    {
        $migrationId = $request->get_param('id');
        $migrationClass = $this->migrationRegister->getMigration($migrationId);
        $migration = give($migrationClass);
        $migrationLog = $this->migrationLogFactory->make($migrationId);

        if ($migration instanceof ReversibleMigration) {
            try {
                $migration->reverse();
                $migrationLog->setStatus(MigrationLogStatus::REVERSED);
            } catch (Exception $e) {
                $migrationLog
                    ->setStatus(MigrationLogStatus::FAILED)
                    ->setError([
                        'status' => __('Rollback failed', 'give'),
                        'error' => [
                            'message' => $e->getMessage(),
                            'code' => $e->getCode(),
                            'file' => $e->getFile(),
                            'line' => $e->getLine(),
                        ],
                    ]);

                return new WP_REST_Response([
                    'status' => false,
                    'message' => $e->getMessage(),
                ]);
            }

            $migrationLog->save();

            return new WP_REST_Response(['status' => true]);
        }

        return new WP_REST_Response(['status' => false]);
    }

}
