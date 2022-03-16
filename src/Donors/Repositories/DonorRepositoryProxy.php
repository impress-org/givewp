<?php

namespace Give\Donors\Repositories;

use Give\Donors\Models\Donor;
use Give\Framework\Exceptions\Primitives\InvalidArgumentException;
use Give_DB_Donors;

/**
 * This proxy determines which donors repository to call donors->method() from.
 * In the case of naming conflicts, we will manually check SHARED_METHOD against their arguments.
 *
 * @unreleased
 *
 * @mixin DonorRepository
 * @mixin Give_DB_Donors
 *
 * @throws InvalidArgumentException
 */
class DonorRepositoryProxy
{
    const SHARED_METHODS = ['insert', 'update', 'delete'];

    /**
     * The Give_DB_Donors class extends Give_DB which has & assigns public properties that we need to
     * dynamically assign to this proxy class or else they won't be accessible.
     *
     * @unreleased
     */
    public function __construct()
    {
        /** @var Give_DB_Donors $legacyDonorRepository */
        $legacyDonorRepository = give(Give_DB_Donors::class);

        $properties = get_object_vars($legacyDonorRepository);

        foreach ($properties as $key => $value) {
            $this->$key = $value;
        }
    }

    /**
     * @unreleased
     *
     * @param  string  $method
     * @param  array  $parameters
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        /** @var DonorRepository $donorRepository */
        $donorRepository = give(DonorRepository::class);

        /** @var Give_DB_Donors $legacyDonorRepository */
        $legacyDonorRepository = give(Give_DB_Donors::class);

        if (in_array($method, self::SHARED_METHODS, true)) {
            return $parameters[0] instanceof Donor ? $donorRepository->{$method}(
                ...$parameters
            ) : $legacyDonorRepository->{$method}(...$parameters);
        }

        if (method_exists($donorRepository, $method)) {
            return $donorRepository->{$method}(...$parameters);
        }

        if (method_exists($legacyDonorRepository, $method)) {
            return $legacyDonorRepository->{$method}(...$parameters);
        }

        throw new InvalidArgumentException("$method does not exist.");
    }

    /**
     * @unreleased
     *
     * @return void
     */
    public function __clone()
    {
        /** @var Give_DB_Donors $legacyDonorRepository */
        $legacyDonorRepository = give(Give_DB_Donors::class);

        $properties = get_object_vars($legacyDonorRepository);

        foreach ($properties as $key => $value) {
            $this->$key = $value;
        }
    }
}
