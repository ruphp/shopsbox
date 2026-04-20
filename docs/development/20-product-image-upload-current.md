# Загрузка изображений товара

Issue #49 закрывает текущий MVP-сценарий загрузки изображения товара через `FileStorage`.

## Что уже есть в коде

- `UploadProductImageUseCase` сохраняет файл через `App\FileStorage\Application\Contracts\FileStorage`;
- `UploadProductImageForm` читает файл из HTTP-запроса;
- `AdminProductImageController` принимает POST-загрузку;
- `ProductImage` хранит связь изображения с товаром;
- `DoctrineProductImageRepository` сохраняет metadata изображения;
- витрина берет первое изображение товара и показывает его в списке и карточке.

## Ограничения

Текущие ограничения MVP:

- размер до 5 МБ;
- типы: JPEG, PNG, WebP, GIF;
- файл не может быть пустым;
- товар ищется внутри `store_id`, поэтому загрузка не обходит store boundary;
- публичный URL строится через FileStorage/FileUrlBuilder, а не через прямой путь на диске.

## Связь с медиатекой

Медиатека из `docs/architecture/12-media-library-plan.md` станет следующим слоем над этим сценарием:

- единый список всех файлов магазина;
- usage-связи;
- безопасное удаление;
- подсчет лимитов.

Текущий `ProductImage` можно позже связать с `media_assets`, не ломая уже существующую загрузку через FileStorage.

## Проверки и тестовое покрытие

Новые тесты не добавлены по текущей договоренности для вертикального MVP.

Проверки:

- `lint:container`;
- `doctrine:schema:validate --skip-sync`;
- `php -l` для upload use case/controller/form;
- smoke-check страницы товара на витрине, где изображение уже выводится.

При стабилизации нужны:

- unit-тест валидации mime/size;
- integration-тест сохранения `ProductImage`;
- functional-тест загрузки файла;
- functional-тест запрета загрузки в чужой store.
