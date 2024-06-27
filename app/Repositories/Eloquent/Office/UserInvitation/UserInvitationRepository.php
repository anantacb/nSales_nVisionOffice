<?php

namespace App\Repositories\Eloquent\Office\UserInvitation;

use App\Models\Office\UserInvitation;
use App\Repositories\Eloquent\Base\BaseRepository;

class UserInvitationRepository extends BaseRepository implements UserInvitationRepositoryInterface
{
    public function __construct(UserInvitation $model)
    {
        parent::__construct($model);
    }
}
