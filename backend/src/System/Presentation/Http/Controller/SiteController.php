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

    #[Route('/{page}', name: 'site_static', requirements: ['page' => 'privacy'], methods: ['GET'])]
    public function staticPage(string $page): Response
    {
        return new Response($this->twig->render('site/static.html.twig', [
            'page' => $this->staticPages()[$page],
        ]));
    }

    /**
     * @return array<string, array{slug: string, title: string, description: string, paragraphs: list<string>}>
     */
    private function staticPages(): array
    {
        $operator = 'Оператор сайта: физическое лицо Смирнов Александр Викторович. Контакты: телефон 8 929 203-04-99, email ruphp@mail.ru.';

        return [
            'privacy' => [
                'slug' => 'privacy',
                'title' => 'Политика обработки персональных данных',
                'description' => 'Как ShopsBox обрабатывает персональные данные, cookie и данные веб-аналитики.',
                'paragraphs' => [
                    $operator,
                    'Политика подготовлена для закрытой мини-беты ShopsBox и описывает обработку данных пользователей сайта shopsbox.ru. Документ является рабочей редакцией и должен быть юридически проверен перед публичной регистрацией клиентов и приемом оплат.',
                    'Сайт может обрабатывать данные, которые пользователь сообщает сам: имя, телефон, email, текст обращения, сведения о магазине и другую информацию из форм обратной связи или регистрации.',
                    'Сайт также может автоматически получать технические данные: IP-адрес, cookie, сведения о браузере и устройстве, источник перехода, просмотренные страницы и действия на сайте.',
                    'Для аналитики используется Яндекс.Метрика. Сервис может собирать обезличенные технические данные и cookie для статистики посещений, улучшения сайта и проверки эффективности страниц.',
                    'Продолжая пользоваться сайтом и нажимая кнопку согласия в cookie-уведомлении, пользователь соглашается на использование cookie и обработку технических данных в указанных целях.',
                    'Пользователь может отключить cookie в настройках браузера. В этом случае отдельные функции сайта или аналитика могут работать некорректно.',
                    'Запросы по персональным данным, уточнение данных или отзыв согласия можно направить на email ruphp@mail.ru.',
                    'Данные не продаются третьим лицам. Передача возможна только техническим подрядчикам, сервисам аналитики, хостинга и в случаях, предусмотренных законом.',
                ],
            ],
        ];
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
</urlset>
XML;

        return new Response($xml, Response::HTTP_OK, ['Content-Type' => 'application/xml; charset=UTF-8']);
    }
}
