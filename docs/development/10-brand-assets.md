# Brand assets

Документ фиксирует текущие брендовые assets ShopsBox для web, iOS и Android.

## Логотип

Основной визуальный концепт:

```text
backend/public/assets/brand/shopsbox-logo-concept.png
```

Версия для шапки:

```text
backend/public/assets/brand/shopsbox-logo-header.png
```

Шапка использует именно header-версию, потому что исходный концепт слишком широкий и содержит много фона.

## Иконка приложения и favicon

Текущая основная иконка - простой знак:

- желтый квадрат цвета коробки;
- острые углы;
- оранжевая окантовка;
- белая буква `S` с тонкой оранжевой обводкой;
- прозрачное поле вокруг квадрата, чтобы острые углы читались в системных масках.

Файлы:

```text
backend/public/assets/brand/shopsbox-icon-square.svg
backend/public/assets/brand/shopsbox-icon-square-16.png
backend/public/assets/brand/shopsbox-icon-square-32.png
backend/public/assets/brand/shopsbox-icon-square-48.png
backend/public/assets/brand/shopsbox-icon-square-180.png
backend/public/assets/brand/shopsbox-icon-square-192.png
backend/public/assets/brand/shopsbox-icon-square-256.png
backend/public/assets/brand/shopsbox-icon-square-512.png
backend/public/assets/brand/shopsbox-icon-square.png
backend/public/assets/brand/favicon-square.ico
backend/public/assets/brand/apple-touch-icon.png
```

Старые варианты favicon/icon оставлены рядом как черновики, но в `<head>` подключается square-версия.

## Web manifest

Для Android/PWA используется:

```text
backend/public/site.webmanifest
```

Manifest подключает `192x192` и `512x512` PNG-иконки, включая `maskable`-вариант.

## Проверка

Static assets проверяются через:

```text
make static-assets-check
```

Эта проверка нужна, потому что локальный Symfony-контур работает через PHP built-in server. Static router должен отдавать изображения как файлы, а не отправлять их в `public/index.php`.
