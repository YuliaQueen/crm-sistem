<?php

namespace backend\modules\rbac\models\enums;

use yii2mod\enum\helpers\BaseEnum;

class Permission extends BaseEnum
{
    /** @var string админ. */
    public const ADMIN = 'admin';

    /** @var string Доступ к управлению пользователями. */
    public const USER = 'user';

    /** @var string Доступ к управлению ролями и разрешениями. */
    public const RBAC = 'rbac';

    /** @var string Доступ к управлению системными настройками. */
    public const SETTINGS = 'settings';

    /** @var string Доступ к управлению настройками календаря. */
    public const CALENDAR = 'calendar';

    /** @var string Доступ к получению jira worklogs. */
    public const RECEIVING_WORKLOGS = 'receiving_worklogs';
}
