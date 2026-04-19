<?php

declare(strict_types=1);

namespace App\System\Presentation\Http\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Twig\Environment;

final readonly class SiteController
{
    public function __construct(private Environment $twig)
    {
    }

    #[Route('/', name: 'site_home', methods: ['GET'])]
    public function home(): Response
    {
        return new Response($this->twig->render('site/home.html.twig'));
    }

    #[Route('/{page}', name: 'site_static', requirements: ['page' => 'privacy|terms|offer|contacts|status'], methods: ['GET'])]
    public function staticPage(string $page): Response
    {
        $pages = [
            'privacy' => [
                'slug' => 'privacy',
                'title' => 'Политика конфиденциальности',
                'description' => 'Как ShopsBox планирует работать с персональными данными пользователей и владельцев магазинов.',
                'paragraphs' => [
                    'Этот раздел является черновиком для MVP и будет уточняться перед публичным запуском сервиса.',
                    'ShopsBox должен собирать только те данные, которые нужны для работы магазина, авторизации, поддержки, бэкапов и технической безопасности.',
                    'Перед приемом реальных клиентов документ нужно заменить полноценной юридической редакцией с реквизитами оператора, сроками хранения данных и порядком обращений.',
                ],
            ],
            'terms' => [
                'slug' => 'terms',
                'title' => 'Пользовательское соглашение',
                'description' => 'Базовые условия использования ShopsBox для владельцев магазинов и сотрудников.',
                'paragraphs' => [
                    'Этот раздел является черновиком для MVP и нужен, чтобы на сайте уже была понятная точка для будущих условий сервиса.',
                    'Соглашение должно описывать доступ к админке, роли пользователей, ограничения ответственности, правила использования конструктора и технического сопровождения.',
                    'Перед коммерческим запуском условия нужно согласовать с юристом и связать с тарифами, SLA и моделью размещения магазина.',
                ],
            ],
            'offer' => [
                'slug' => 'offer',
                'title' => 'Публичная оферта',
                'description' => 'Черновой раздел для будущих коммерческих условий запуска интернет-магазина на ShopsBox.',
                'paragraphs' => [
                    'Оферта пока не является финальным договором. На MVP здесь фиксируется место для будущих правил оплаты, тарифов, сопровождения и дополнительных работ.',
                    'В оферте нужно будет отдельно описать запуск магазина, подключение домена, SSL, бэкапы, перенос старого магазина, поддержку и границы кастомизации.',
                    'До публичных продаж этот раздел должен быть заменен юридически проверенным текстом.',
                ],
            ],
            'contacts' => [
                'slug' => 'contacts',
                'title' => 'Контакты',
                'description' => 'Как связаться с ShopsBox по запуску интернет-магазина, переносу и техническому сопровождению.',
                'paragraphs' => [
                    'Контактные каналы будут добавлены перед публичным запуском.',
                    'Для MVP этот раздел фиксирует место для email, формы заявки, реквизитов и ссылок на поддержку.',
                    'Отдельно нужно будет добавить сценарий заявки на перенос старого интернет-магазина.',
                ],
            ],
            'status' => [
                'slug' => 'status',
                'title' => 'Статус сервиса',
                'description' => 'Технический статус ShopsBox, healthchecks, бэкапы и будущие операционные уведомления.',
                'paragraphs' => [
                    'На MVP технический статус проверяется локально через healthcheck и make-команды.',
                    'Перед реальными клиентами здесь должна появиться понятная страница состояния: backend, база данных, хранилище файлов, бэкапы и плановые работы.',
                    'Статус сервиса должен быть частью продукта, а не ручной заметкой в чате.',
                ],
            ],
        ];

        return new Response($this->twig->render('site/static.html.twig', [
            'page' => $pages[$page],
        ]));
    }

    #[Route('/robots.txt', name: 'site_robots', methods: ['GET'])]
    public function robots(): Response
    {
        return new Response(
            "User-agent: *\nAllow: /\nSitemap: https://shopsbox.ru/sitemap.xml\n",
            Response::HTTP_OK,
            ['Content-Type' => 'text/plain; charset=UTF-8'],
        );
    }

    #[Route('/sitemap.xml', name: 'site_sitemap', methods: ['GET'])]
    public function sitemap(): Response
    {
        $xml = <<<'XML'
<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
    <url>
        <loc>https://shopsbox.ru/</loc>
        <priority>1.0</priority>
    </url>
    <url>
        <loc>https://shopsbox.ru/s/demo-store/products</loc>
        <priority>0.7</priority>
    </url>
    <url>
        <loc>https://shopsbox.ru/privacy</loc>
        <priority>0.3</priority>
    </url>
    <url>
        <loc>https://shopsbox.ru/terms</loc>
        <priority>0.3</priority>
    </url>
    <url>
        <loc>https://shopsbox.ru/offer</loc>
        <priority>0.3</priority>
    </url>
    <url>
        <loc>https://shopsbox.ru/contacts</loc>
        <priority>0.4</priority>
    </url>
    <url>
        <loc>https://shopsbox.ru/status</loc>
        <priority>0.3</priority>
    </url>
</urlset>
XML;

        return new Response($xml, Response::HTTP_OK, ['Content-Type' => 'application/xml; charset=UTF-8']);
    }
}
