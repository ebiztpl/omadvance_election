<?php

namespace Modules\Sampark\Entities;

use Laravel\Sanctum\PersonalAccessToken as SanctumPersonalAccessToken;

class PersonalAccessToken extends SanctumPersonalAccessToken
{
    /**
     * The connection name for the model.
     */
    protected $connection = 'sampark';

    /**
     * The table associated with the model.
     */
    protected $table = 'personal_access_tokens';
}
