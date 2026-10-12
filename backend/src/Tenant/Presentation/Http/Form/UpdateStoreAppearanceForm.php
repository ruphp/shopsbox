<?php

declare(strict_types=1);

namespace App\Tenant\Presentation\Http\Form;

use App\Tenant\Application\Dto\UpdateStoreAppearanceInput;
use Symfony\Component\HttpFoundation\Request;

final readonly class UpdateStoreAppearanceForm
{
    public function fromRequest(Request $request, string $ownerEmail): UpdateStoreAppearanceInput
    {
        $enabled = array_values(array_map('strval', $request->request->all('enabled_sections')));
        $positions = $request->request->all('section_positions');
        usort($enabled, static function (string $left, string $right) use ($positions): int {
            return ((int) ($positions[$left] ?? 99)) <=> ((int) ($positions[$right] ?? 99));
        });

        return new UpdateStoreAppearanceInput(
            $ownerEmail,
            (string) $request->request->get('template', 'classic'),
            (string) $request->request->get('primary_color', '#0077b6'),
            (string) $request->request->get('hero_title', ''),
            (string) $request->request->get('hero_text', ''),
            $enabled,
        );
    }
}
