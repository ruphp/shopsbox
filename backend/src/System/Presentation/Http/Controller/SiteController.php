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
        <loc>https://shopsbox.ru/s/demo-store</loc>
        <priority>0.7</priority>
    </url>
</urlset>
XML;

        return new Response($xml, Response::HTTP_OK, ['Content-Type' => 'application/xml; charset=UTF-8']);
    }
}
