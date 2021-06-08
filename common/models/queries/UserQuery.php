<?php

namespace common\models\queries;

use common\models\enums\Employment;
use common\models\enums\UserType;
use yii\db\ActiveQuery;

/**
 * This is the ActiveQuery class for [[\common\models\domains\User]].
 *
 * @see \common\models\domains\User
 */
class UserQuery extends ActiveQuery
{

    /**
     * Добавляет условие на выборку неудаленных пользователей.
     * @return UserQuery
     */
    public function notDeleted()
    {
        return $this->andWhere(['deleted_at' => 0]);
    }

    /**
     * Добавляет условие на выборку пользователей, не являющихся системными.
     * @return UserQuery
     */
    public function notSystem()
    {
        return $this->andWhere(['<>', 'type', UserType::SYSTEM]);
    }

    /**
     * Поиск пользователя по email
     * @param $email
     * @return UserQuery
     */
    public function whereEmail($email): UserQuery
    {
        return $this->andWhere(['email' => $email]);
    }

    /**
     * Поиск пользователя по id
     * @param $id
     * @return UserQuery
     */
    public function whereId($id): UserQuery
    {
        return $this->andWhere(['id' => $id]);
    }

    /**
     * Добавляет условие на поиск только штатных сотрудников
     * @return UserQuery
     */
    public function isStaff(): UserQuery
    {
        return $this->andWhere(['employee' => Employment::STAFF]);
    }
}
