<?php

declare(strict_types=1);

namespace App\Tenant\Application\Dto;

final readonly class UpdateStoreAppearanceInput
{
    /**
     * @param list<string> $sections
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
    ) {
    }
}
