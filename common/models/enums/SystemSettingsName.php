<?php

namespace common\models\enums;

use yii2mod\enum\helpers\BaseEnum;

/**
 * Названия атрибутов системных настроек.
 */
class SystemSettingsName extends BaseEnum
{
    /** @var string url адрес в Jira */
    public const JIRA_URL = 'jiraUrl';

    /** @var string логин входа в Jira */
    public const JIRA_LOGIN = 'jiraLogin';

    /** @var string пароль для входа в Jira */
    public const JIRA_PASSWORD = 'jiraPassword';

    /** @var string токен доступа в Slack */
    public const SLACK_TOKEN = 'slackToken';

    /**
     * @var array
     */
    protected static $list = [
        self::JIRA_URL => 'Url в Jira',
        self::JIRA_LOGIN => 'Логин в Jira',
        self::JIRA_PASSWORD => 'Пароль в Jira',
        self::SLACK_TOKEN => 'Токен в Slack',
    ];
}
