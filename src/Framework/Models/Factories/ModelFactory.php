<?php

namespace Give\Framework\Models\Factories;

use Exception;
use Faker\Generator;
use Give\Framework\Database\DB;
use Give\Framework\Models\Contracts\ModelCrud;

/**
 * @template M
 */
abstract class ModelFactory
{
    /**
     * @var class-string<M>
     */
    protected $model;

    /**
     * @var Generator
     */
    protected $faker;

    /**
     * @var int The number of models to create.
     */
    protected $count = 1;

    /**
     * @since 2.20.0
     *
     * @param  class-string<M>  $model
     *
     * @return void
     */
    public function __construct($model)
    {
        $this->model = $model;
        $this->faker = $this->withFaker();
    }

    /**
     * Define the model's default state.
     *
     * @return array
     */
    abstract public function definition();

    /**
     * @since 2.20.0
     *
     * @param  array  $attributes
     *
     * @return M|M[]
     */
    public function make(array $attributes = [])
    {
        $results = [];
        for ($i = 0; $i < $this->count; $i++) {
            /** @var ModelCrud $model */
            $model = $this->model;

            $instance = new $model(
                array_merge($this->definition(), $attributes)
            );

            $this->afterMaking($instance);

            $results[] = $instance;
        }

        return $this->count === 1 ? $results[0] : $results;
    }

    /**
     * @since 2.20.0
     *
     * @param  array  $attributes
     *
     * @return M|M[]
     * @throws Exception
     */
    public function create(array $attributes = [])
    {
        $instances = $this->make($attributes);
        $instances = is_array($instances) ? $instances : [$instances];

        DB::transaction(function () use ($instances) {
            foreach ($instances as $instance) {
                $instance->save();

                $this->afterCreating($instance);
            }
        });

        return $this->count === 1 ? $instances[0] : $instances;
    }

    /**
     * Get a new Faker instance.
     *
     * @return Generator
     */
    protected function withFaker()
    {
        return give()->make(Generator::class);
    }

    /**
     * Configure the factory.
     *
     * @return $this
     */
    public function configure()
    {
        return $this;
    }

    /**
     * @param int $count
     *
     * @return $this
     */
    public function count($count)
    {
        $this->count = $count;

        return $this;
    }

    /**
     * @since 2.20.0
     *
     * @param  M  $model
     *
     * @return void
     */
    public function afterCreating($model)
    {
        //
    }

    /**
     * @since 2.20.0
     *
     * @param  M  $model
     *
     * @return void
     */
    public function afterMaking($model)
    {
        //
    }
}
