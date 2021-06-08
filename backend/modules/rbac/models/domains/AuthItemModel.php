<?php

namespace backend\modules\rbac\models\domains;

use backend\modules\rbac\helpers\RbacHelper;
use common\helpers\CommonHelper;
use Yii;
use yii\base\Exception;
use yii\base\Model;
use yii\db\Query;
use yii\rbac\Item;
use yii\rbac\ManagerInterface;
use yii\rbac\Role;

/**
 * Class AuthItemModel
 *
 * @property string $name
 * @property int $type
 * @property string $description
 * @property string $ruleName
 * @property string $data
 * @property Role|null $item
 * @property-read bool $isNewRecord
 * @property-read array $items
 */
class AuthItemModel extends Model
{
    /**
     * @var string auth item name
     */
    public string $name = '';

    /**
     * @var int auth item type
     */
    public int $type = Role::TYPE_ROLE;

    /**
     * @var string auth item description
     */
    public string $description = '';

    /**
     * @var ManagerInterface
     */
    protected ManagerInterface $manager;

    /**
     * @var Role|null
     */
    private ?Role $item;

    /**
     * AuthItemModel constructor.
     *
     * @param Role|null $item
     * @param array $config
     */
    public function __construct($item = null, $config = [])
    {
        $this->item = $item;
        $this->manager = Yii::$app->authManager;

        if ($item !== null) {
            $this->name = $item->name;
            $this->type = Role::TYPE_ROLE;
            $this->description = $item->description;
        }

        parent::__construct($config);
    }

    /**
     * @inheritdoc
     */
    public function rules(): array
    {
        return [
            [['name', 'description'], 'required'],
            ['name', 'validateName', 'when' => function () {
                return $this->isNewRecord || ($this->item->name !== $this->name);
            }],
            [['name'], 'string', 'max' => 64],
            [['description'], 'string', 'max' => 64],
            [['description'], 'validateDescription', 'when' => function () {
                return $this->isNewRecord || ($this->item->description !== $this->description);
            }],
        ];
    }

    public function beforeValidate()
    {
        $this->name = CommonHelper::transliterate($this->description);

        return parent::beforeValidate();
    }

    /**
     * Validate item name
     */
    public function validateName()
    {
        if ($this->manager->getRole($this->name) !== null || $this->manager->getPermission($this->name) !== null) {
            $this->addError('name', 'Роль с таким названием уже существует.');
        }
    }

    public function validateDescription()
    {
        if ((new Query())->from('auth_item')->andWhere(['description' => $this->description])->exists()) {
            $this->addError('description', 'Роль с таким названием уже существует.');
        }
    }

    public function afterValidate()
    {
        $this->type = Item::TYPE_ROLE;
        parent::afterValidate();
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels(): array
    {
        return [
            'name' => 'Название',
            'description' => 'Название',
        ];
    }

    /**
     * Check if is new record.
     *
     * @return bool
     */
    public function getIsNewRecord(): bool
    {
        return $this->item === null;
    }

    /**
     * Find role
     * @param string $id
     * @return null|self
     */
    public static function find(string $id)
    {
        $item = Yii::$app->authManager->getRole($id);

        if ($item !== null) {
            return new self($item);
        }

        return null;
    }

    /**
     * Save role to [[\yii\rbac\authManager]]
     * @return bool
     * @throws \Exception
     */
    public function save(): bool
    {
        if ($this->validate()) {
            if ($this->item === null) {
                $this->item = $this->manager->createRole($this->name);
                $isNew = true;
                $oldName = false;
            } else {
                $isNew = false;
                $oldName = $this->item->name;
            }

            $this->item->name = $this->name;
            $this->item->description = $this->description;

            if ($isNew) {
                $this->manager->add($this->item);
            } else {
                $this->manager->update($oldName, $this->item);
            }

            return true;
        }

        return false;
    }

    /**
     * Add child to Item
     * @param array $items
     * @return bool
     * @throws Exception
     */
    public function addChildren(array $items): bool
    {
        if ($this->item) {
            foreach ($items as $name) {
                $child = $this->manager->getPermission($name);
                if ($child === null && (int)$this->type === Item::TYPE_ROLE) {
                    $child = $this->manager->getRole($name);
                }
                $this->manager->addChild($this->item, $child);
            }
        }

        return true;
    }

    /**
     * Remove child from an item
     * @param array $items
     * @return bool
     */
    public function removeChildren(array $items): bool
    {
        if ($this->item !== null) {
            foreach ($items as $name) {
                $child = $this->manager->getPermission($name);
                if ($child === null && (int)$this->type === Item::TYPE_ROLE) {
                    $child = $this->manager->getRole($name);
                }
                $this->manager->removeChild($this->item, $child);
            }
        }

        return true;
    }

    /**
     * Get all available and assigned roles and permission
     * @return array
     */
    public function getItems(): array
    {
        $available = RbacHelper::getAvailableItems($this->manager);
        $assigned = [];

        /** @var AuthItemModel $item */
        foreach ($this->manager->getChildren($this->item->name) as $item) {
            $assigned[$item->name] = [
                'type' => (int)$item->type === Item::TYPE_ROLE ? 'role' : 'permission',
                'description' => $item->description,
            ];
            unset($available[$item->name]);
        }

        unset($available[$this->name]);

        return compact('available', 'assigned');
    }

    /**
     * @return Role|null
     */
    public function getItem()
    {
        return $this->item;
    }
}
