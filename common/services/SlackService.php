<?php

namespace common\services;

use common\models\SystemSettings;
use JoliCode\Slack\ClientFactory;
use JoliCode\Slack\Exception\SlackErrorResponse;
use Yii;

class SlackService
{
    private object $client;

    public const SLACK_SERVICE_LOG = 'slack_service';

    public function __construct()
    {
        /** @var SystemSettings $settings */
        $settings = new SystemSettings();
        $this->client = ClientFactory::create($settings->slackToken);
    }

    /**
     * Метод отправляет сообщение в Slack на id пользователя
     * @param $userSlackEmail
     * @param $message
     */
    public function sendMessage($userSlackEmail, $message)
    {
        try {
            // This method requires your token to have the scope "chat:write"
            $userId = $this->getUserSlackId($userSlackEmail);
            if ($userId === null) {
                Yii::error("Id пользователя с email {$userSlackEmail} не найден");
            } else {
                $postMessage = $this->client->chatPostMessage([
                    'username' => 'BrainCRM-bot',
                    'channel' => $userId,
                    'text' => $message,
                ]);

                if ($postMessage !== null) {
                    Yii::info('Сообщение отправлено для ' . $userSlackEmail, self::SLACK_SERVICE_LOG);
                } else {
                    Yii::error('Сообщение не отправлено для ' . $userSlackEmail);
                }
            }
        } catch (SlackErrorResponse $e) {
            Yii::error('Ошибка отправки сообщения. ', $e->getMessage());
        }
    }

    /**
     * Метод получает id пользователя в Slack по адресу электронной почты
     * @param $email - email пользователя Slack
     * @return string|null
     */
    private function getUserSlackId($email): ?string
    {
        try {
            $cache = Yii::$app->cache;
            $key = 'slackId.' . $email;

            if ($cache->exists($key)) {
                return $cache->get($key);
            } else {
                $userId = $this->client->usersLookupByEmail(['email' => $email])->getUser()->getId();
                $cache->set($key, $userId);
                return $userId;
            }
        } catch (SlackErrorResponse $e) {
            Yii::error('Ошибка: ', $e->getMessage());
        }
        return null;
    }
}
