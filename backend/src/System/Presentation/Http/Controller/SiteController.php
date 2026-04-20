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

    #[Route('/{page}', name: 'site_static', requirements: ['page' => 'privacy|terms|cookies|payments|contacts'], methods: ['GET'])]
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
        $operator = 'Оператор сайта: Смирнов Александр Викторович, ИНН 590612372849, самозанятый. Контакты: телефон 8 929 203-04-99, email ruphp@mail.ru.';

        return [
            'privacy' => [
                'slug' => 'privacy',
                'title' => 'Политика конфиденциальности',
                'description' => 'Сбор, обработка и хранение персональных данных пользователей ShopsBox.',
                'paragraphs' => [
                    'На сайте shopsbox.ru (далее - Сайт) осуществляется сбор и обработка персональных данных пользователей. При регистрации и использовании сервисов Сайта вы предоставляете следующие данные: ФИО, адрес электронной почты, номер телефона.',
                    'Эти данные используются для создания и управления интернет-магазином, а также для предоставления технической поддержки.',
                    'Мы не передаем персональные данные третьим лицам, за исключением случаев, предусмотренных законодательством РФ.',
                    'Вы можете в любой момент запросить удаление или изменение своих данных, обратившись в службу поддержки.',
                    'Для аналитики посещаемости используется сервис Яндекс.Метрика. При этом собираются обезличенные данные о поведении пользователей на сайте.',
                ],
            ],
            'terms' => [
                'slug' => 'terms',
                'title' => 'Пользовательское соглашение',
                'description' => 'Правила использования платформы ShopsBox, ответственность сторон и ограничения.',
                'paragraphs' => [
                    'Используя shopsbox.ru, вы соглашаетесь с настоящими условиями. Платформа предоставляет инструменты для создания интернет-магазинов на поддоменах.',
                    'Вы несете полную ответственность за размещенный контент, включая товары, описания и изображения.',
                    'Запрещено размещать материалы, нарушающие законодательство РФ, авторские права, а также контент, который может быть признан незаконным, вредоносным или оскорбительным.',
                    'shopsbox.ru оставляет за собой право блокировать витрины и аккаунты без предварительного уведомления в случае выявления нарушений. Возврат средств при блокировке не производится.',
                    'Факт регистрации, принятия условий и ключевые обращения пользователей могут фиксироваться в технических журналах сервиса для подтверждения добросовестности сторон в случае споров.',
                ],
            ],
            'cookies' => [
                'slug' => 'cookies',
                'title' => 'Политика использования файлов cookie',
                'description' => 'Как ShopsBox использует cookie для работы сайта и аналитики.',
                'paragraphs' => [
                    'На shopsbox.ru используются файлы cookie для обеспечения корректной работы платформы, хранения настроек и анализа посещаемости. Cookie не содержат персональных данных.',
                    'Продолжая использовать сайт, пользователь соглашается с использованием cookie.',
                    'Пользователь может отключить cookie в настройках браузера, однако это может повлиять на функциональность сервиса.',
                ],
            ],
            'payments' => [
                'slug' => 'payments',
                'title' => 'Политика платежей и возврата средств',
                'description' => 'Порядок оплаты услуг ShopsBox и условия возврата средств.',
                'paragraphs' => [
                    'Доступ к расширенному функционалу shopsbox.ru может предоставляться по ежемесячной или годовой подписке, а также по лицензии на коробочную версию.',
                    'Оплата производится через защищенные платежные системы, включая подключаемые платежные решения для российских пользователей.',
                    'Возврат средств возможен только в случае технических сбоев со стороны платформы или при двойном списании.',
                    'В остальных случаях возврат не предусмотрен, если иное не требуется законодательством РФ.',
                ],
            ],
            'contacts' => [
                'slug' => 'contacts',
                'title' => 'Контактная информация и реквизиты',
                'description' => 'Контакты разработчика платформы ShopsBox для юридических и финансовых вопросов.',
                'paragraphs' => [
                    'Разработчик платформы: Александр Викторович Смирнов.',
                    'ИНН: 590612372849, самозанятый.',
                    'Для связи: email ruphp@mail.ru, телефон 8 929 203-04-99.',
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
    <url>
        <loc>https://shopsbox.ru/terms</loc>
        <priority>0.3</priority>
    </url>
    <url>
        <loc>https://shopsbox.ru/cookies</loc>
        <priority>0.3</priority>
    </url>
    <url>
        <loc>https://shopsbox.ru/payments</loc>
        <priority>0.3</priority>
    </url>
    <url>
        <loc>https://shopsbox.ru/contacts</loc>
        <priority>0.3</priority>
    </url>
</urlset>
XML;

        return new Response($xml, Response::HTTP_OK, ['Content-Type' => 'application/xml; charset=UTF-8']);
    }
}
