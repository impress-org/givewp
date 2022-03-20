<?php

namespace Give\Framework\Models\Factories;

use Exception;
use Faker\Generator;
use Give\Framework\Models\Contracts\ModelCrud;
use Give\Framework\Models\Model;

abstract class ModelFactory
{
    /**
     * @var string
     */
    protected $model;
    /**
     * @var Generator
     */
    protected $faker;

    /**
     * @param  string $model
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
     * @param  array  $attributes
     * @return Model
     * @throws Exception
     */
    public function create(array $attributes = [])
    {
        /** @var ModelCrud $model */
        $model = $this->model;

        $results = $model::create(
            array_merge($this->definition(), $attributes)
        );

        $this->afterCreating($results);

        return $results;
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
     * @param  Model  $model
     * @return void
     */
    public function afterCreating($model)
    {
        //
    }
}
