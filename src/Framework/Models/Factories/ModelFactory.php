<?php

namespace Give\Framework\Models\Factories;

use Exception;
use Give\Vendors\Faker\Generator;
use Give\Framework\Database\DB;

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
     */
    abstract public function definition(): array;

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
            $instance = $this->makeInstance($attributes);

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
            }
        });

        return $this->count === 1 ? $instances[0] : $instances;
    }

    /**
     * Creates an instance of the model from the attributes and definition.
     *
     * @since 2.23.0
     *
     * @return M
     */
    protected function makeInstance(array $attributes)
    {
        return new $this->model(array_merge($this->definition(), $attributes));
    }

    /**
     * Get a new Faker instance.
     *
     * @since 2.20.0
     */
    protected function withFaker(): Generator
    {
        return give()->make(Generator::class);
    }

    /**
     * Configure the factory.
     *
     * @since 2.20.0
     */
    public function configure(): self
    {
        return $this;
    }

    /**
     * Sets the number of models to generate.
     *
     * @since 2.20.0
     */
    public function count(int $count): self
    {
        $this->count = $count;

        return $this;
    }
}
