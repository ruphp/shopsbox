# SMS provider: SigmaSMS

MVP сейчас не подключает реальный SMS-провайдер: коды подтверждения в dev-режиме остаются через внутренний contract `AuthCodeDelivery`.

Когда переходим к реальным SMS, первый кандидат - SigmaSMS REST API:

- endpoint: `https://online.sigmasms.ru/api/sendings`;
- авторизация: header `Authorization: <token>`;
- формат отправки SMS:

```json
{
  "type": "sms",
  "recipient": "+79998887766",
  "payload": {
    "sender": "ShopsBox",
    "text": "Код подтверждения ShopsBox: 123456"
  }
}
```

Архитектурно подключать через реализацию `AuthCodeDelivery` в `Tenant/Infrastructure/Adapters`, а не напрямую из controller/use case.

Токен SigmaSMS не хранить в репозитории. Нужен env-параметр `SIGMASMS_API_TOKEN`.

На dev/prod окружениях токен задается только через ignored env-файлы или переменные окружения.

Перед production-подключением:

- решить sender name;
- проверить лимиты и стоимость;
- добавить логирование внешнего message id;
- добавить обработку ошибок API;
- проверить согласие пользователя на получение кода.
