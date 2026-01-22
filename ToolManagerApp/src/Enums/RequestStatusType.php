<?php

namespace app\Enums;

enum RequestStatusType: string
{
    case pending = 'pending';
    case rejected = 'rejected';
    case approved = 'approved';

}
