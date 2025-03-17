<?php

namespace App\Models;

use Laravel\Passport\Client as PassportClient;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Client extends PassportClient
{
    use HasUuids;
}
