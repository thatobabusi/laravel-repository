<?php

namespace Thatobabusi\LaravelRepositoryPattern\Contracts;

interface CriteriaInterface
{
    public function apply(mixed $model, RepositoryInterface $repository): mixed;
}
