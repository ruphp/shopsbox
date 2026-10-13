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
    ) {
    }
}
