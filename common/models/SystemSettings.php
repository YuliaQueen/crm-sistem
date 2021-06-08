<?php

namespace common\models;

use common\models\domains\Settings;
use common\models\enums\SystemSettingsName;
use Exception;
use Yii;
use yii\base\Model;
use yii\base\UnknownPropertyException;
use yii\db\Transaction;

/**
 * @property string $jiraUrl
 * @property string $jiraLogin
 * @property string $jiraPassword
 * @property string $slackToken
 */
class SystemSettings extends Model
{

    /**
     * @var Settings[] Ассоциативный массив моделей Settings. В качестве ключей массива используются значения полей name.
     */
    protected array $settings = [];

    public function rules(): array
    {
        return [
            [
                [
                    SystemSettingsName::JIRA_URL,
                    SystemSettingsName::JIRA_LOGIN,
                    SystemSettingsName::JIRA_PASSWORD,
                    SystemSettingsName::SLACK_TOKEN,
                ],
                'required',
            ],
            [[SystemSettingsName::JIRA_URL], 'url'],
            [[SystemSettingsName::JIRA_LOGIN, SystemSettingsName::JIRA_PASSWORD, SystemSettingsName::SLACK_TOKEN], 'string'],
        ];
    }

    public function attributeLabels(): array
    {
        return [
            SystemSettingsName::JIRA_URL => 'URL в Jira',
            SystemSettingsName::JIRA_LOGIN => 'Логин в Jira',
            SystemSettingsName::JIRA_PASSWORD => 'Пароль в Jira',
            SystemSettingsName::SLACK_TOKEN => 'Токен для Slack',
        ];
    }

    /**
     * Получает массив настроек при инициализации объекта класса.
     */
    public function init()
    {
        $this->settings = Settings::find()->notDeleted()->actual()->indexBy('name')->all();
    }

    /**
     * @param string $name
     * @return mixed|string|null
     * @throws UnknownPropertyException
     * @throws Exception
     */
    public function __get($name)
    {
        if (SystemSettingsName::isValidValue($name)) {
            if (isset($this->settings[$name]->value)) {
                return $this->settings[$name]->value;
            } else {
                return '';
            }
        }

        return parent::__get($name);
    }

    /**
     * @param string $name
     * @param mixed $value
     * @throws UnknownPropertyException
     * @throws Exception
     */
    public function __set($name, $value)
    {
        if (SystemSettingsName::isValidValue($name)) {
            if (isset($this->settings[$name])) {
                $this->settings[$name]->value = $value;
            } else {
                $settings = new Settings();
                $settings->name = $name;
                $settings->value = $value;
                $this->settings[$name] = $settings;
            }
        } else {
            parent::__set($name, $value);
        }
    }

    /**
     * @return bool
     */
    public function save()
    {
        if (!$this->validate()) {
            return false;
        }

        /** @var Transaction $transaction */
        $transaction = Yii::$app->db->beginTransaction();
        try {
            foreach ($this->settings as $settingsModel) {
                if ($settingsModel->isNewRecord) {
                    if (!$settingsModel->save(false)) {
                        throw new Exception('Ошибка сохранения модели настроек.', $settingsModel);
                    }
                } elseif ($settingsModel->value != $settingsModel->getOldAttribute('value')) {
                    // Если значение настройки изменилось, создает новую модель настройки.
                    $newSettingsModel = new Settings();
                    $newSettingsModel->name = $settingsModel->name;
                    $newSettingsModel->value = $settingsModel->value;

                    if (!$newSettingsModel->save(false)) {
                        throw new Exception('Ошибка сохранения модели настроек.', $newSettingsModel);
                    }
                }
            }

            $transaction->commit();
            return true;
        } catch (Exception $ex) {
            $transaction->rollBack();
            Yii::error($ex);
            return false;
        }
    }
}
