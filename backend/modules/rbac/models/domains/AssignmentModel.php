<?php

namespace backend\modules\rbac\models\domains;

use backend\modules\rbac\helpers\RbacHelper;
use Exception;
use Yii;
use yii\base\BaseObject;
use yii\base\InvalidConfigException;
use yii\rbac\Assignment;
use yii\rbac\ManagerInterface;
use yii\web\IdentityInterface;

/**
 * @property IdentityInterface $user
 * @property int $userId
 * @property-read array $items
 */
class AssignmentModel extends BaseObject
{
    /**
     * @var IdentityInterface|null
     */
    public ?IdentityInterface $user = null;

    /**
     * @var int|null User id
     */
    public ?int $userId = null;

    /**
     * @var ManagerInterface|null
     */
    protected ?ManagerInterface $manager = null;

    /**
     * AssignmentModel constructor.
     *
     * @param IdentityInterface $user
     * @param array $config
     *
     * @throws InvalidConfigException
     */
    public function __construct(IdentityInterface $user, $config = [])
    {
        $this->user = $user;
        $this->userId = $user->getId();
        $this->manager = Yii::$app->authManager;

        if ($this->userId === null) {
            throw new InvalidConfigException('The "userId" property must be set.');
        }

        parent::__construct($config);
    }

    /**
     * Assign a roles and permissions to the user.
     * @param array $items
     * @return bool
     * @throws Exception
     */
    public function assign(array $items): bool
    {
        foreach ($items as $name) {
            $item = $this->manager->getRole($name);
            $item = $item ?: $this->manager->getPermission($name);
            $this->manager->assign($item, $this->userId);
        }

        return true;
    }

    /**
     * Revokes a roles and permissions from the user.
     * @param array $items
     * @return bool
     */
    public function revoke(array $items): bool
    {
        foreach ($items as $name) {
            $item = $this->manager->getRole($name);
            $item = $item ?: $this->manager->getPermission($name);
            $this->manager->revoke($item, $this->userId);
        }

        return true;
    }

    /**
     * Get all available and assigned roles and permissions
     * @return array
     */
    public function getItems(): array
    {
        $available = RbacHelper::getAvailableItems($this->manager);
        $assigned = [];

        /** @var Assignment $item */
        foreach ($this->manager->getAssignments($this->userId) as $item) {
            $assigned[$item->roleName] = $available[$item->roleName];
            unset($available[$item->roleName]);
        }

        return compact('available', 'assigned');
    }
}
