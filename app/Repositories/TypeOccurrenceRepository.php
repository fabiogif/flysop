<?php

namespace App\Repositories;

use App\Models\TypeOccurrence;
use App\Repositories\Contracts\TypeOccurrenceRepositoryInterface;

class TypeOccurrenceRepository implements TypeOccurrenceRepositoryInterface
{
    protected $entity;

    public function __construct(TypeOccurrence $typeOccurrence)
    {
        $this->entity = $typeOccurrence;
    }
    public function getAllTypeOccurrence(int $pre_page)
    {
        return $this->entity->paginate($pre_page);
    }

}
