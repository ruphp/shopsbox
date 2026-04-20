<?php

declare(strict_types=1);

namespace App\Tenant\Presentation\Http\Form;

use App\Tenant\Application\Dto\UpdateStoreSettingsInput;
use Symfony\Component\HttpFoundation\Request;

final readonly class UpdateStoreSettingsForm
{
    public function fromRequest(Request $request, string $ownerEmail): UpdateStoreSettingsInput
    {
        return new UpdateStoreSettingsInput(
            $ownerEmail,
            (string) $request->request->get('name', ''),
            (string) $request->request->get('public_description', ''),
            (string) $request->request->get('contact_email', ''),
            (string) $request->request->get('contact_phone', ''),
            (string) $request->request->get('contact_city', ''),
            (string) $request->request->get('contact_address', ''),
            (string) $request->request->get('seller_legal_name', ''),
            (string) $request->request->get('seller_inn', ''),
            (string) $request->request->get('seller_legal_text', ''),
            (string) $request->request->get('delivery_text', ''),
            (string) $request->request->get('payment_text', ''),
            (string) $request->request->get('default_currency', ''),
            (string) $request->request->get('timezone', ''),
            [
                'primary_color' => (string) $request->request->get('primary_color', '#0077b6'),
                'accent' => (string) $request->request->get('accent', 'blue'),
                'hero_title' => (string) $request->request->get('hero_title', ''),
                'hero_text' => (string) $request->request->get('hero_text', ''),
                'sections' => (array) $request->request->all('sections'),
            ],
        );
    }
}
