<?php

namespace backend\modules\rbac;

use yii\base\Component;
use yii\base\Exception;
use yii\base\InvalidArgumentException;
use yii\base\InvalidConfigException;
use yii\di\Instance;
use yii\rbac\DbManager;
use yii\rbac\Item;
use yii\rbac\Permission;
use yii\rbac\Role;

class RbacService extends Component
{
    /**
     * @var string|DbManager The auth manager component ID that this migration should work with
     */
    public $authManager = 'authManager';

    /**
     * Initializes the migration.
     * This method will set [[authManager]] to be the 'authManager' application component, if it is `null`.
     * @throws InvalidConfigException
     */
    public function init()
    {
        $this->authManager = Instance::ensure($this->authManager, DbManager::class);

        parent::init();
    }

    /**
     * Creates new permission.
     * @param string $name The name of the permission
     * @param string $description The description of the permission
     * @param string|null $ruleName The rule associated with the permission
     * @param mixed|null $data The additional data associated with the permission
     * @return Permission
     * @throws \Exception
     */
    public function createPermission(string $name, string $description = '', string $ruleName = null, $data = null)
    {
        echo "    > create permission $name ...";
        $time = microtime(true);
        $permission = $this->authManager->createPermission($name);
        $permission->description = $description;
        $permission->ruleName = $ruleName;
        $permission->data = $data;
        $this->authManager->add($permission);
        echo ' done (time: ' . sprintf('%.3f', microtime(true) - $time) . "s)\n";

        return $permission;
    }

    /**
     * Creates new role.
     * @param string $name The name of the role
     * @param string $description The description of the role
     * @param string|null $ruleName The rule associated with the role
     * @param mixed|null $data The additional data associated with the role
     * @return Role
     * @throws \Exception
     */
    public function createRole(string $name, string $description = '', string $ruleName = null, $data = null)
    {
        echo "    > create role $name ...";
        $time = microtime(true);
        $role = $this->authManager->createRole($name);
        $role->description = $description;
        $role->ruleName = $ruleName;
        $role->data = $data;
        $this->authManager->add($role);
        echo ' done (time: ' . sprintf('%.3f', microtime(true) - $time) . "s)\n";

        return $role;
    }

    /**
     * Finds either role or permission or throws an exception if it is not found.
     * @param string $name
     * @return Permission|Role|null
     */
    public function findItem(string $name)
    {
        $item = $this->authManager->getRole($name);
        if ($item instanceof Role) {
            return $item;
        }
        $item = $this->authManager->getPermission($name);
        if ($item instanceof Permission) {
            return $item;
        }

        return null;
    }

    /**
     * Finds the role or throws an exception if it is not found.
     * @param string $name
     * @return Role|null
     */
    public function findRole(string $name)
    {
        $role = $this->authManager->getRole($name);
        if ($role instanceof Role) {
            return $role;
        }

        return null;
    }

    /**
     * Adds child.
     * @param Item|string $parent Either name or Item instance which is parent
     * @param Item|string $child Either name or Item instance which is child
     * @throws Exception
     */
    public function addChild($parent, $child)
    {
        if (is_string($parent)) {
            $parent = $this->findItem($parent);
        }
        if (is_string($child)) {
            $child = $this->findItem($child);
        }
        echo "    > adding $child->name as child to $parent->name ...";
        $time = microtime(true);
        $this->authManager->addChild($parent, $child);
        echo ' done (time: ' . sprintf('%.3f', microtime(true) - $time) . "s)\n";
    }

    /**
     * Removes child.
     * @param Item|string $parent Either name or Item instance which is parent
     * @param Item|string $child Either name or Item instance which is child
     */
    public function removeChild($parent, $child)
    {
        if (is_string($parent)) {
            $parent = $this->findItem($parent);
        }
        if (is_string($child)) {
            $child = $this->findItem($child);
        }
        echo "    > removing $child->name from $parent->name ...";
        $time = microtime(true);
        $this->authManager->removeChild($parent, $child);
        echo ' done (time: ' . sprintf('%.3f', microtime(true) - $time) . "s)\n";
    }

    /**
     * Assigns a role to a user.
     * @param string|Role $role
     * @param string|int $userId
     * @throws \Exception
     */
    public function assign($role, $userId)
    {
        if (is_string($role)) {
            $role = $this->findRole($role);
        }
        echo "    > assigning $role->name to user $userId ...";
        $time = microtime(true);
        $this->authManager->assign($role, $userId);
        echo ' done (time: ' . sprintf('%.3f', microtime(true) - $time) . "s)\n";
    }

    /**
     * Remove role.
     * @param string $name
     */
    public function removeRole(string $name)
    {
        $role = $this->authManager->getRole($name);
        if ($role === null) {
            throw new InvalidArgumentException("Role '{$role}' does not exists");
        }
        echo "    > removing role $role->name ...";
        $time = microtime(true);
        $this->authManager->remove($role);
        echo ' done (time: ' . sprintf('%.3f', microtime(true) - $time) . "s)\n";
    }

    /**
     * Remove permission.
     * @param string $name
     */
    public function removePermission(string $name)
    {
        $permission = $this->authManager->getPermission($name);
        if ($permission === null) {
            throw new InvalidArgumentException("Permission '{$permission}' does not exists");
        }
        echo "    > removing permission $permission->name ...";
        $time = microtime(true);
        $this->authManager->remove($permission);
        echo ' done (time: ' . sprintf('%.3f', microtime(true) - $time) . "s)\n";
    }
}
