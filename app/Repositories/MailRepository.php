<?php

namespace App\Repositories;

use App\Repositories\Contracts\MailRepositoryInterface;

class MailRepository implements MailRepositoryInterface
{
    protected $entity;

    public function __construct()
    {

    }


    public function sendMail(array $data)
    {
        # code...
    }

}