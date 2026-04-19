# Ручное обновление главной на сервере

Документ фиксирует временную ручную процедуру обновления главной страницы на сервере `155.212.228.12`.

Это не полноценный production deploy. Нужен только как памятка, пока нет нормального pipeline.

## Что важно

- Не править файлы на сервере руками построчно.
- Копировать целые файлы с локальной рабочей копии.
- Не трогать nginx без отдельной задачи.
- Не удалять Twig-cache руками от `root`.
- Symfony cache чистить только штатной командой внутри `webserver_php_fpm` от пользователя `www-data`.
- Если после cache clear страница все еще старая, перезапускать только `webserver_php_fpm`, чтобы сбросить OPcache.

## Файлы главной

Для текущей главной нужен согласованный комплект:

- `backend/templates/base.html.twig`
- `backend/templates/site/home.html.twig`
- `backend/templates/site/_footer.html.twig`

Если поменять только `home.html.twig`, а стили лежат в `base.html.twig`, на сервере может появиться рассинхрон: контент новый, стили старые.

## 1. Сделать бэкап на сервере

```bash
cd /home/webserver/shopsbox

BACKUP_DIR="var/deploy-backups/$(date +%F-%H%M)"
mkdir -p "$BACKUP_DIR"

cp backend/templates/base.html.twig "$BACKUP_DIR/base.html.twig"
cp backend/templates/site/home.html.twig "$BACKUP_DIR/home.html.twig"
cp backend/templates/site/_footer.html.twig "$BACKUP_DIR/_footer.html.twig"
```

## 2. Скопировать файлы с локальной машины

Команды запускать из корня локального проекта `D:\codex\shopsbox`.

```bash
scp backend/templates/base.html.twig root@155.212.228.12:/home/webserver/shopsbox/backend/templates/base.html.twig
scp backend/templates/site/home.html.twig root@155.212.228.12:/home/webserver/shopsbox/backend/templates/site/home.html.twig
scp backend/templates/site/_footer.html.twig root@155.212.228.12:/home/webserver/shopsbox/backend/templates/site/_footer.html.twig
```

## 3. Очистить и прогреть Symfony cache

Команды запускать на сервере.

```bash
docker exec -u www-data webserver_php_fpm sh -lc 'cd /var/www/shopsbox/backend && php bin/console cache:clear --env=prod --no-warmup'
docker exec -u www-data webserver_php_fpm sh -lc 'cd /var/www/shopsbox/backend && php bin/console cache:warmup --env=prod'
```

Важно: не запускать эти команды от `root` внутри контейнера, иначе cache может пересоздаться с правами, которые мешают `www-data`.

## 4. Сбросить OPcache, если отдается старый шаблон

Если после cache clear сайт все еще показывает старый HTML, перезапустить только PHP-FPM контейнер:

```bash
docker restart webserver_php_fpm
```

Nginx не трогать.

## 5. Проверить сайт

```bash
curl -I http://shopsbox.ru/
curl -L -s http://shopsbox.ru/ | grep -E 'Создать|Весь основной функционал|Подключение своего домена'
```

Ожидаемо:

- HTTP status `200 OK`;
- нет `Internal Server Error`;
- новый текст главной виден в HTML;
- стили из `base.html.twig` применяются.

## Быстрый откат

Если после обновления что-то сломалось:

```bash
cd /home/webserver/shopsbox

cp var/deploy-backups/YYYY-MM-DD-HHMM/base.html.twig backend/templates/base.html.twig
cp var/deploy-backups/YYYY-MM-DD-HHMM/home.html.twig backend/templates/site/home.html.twig
cp var/deploy-backups/YYYY-MM-DD-HHMM/_footer.html.twig backend/templates/site/_footer.html.twig

docker exec -u www-data webserver_php_fpm sh -lc 'cd /var/www/shopsbox/backend && php bin/console cache:clear --env=prod --no-warmup'
docker exec -u www-data webserver_php_fpm sh -lc 'cd /var/www/shopsbox/backend && php bin/console cache:warmup --env=prod'
docker restart webserver_php_fpm

curl -I http://shopsbox.ru/
```

Подставить реальную папку бэкапа вместо `YYYY-MM-DD-HHMM`.
