<?php

namespace Laravel\Repository\Contracts;

interface CriteriaInterface
{
    public function apply(mixed $model, RepositoryInterface $repository): mixed;
}
