<?php

namespace Give\Donors\Repositories;

use Give\Donors\Models\Donor;
use Give\Framework\Exceptions\Primitives\InvalidArgumentException;
use Give_DB_Donors;

/**
 * This proxy determines which donors repository to call donors->method() from.
 * In the case of naming conflicts, we will manually check SHARED_METHOD against their arguments.
 *
 * @since 2.19.6
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
     * @var Give_DB_Donors
     */
    private $legacyDonorRepository;
    /**
     * @var DonorRepository
     */
    private $donorRepository;

    /**
     * The Give_DB_Donors class extends Give_DB which has & assigns public properties that we need to
     * dynamically assign to this proxy class or else they won't be accessible.
     *
     * @since 2.19.6
     */
    public function __construct(Give_DB_Donors $legacyDonorRepository, DonorRepository $donorRepository)
    {
        $this->legacyDonorRepository = $legacyDonorRepository;
        $this->donorRepository = $donorRepository;

        $properties = get_object_vars($legacyDonorRepository);

        foreach ($properties as $key => $value) {
            $this->$key = $value;
        }
    }

    /**
     * @since 2.19.6
     *
     * @param  string  $method
     * @param  array  $parameters
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        if (in_array($method, self::SHARED_METHODS, true)) {
            return $parameters[0] instanceof Donor ? $this->donorRepository->{$method}(
                ...$parameters
            ) : $this->legacyDonorRepository->{$method}(...$parameters);
        }

        if (method_exists($this->donorRepository, $method)) {
            return $this->donorRepository->{$method}(...$parameters);
        }

        if (method_exists($this->legacyDonorRepository, $method)) {
            return $this->legacyDonorRepository->{$method}(...$parameters);
        }

        throw new InvalidArgumentException("$method does not exist.");
    }
}
