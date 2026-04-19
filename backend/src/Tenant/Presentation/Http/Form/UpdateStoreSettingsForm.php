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
            (string) $request->request->get('default_currency', ''),
            (string) $request->request->get('timezone', ''),
        );
    }
}
