<?php

namespace common\models\domains;

use common\models\enums\DayType;
use common\models\enums\EventType;
use common\models\queries\CalendarQuery;
use common\models\queries\UserQuery;

use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii2tech\ar\softdelete\SoftDeleteBehavior;

/**
 * This is the model class for table "calendar".
 *
 * @property int $id
 * @property string $date_start
 * @property string $date_end
 * @property int $type
 * @property int $user_id
 * @property int|null $created_at
 * @property int|null $updated_at
 * @property int $deleted_at
 * @property int|null $created_by_id
 * @property int|null $updated_by_id
 *
 * @property User $createdBy
 * @property User $updatedBy
 * @property User $user
 */
class Calendar extends ActiveRecord
{
    /** @var string Сценарий создания пользовательского переопределения. */
    public const SCENARIO_CREATE_USER_EVENT = 'create_user_event';

    /** @var string Сценарий создания общего переопределения. */
    public const SCENARIO_CREATE_COMMON_EVENT = 'create_common_event';

    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return 'calendar';
    }

    public function behaviors(): array
    {
        return [
            TimestampBehavior::class => [
                'class' => TimestampBehavior::class,
            ],
            SoftDeleteBehavior::class => [
                'class' => SoftDeleteBehavior::class,
                'softDeleteAttributeValues' => [
                    'deleted_at' => time(),
                    'updated_at' => time(),
                ],
                'replaceRegularDelete' => true,
            ],
            BlameableBehavior::class => [
                'class' => BlameableBehavior::class,
                'createdByAttribute' => 'created_by_id',
                'updatedByAttribute' => 'updated_by_id',
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return
            [
                [['date_start', 'type'], 'required'],
                [['deleted_at'], 'default', 'value' => 0],
                [['type', 'user_id'], 'integer'],
                [
                    ['type'],
                    'in',
                    'range' => DayType::getConstantsByName(),
                    'on' => self::SCENARIO_CREATE_COMMON_EVENT,
                ],
                [
                    ['type'],
                    'in',
                    'range' => EventType::getConstantsByName(),
                    'on' => self::SCENARIO_CREATE_USER_EVENT,
                ],
                [
                    ['date_start', 'date_end'],
                    'userDateValidator',
                    'skipOnEmpty' => false,
                    'on' => self::SCENARIO_CREATE_USER_EVENT,
                ],
                [
                    ['date_start'],
                    'commonDateValidator',
                    'skipOnEmpty' => false,
                    'on' => self::SCENARIO_CREATE_COMMON_EVENT,
                ],
                [
                    ['date_end'],
                    'default',
                    'value' => function () {
                        return $this->date_start;
                    },
                    'on' => self::SCENARIO_CREATE_COMMON_EVENT,
                ],
                [
                    ['date_end'],
                    'required',
                    'on' => self::SCENARIO_CREATE_USER_EVENT,
                    'message' => 'Введите дату окончания события',
                ],
                [
                    ['date_end'],
                    'compare',
                    'compareAttribute' => 'date_start',
                    'operator' => '>=',
                    'on' => self::SCENARIO_CREATE_USER_EVENT,
                    'message' => 'Дата окончания не может быть раньше даты начала',
                ],
            ];
    }

    public function commonDateValidator()
    {
        $date = $this->date_start;

        $hasEvent = Calendar::find()->isCommonEvent()->notDeleted()->whereDate($date)->exists();
        if ($hasEvent) {
            $this->addError('date_start', 'Эта дата уже переопределена ранее!');
        }
    }

    public function userDateValidator()
    {
        $dateStart = $this->date_start;
        $dateEnd = $this->date_end;
        $userId = $this->user_id;
        $hasEvents = Calendar::find()
            ->notDeleted()
            ->whereUserId($userId)
            ->whereIntersectPeriod($dateStart, $dateEnd)
            ->exists();

        if ($hasEvents) {
            $this->addError('date_start', 'Этот период дат уже переопределен!');
            $this->addError('date_end', 'Этот период дат уже переопределен!');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels(): array
    {
        return
            [
                'id' => 'ID',
                'date_start' => 'Дата',
                'date_end' => 'Дата окончания',
                'type' => 'Тип дня недели',
                'created_at' => 'Создана',
                'updated_at' => 'Изменена',
                'deleted_at' => 'Deleted At',
                'created_by_id' => 'Created By ID',
                'updated_by_id' => 'Updated By ID',
            ];
    }

    /**
     * Gets query for [[User]].
     *
     * @return ActiveQuery|UserQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }

    /**
     * Gets query for [[CreatedBy]].
     *
     * @return ActiveQuery|UserQuery
     */
    public function getCreatedBy()
    {
        return $this->hasOne(User::class, ['id' => 'created_by_id']);
    }

    /**
     * Gets query for [[UpdatedBy]].
     *
     * @return ActiveQuery|UserQuery
     */
    public function getUpdatedBy()
    {
        return $this->hasOne(User::class, ['id' => 'updated_by_id']);
    }

    /**
     * {@inheritdoc}
     * @return CalendarQuery the active query used by this AR class.
     */
    public static function find(): CalendarQuery
    {
        return new CalendarQuery(static::class);
    }
}
