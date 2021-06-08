# Slack Bot для Jira

Сервис для автоматической проверки залогированного времени в Jira и уведомления сотрудников через мессенджер Slack о
необходимости залогировать недостающие часы.

## Разворачивание проекта локально

1. Скачать/клонировать папку проекта из удаленного репозитория
2. В директории `vagrant/config` найти файл `vagrant-local.example.yml`, скопировать его в ту же папку и переименовать
   в `vagrant-local.yml`.
3. В файле `vagrant-local.yml` прописать GitHub-token. Как получить токен в GitHub можно узнать здесь -
   https://docs.github.com/en/github/authenticating-to-github/creating-a-personal-access-token
4. В командной строке (терминале) перейти в папку проекта и выполнить команду `vagrant up`. После нескольких минут
   ожидания проект будет запущен.
5. После запуска проекта в командной строке выполнить команду `vagrant ssh`.
6. Далее выполнить команду `cd /app` для перехода в папку проекта на виртуальной машине.

## Применение миграций для создания таблиц базы данных

Для создания таблиц в базе данных в терминале выполнить команду `php yii migrate`. Будут созданы все необходимые таблицы
БД.

## Создание аккаунта администратора при помощи консольной команды

Для создания аккаунта администратора приложения в терминале выполнить команду `php yii admin/create`. Система предложит
ввести имя пользователя, адрес электронной почты и пароль. После успешного создания аккаунта можно войти в панель
администратора по адресу - `http://admin.brain-crm.test`

## Системные настройки

Для работы сервиса нужны следующие данные:

* URL-адрес в сервисе Jira;
* Логин администратора для входа в Jira;
* Пароль администратора для входа в Jira;
* Токен доступа в Slack;

Все эти данные необходимо ввести в админ-панели в разделе **_Настройки -> Системные настройки_**

## Получение токена в Slack

Для получения токена нужно создать приложение на сайте https://api.slack.com/apps и перейти в раздел настроек
приложения.

* В левом меню выбрать пункт **_OAuth & Permissions_**;
* Выбрать необходимые разрешения для бота:

      - chat:write - Отправка сообщений,
      - users:read - Просмотр пользователей в рабочей области, 
      - users:read.email - Просмотр адресов электронной почты пользователей в рабочей области

* При необходимости (после установки разрешений) переустановить приложение в Workspace (рабочей области);
* Скопировать токен.

## Данные пользователей

Для работы сервиса необходимо собрать данные всех сотрудников. Потребуются:

* ФИО сотрудника;
* Email;
* Пароль;
* Направление (отдел);
* Логин сотрудника в Jira;
* Электронная почта в Slack;
* Тип сотрудничества (штатный/внештатный сотрудник);
* Для внештатного сотрудника нужно внести недельную нагрузку (в часах). Для штатного она равна 40 час./нед.
* Руководитель (необязательно для заполнения)

## Настройка автоматического сбора данных о залогированном времени в сервисе cron

В сервисе cron настроены две автоматические задачи:

* **Ежедневная проверка** - запускается каждый день в 9:30 AM, при условии, что накануне был рабочий день, проверяет
  worklogs и, если необходимо, посредством мессенджера Slack оповещает сотрудников и их руководителей (если они есть)
  о количестве незалогированных часов в Jira за прошедший день.
* **Еженедельная проверка** - запускается каждый понедельник в 9:30 AM, вне зависимости от того, рабочим или нет был
  день накануне. Проверяет worklogs за предыдущую неделю и, если необходимо, посредством мессенджера Slack оповещает
  сотрудников и их руководителей (если они есть)
  о количестве незалогированных часов в Jira за прошедшую неделю.

Для активации автоматических проверок, находясь в папке проекта на виртуальной машине, следует запустить
команду `php yii cron/init`.

Для удаления всех заданий cron, находясь в папке проекта на виртуальной машине, следует запустить
команду `php yii cron/remove-all`.
