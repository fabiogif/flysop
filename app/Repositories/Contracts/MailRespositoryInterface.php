<?php

namespace App\Repositories\Contracts;

interface MailRepositoryInterface
{
    public function sendMail(array $data);
}