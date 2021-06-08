<?php

namespace backend\modules\rbac\helpers;

use Yii;
use yii\rbac\ManagerInterface;
use yii\rbac\Permission;
use yii\rbac\Role;

class RbacHelper
{
    /**
     * Проверяет имеются ли у пользователя необходимые разрешения.
     * @param array|string $permissions Названия разрешение (и ролей), наличие которых проверяется у пользователя.
     * Достаточно одного.
     * Одно разрешение можно передать в виде строки. Несколько разрешений нужно передавать в массиве.
     * @return bool true - разрешение имеется, false - нет разрешений.
     */
    public static function checkAccess($permissions)
    {
        foreach ((array)$permissions as $permission) {
            if (Yii::$app->user->can($permission)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Получает все доступные роли и разрешения.
     * @param ManagerInterface $manager
     * @return array
     */
    public static function getAvailableItems(ManagerInterface $manager)
    {
        $available = [];

        /** @var Role $role */
        foreach ($manager->getRoles() as $name => $role) {
            $available[$name] = [
                'type' => 'role',
                'description' => $role->description
            ];
        }

        /** @var Permission $permission */
        foreach ($manager->getPermissions() as $name => $permission) {
            $available[$name] = [
                'type' => 'permission',
                'description' => $permission->description
            ];
        }

        return $available;
    }
}
