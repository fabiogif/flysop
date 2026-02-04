<?php

namespace App\Services;

use App\Repositories\Contracts\TypeOccurrenceRepositoryInterface;

class TypeOccurrenceService
{
    private $repository;

    public function __construct(TypeOccurrenceRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function getAllTypeOccurrence(int $pre_page)
    {
        return $this->repository->getAllTypeOccurrence($pre_page);
    }



}
