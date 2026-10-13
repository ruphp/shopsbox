<?php

declare(strict_types=1);

namespace App\Tenant\Application\Dto;

final readonly class UpdateStoreAppearanceInput
{
    /**
     * @param list<string> $sections
     * @param list<array<string, mixed>> $heroSlides
     * @param array<string, string> $categoryImageIds
     * @param list<array<string, mixed>> $benefits
     */
    public function __construct(
        public string $ownerEmail,
        public string $template,
        public string $primaryColor,
        public string $heroTitle,
        public string $heroText,
        public array $sections,
        public ?string $logoImageId = null,
        public ?string $heroImageId = null,
        public bool $showStoreNameWithLogo = false,
        public string $heroEyebrow = '',
        public string $heroButtonLabel = 'Смотреть товары',
        public string $heroAlignment = 'left',
        public int $heroOverlay = 60,
        public string $featuredTitle = 'Популярное',
        public int $featuredColumns = 4,
        public bool $featuredShowDescription = true,
        public string $seoTitle = '',
        public string $seoDescription = '',
        public array $heroSlides = [],
        public bool $heroAutoplay = true,
        public int $heroInterval = 5,
        public string $featuredLayout = 'grid',
        public int $featuredLimit = 6,
        public string $featuredSource = 'latest',
        public string $featuredCategorySlug = '',
        public array $featuredProductIds = [],
        public array $categoryImageIds = [],
        public string $benefitsTitle = 'Почему выбирают нас',
        public array $benefits = [],
        public string $contentTitle = 'О магазине',
        public string $contentText = '',
        public ?string $contentImageId = null,
        public string $contentImagePosition = 'left',
        public string $contentButtonLabel = '',
        public string $contentButtonUrl = '',
    ) {
    }
}
