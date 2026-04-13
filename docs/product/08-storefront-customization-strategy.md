# Стратегия настройки витрины

Документ фиксирует, как ShopsBox должен давать клиентам менять фронт магазина не хуже похожих платформ, но без превращения MVP в бесконечный visual builder.

## Как делают конкуренты

### Shopify

Shopify дает несколько уровней гибкости:

- theme editor внутри админки;
- настройки темы: цвета, типографика, layout;
- sections и blocks, которые можно добавлять, удалять и переупорядочивать;
- preview, mobile preview, undo/redo и save;
- app embeds и app blocks;
- code editor для Liquid/HTML/CSS/JSON/JavaScript;
- предупреждение, что править код стоит только когда не хватает theme editor или app, потому что изменения могут конфликтовать с обновлениями темы.

Вывод для ShopsBox: лучший базовый путь - не редактирование кода, а управляемые sections/blocks/settings. Кодовый режим нужен позже и только как developer/custom-уровень.

Источники:

- https://help.shopify.com/en/manual/online-store/themes/customizing-themes/theme-editor/features-overview
- https://shopify.dev/docs/storefronts/themes/architecture/sections
- https://shopify.dev/docs/storefronts/themes/architecture/blocks
- https://help.shopify.com/en/manual/online-store/themes/customizing-themes/edit-code/edit-theme-code

### Wix

Wix делает ставку на визуальное редактирование. Для product page в Wix Studio можно менять глобальные настройки, выбирать layout, скрывать/показывать элементы, подключать plugins, настраивать отдельные элементы и мобильные breakpoints. При этом product page является dynamic page: настройка применяется ко всем товарам.

Для разработчиков есть Velo: frontend code, backend code, web modules и permissions. Backend-функции можно вызывать с frontend через web methods, но нужно ограничивать права.

Вывод для ShopsBox: визуальный редактор мощный, но дорогой в разработке. На MVP стоит взять идею dynamic templates и element visibility, а не пытаться повторить весь Wix.

Источники:

- https://support.wix.com/en/article/wix-stores-customizing-the-new-product-page
- https://dev.wix.com/docs/develop-websites/articles/coding-with-velo/backend-code/web-modules/about-web-modules

### Squarespace

Squarespace дает Style Editor: разработчик заранее описывает tweak-настройки, а пользователь меняет presentation без знания CSS. Типы настроек: value/slider, color, typography, checkbox, dropdown, image. Есть и Custom CSS, но Squarespace отдельно предупреждает, что custom code/CSS выходит за рамки поддержки и может ломать совместимость с будущими обновлениями.

Вывод для ShopsBox: хорошая модель для нас - заранее разрешенные style tokens и controls. Custom CSS можно дать позже как advanced-режим без обычной поддержки.

Источники:

- https://developers.squarespace.com/style-editor
- https://support.squarespace.com/hc/en-us/articles/206545567-Using-the-CSS-Editor

### WooCommerce

WooCommerce в современной модели опирается на WordPress Site Editor, block themes, templates, template parts, patterns и WooCommerce blocks. Это дает no-code настройку магазина, product pages, cart и checkout через блоки и шаблоны. Для classic/custom тем остаются template overrides, но такие изменения требуют разработчика и часто выходят за рамки обычной поддержки.

Вывод для ShopsBox: blocks/templates/patterns - сильный ориентир, но нужно сделать проще: фиксированный набор storefront blocks и шаблонов, а не весь WordPress-уровень гибкости.

Источники:

- https://woocommerce.com/documentation/woocommerce/store-design/block-themes-store-editing/
- https://woocommerce.com/document/woocommerce-store-editing/
- https://developer.woocommerce.com/docs/user-experience-guidelines/ux-guidelines-themes/theme-customization/

### BigCommerce

BigCommerce использует Stencil themes. У темы есть Page Builder / theme styles / config, а разработчики могут создавать custom templates для brand/category/product/page, тестировать их через Stencil CLI, загружать тему и назначать templates через control panel. Настройки theme styles сохраняются отдельно от кода темы.

Вывод для ShopsBox: для продвинутого режима нужен theme package и dev workflow: локальная разработка, сборка, загрузка/публикация, назначение шаблонов. Но для MVP достаточно theme settings + blocks + template selection.

Источники:

- https://developer.bigcommerce.com/docs/storefront/stencil/themes/foundations/customizability
- https://developer.bigcommerce.com/docs/storefront/stencil/themes/templates

## Уровни гибкости ShopsBox

Уровни гибкости должны быть связаны с тарифами. Иначе клиент на базовом тарифе сможет включить сложный кастом, а ShopsBox получит дорогую поддержку без оплаты.

Предварительная логика:

- Start: brand settings и базовые блоки.
- Business: больше блоков, меню, статические страницы, простая публикация.
- Managed Plus: preview, draft/published, больше тем и template selection.
- Dedicated Managed: расширенная настройка, performance review, отдельный регламент.
- Own Frontend: клиентский фронт поверх API, поддерживаем API contract, а не каждый пиксель.
- Custom / Enterprise: custom CSS, theme package, разработка индивидуальных шаблонов, SLA и отдельный договор.

Правило:

> Чем больше свободы в дизайне и коде, тем выше тариф или тем явнее отдельная проектная работа.

### Уровень 1: Brand settings

MVP. Должно входить в Start.

Клиент может менять:

- логотип;
- основные цвета;
- контакты;
- соцсети позже;
- favicon позже;
- SEO title/description;
- телефоны/email/адрес.

Как хранить:

