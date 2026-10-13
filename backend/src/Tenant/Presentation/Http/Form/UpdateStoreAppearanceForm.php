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
        if (($heroIndex = array_search('hero', $enabled, true)) !== false) {
            array_splice($enabled, $heroIndex, 1);
            array_unshift($enabled, 'hero');
        }

        return new UpdateStoreAppearanceInput(
            $ownerEmail,
            (string) $request->request->get('template', 'classic'),
            (string) $request->request->get('primary_color', '#0077b6'),
            (string) $request->request->get('hero_title', ''),
            (string) $request->request->get('hero_text', ''),
            $enabled,
            $this->emptyToNull($request->request->getString('logo_image_id')),
            $this->emptyToNull($request->request->getString('hero_image_id')),
            $request->request->getBoolean('show_store_name_with_logo'),
            (string) $request->request->get('hero_eyebrow', ''),
            (string) $request->request->get('hero_button_label', 'Смотреть товары'),
            (string) $request->request->get('hero_alignment', 'left'),
            $request->request->getInt('hero_overlay', 60),
            (string) $request->request->get('featured_title', 'Популярное'),
            $request->request->getInt('featured_columns', 4),
            $request->request->getBoolean('featured_show_description'),
            (string) $request->request->get('seo_title', ''),
            (string) $request->request->get('seo_description', ''),
            array_values(array_filter(
                $request->request->all('hero_slides'),
                static fn (mixed $slide): bool => is_array($slide),
            )),
            $request->request->getBoolean('hero_autoplay'),
            $request->request->getInt('hero_interval', 5),
            (string) $request->request->get('featured_layout', 'grid'),
            $request->request->getInt('featured_limit', 6),
            (string) $request->request->get('featured_source', 'latest'),
            (string) $request->request->get('featured_category_slug', ''),
            array_values(array_map('strval', $request->request->all('featured_product_ids'))),
        );
    }

    private function emptyToNull(string $value): ?string
    {
        $value = trim($value);

        return $value === '' ? null : $value;
    }
}
