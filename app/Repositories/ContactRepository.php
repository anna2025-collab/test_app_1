<?php

namespace App\Repositories;

use App\Models\ContactRequest;

class ContactRepository
{
    public function create(array $data): ContactRequest
    {
        return ContactRequest::create($data);
    }

    public function count(): int
    {
        return ContactRequest::query()->count();
    }
}
