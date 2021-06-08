<?php

namespace common\models\queries;

use yii\db\ActiveQuery;

/**
 * This is the ActiveQuery class for [[\common\models\domains\Calendar]].
 *
 * @see \common\models\domains\Calendar
 */
class CalendarQuery extends ActiveQuery
{
    /**
     * Добавляет условие на выборку неудаленных дат.
     * @return CalendarQuery
     */
    public function notDeleted(): CalendarQuery
    {
        return $this->andWhere(['deleted_at' => 0]);
    }

    /**
     * Добавляет условие на поиск даты по id
     * @param $id
     * @return CalendarQuery
     */
    public function whereId($id): CalendarQuery
    {
        return $this->andWhere(['id' => $id]);
    }

    /**
     * Добавляет условие на поиск только общих переопределений
     *
     * @return CalendarQuery
     */
    public function isCommonEvent(): CalendarQuery
    {
        return $this->andWhere(['user_id' => null]);
    }

    /**
     * Добавляет условие на поиск только пользовательских переопределений
     *
     * @param $id
     * @return CalendarQuery
     */
    public function whereUserId($id): CalendarQuery
    {
        return $this->andWhere(['user_id' => $id]);
    }

    /**
     * Добавляет условие на поиск по конкретной дате
     * @param $date
     * @return CalendarQuery
     */
    public function whereDate($date): CalendarQuery
    {
        return $this->andWhere(['date_start' => $date]);
    }

    /**
     * Добавляет условие на поиск записей, принадлежащих интервалу $dateStart - $dateEnd
     *
     * @param $dateStart
     * @param $dateEnd
     * @return CalendarQuery
     */
    public function whereIntersectPeriod($dateStart, $dateEnd): CalendarQuery
    {
        return $this->andWhere([
            'or',
            ['and', ['<=', 'date_start', $dateStart], ['>=', 'date_end', $dateStart]],
            ['and', ['<=', 'date_start', $dateEnd], ['>=', 'date_end', $dateEnd]],
            ['and', ['>=', 'date_start', $dateStart], ['<=', 'date_end', $dateEnd]],
        ]);
    }
}
