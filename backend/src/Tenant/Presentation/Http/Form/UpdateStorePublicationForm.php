<?php

declare(strict_types=1);

namespace App\Tenant\Presentation\Http\Form;

use App\Tenant\Application\Dto\UpdateStorePublicationInput;
use Symfony\Component\HttpFoundation\Request;

final readonly class UpdateStorePublicationForm
{
    public function fromRequest(Request $request, string $ownerEmail): UpdateStorePublicationInput
    {
        return new UpdateStorePublicationInput(
            $ownerEmail,
            (string) $request->request->get('owner_name', ''),
            (string) $request->request->get('publication_email', ''),
            (string) $request->request->get('publication_phone', ''),
            (string) $request->request->get('public_subdomain', ''),
            $request->request->getBoolean('terms'),
        );
    }
}