- `storefront_settings`;
- файлы через `files`;
- публикация через `published_at`.

Плюс: безопасно, быстро, легко поддерживать.

### Уровень 2: Storefront blocks

MVP или сразу после MVP. Базовые блоки могут входить в Start, расширенный набор - в Business.

Клиент может менять главную страницу через заранее заданные блоки:

- hero;
- categories;
- featured products;
- text section;
- image + text;
- contacts;
- delivery/payment info;
- FAQ позже.

Можно:

- включить/выключить блок;
- поменять порядок;
- поменять текст;
- выбрать изображение;
- выбрать товары/категории;
- опубликовать изменения.

Как хранить:

- `storefront_blocks.type`;
- `storefront_blocks.payload`;
- `storefront_blocks.sort_order`;
- `storefront_blocks.is_active`.

Плюс: похоже на Shopify sections/blocks и WooCommerce patterns, но контролируемо.

### Уровень 3: Template selection

После базового MVP. Лучше привязать к Managed Plus или Business+, если появится такой тариф.

Клиент может выбрать шаблон для:

- главной;
- категории;
- карточки товара;
- корзины;
- checkout;
- статической страницы.

Варианты:

- `default`;
- `minimal`;
- `promo`;
- `catalog-heavy`;
- `single-product`;
- позже custom template.

Как хранить:

- отдельная таблица `storefront_templates` позже;
- или поле в settings/payload на раннем этапе.

### Уровень 4: Theme tokens

После MVP.

Клиент может менять дизайн через токены, похожие на style editor:

- primary color;
- secondary color;
- text color;
- background color;
- button radius;
- heading font;
- body font;
- product card style;
- grid density.

Важно: это должны быть разрешенные поля, а не произвольный CSS.

Коммерчески это хороший уровень для Managed Plus: клиент получает гибкость, но ShopsBox сохраняет контроль и поддержку обновлений.

### Уровень 5: Custom CSS

Advanced, не для базового тарифа.

Можно дать поле custom CSS только с предупреждением:

- не входит в базовую поддержку;
- может сломать адаптивность;
- может конфликтовать с обновлениями темы;
- требует проверки перед публикацией;
- лучше включать только для `Managed Plus`, `Dedicated Managed`, `Own Frontend` или `Custom`.

Для MVP не делать.

Коммерчески: только Managed Plus с ограниченной поддержкой, Dedicated Managed, Own Frontend или Custom. Если custom CSS ломает верстку, исправление не должно входить в базовую поддержку.

### Уровень 6: Theme package / developer mode

Позже.

Для разработчиков и кастомных клиентов:

- тема как пакет;
- локальная разработка;
- сборка;
- preview;
- upload/publish;
- versioning;
- rollback;
- назначение шаблонов страниц.

Это аналог BigCommerce Stencil и Shopify theme code workflow, но его нельзя делать первым.

Коммерчески: только Custom / Enterprise или paid developer add-on. Такой режим требует версионирования, preview, rollback и понятного договора поддержки.

### Уровень 7: Own Frontend / Headless

Для клиентов, которым нужен полный контроль.

Клиент делает свой фронт и подключается к Storefront API ShopsBox или к своему backend/agent в Own Infra.

ShopsBox отвечает за:

- API contract;
- админку/control plane;
- agent/API;
- документацию.

ShopsBox не отвечает за каждый пиксель кастомного фронта без отдельного договора.

Коммерчески: отдельный тариф Own Frontend/Own Infra или проектная работа. Клиент платит за API, документацию, интеграцию и поддержку контракта.

## Что сделать в первом MVP

Минимум “не хуже по смыслу”, но без тяжелого конструктора:

1. `storefront_settings`: логотип, цвета, контакты, SEO.
2. `storefront_blocks`: блоки главной с порядком и включением.
3. Публикация изменений: draft -> published позже, на MVP можно сразу сохранять и публиковать.
4. Preview на поддомене demo/pilot.
5. Storefront API для чтения settings/blocks.
6. Запрет произвольного CSS/JS в базовом режиме.

Такой MVP не будет мощнее Wix, но будет честно сопоставим по основной ценности: владелец может поменять внешний вид и структуру главной без разработчика, а мы не берем риск поддержки произвольного кода.

Тарифно:

- Start: логотип, цвета, контакты, базовые блоки.
- Business: больше блоков, меню, статические страницы, SEO.
- Managed Plus: preview/draft/published и расширенные темы позже.
- Custom/Own Frontend: все, что требует кода.

## Что добавить после MVP

1. Preview перед публикацией.
2. Draft/published versions.
3. Набор тем.
4. Template selection для page/product/category/checkout.
5. Theme tokens.
6. Custom CSS как advanced без базовой поддержки.
7. Theme package для разработчиков.
8. Own Frontend/headless mode.

## Граница поддержки

Поддерживается:

- настройки бренда;
- штатные блоки;
- штатные шаблоны;
- ошибки ShopsBox theme renderer;
- Storefront API contract.

Не поддерживается в базовом тарифе:

- произвольный CSS/JS;
- кастомный frontend клиента;
- ручные правки файлов темы;
- сторонние скрипты, которые ломают checkout;
- изменения, сделанные вне ShopsBox без согласованного deployment-механизма.

## Простое объяснение клиенту

В базовом режиме вы можете менять логотип, цвета, тексты, блоки главной, меню и SEO без разработчика. Если нужен полностью индивидуальный дизайн, можно перейти на кастомную тему, Own Frontend или отдельный проект. Так магазин остается управляемым и обновляемым, а кастом не ломает поддержку.
