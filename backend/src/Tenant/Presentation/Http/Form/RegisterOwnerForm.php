<?php

declare(strict_types=1);

namespace App\Tenant\Presentation\Http\Form;

use App\Tenant\Application\Dto\RegisterOwnerInput;
use Symfony\Component\HttpFoundation\Request;

final readonly class RegisterOwnerForm
{
    public function fromRequest(Request $request, string $verifiedPhone): RegisterOwnerInput
    {
        return new RegisterOwnerInput(
            (string) $request->request->get('owner_name', ''),
            (string) $request->request->get('email', ''),
            $verifiedPhone,
            (string) $request->request->get('store_name', ''),
            (string) $request->request->get('store_slug', ''),
            (string) $request->request->get('timezone', 'Asia/Yekaterinburg'),
        );
    }
}
