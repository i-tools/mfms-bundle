Бандл для отправки sms-сообщений через шлюз https://mfms.ru/
------------------------------------------------------------

Реализует сервис по отправке смс сообщений через [API mfms.ru](https://mfms.ru/)


Установка
---------

Установка производиться с помощь `composer` и следует стандартной структуре, для работы требуется `symfony => 4.2`

1. Добавить репозиторий пакета в `composer.json`:

```json
    "repositories": [
        {
            "type": "git",
            "url": "https://github.com/i-tools/mfms-bundle.git"
        }
    ]
```

2. Добавить пакет бандла в проект:

```
    composer require itools/mfms-bundle

```

3. Настроить доступ к API:

```yaml
    # app/config/packages/mfmsgateway.yaml
    mfmsgateway:
        url: 'gateway url'
        login: 'login'
        password: 'password'
```

Добавление сообщения в очередь
------------------------------

Осуществляется с помощью функции addMessage класса MFMSGateway

```
$MFMSGateway->addMessage(
    '7878787878'
    'Заголовок'
    'Тело сообщения'
);
```

Отправка сообщения
------------------

Отправка сообщения осуществляется с помощью функции sendMessages() класса MFMSGateway

```
$smsResult = $MFMSGateway->sendMessages();
```

В ответ приходит массив с кодом и сообщением ошибки:

```
[
    "code": XXX,
    "msg": "Описание ошибки"
]
```

Возможные коды ошибок
---------------------

200 - Запрос успешно обработан

401 - Указаны неверные логин\пароль

403 - Отправка на указанный адрес запрещена или отправитель запрещен на платформе

422 - Ошибка входных параметров

505 - Системная или неизвестная ошибка



Пример использования:
---------------------

```
use itools\MFMSGatewayBundle\MFMSGateway;

.......

public function serviceSendSMS(Request $request, MFMSGateway $MFMSGateway): JsonResponse
{
    $MFMSGateway->addMessage(
        $request->get('phone'),
        $request->get('subject'),
        $request->get('msg')
    );

    $smsResult = $MFMSGateway->sendMessages();

    $response = new JsonResponse(
        [
            'success' => true,
            'code' => $smsResult->getStatusCode(),
            'message' => 'SMS Send'
        ],
        JsonResponse::HTTP_OK
    );
    $response->headers->set('Content-Type', 'application/json');
    $response->headers->set('Access-Control-Allow-Origin', '*');
}   
```