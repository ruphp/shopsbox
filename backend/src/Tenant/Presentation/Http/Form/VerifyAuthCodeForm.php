<?php

declare(strict_types=1);

namespace App\Tenant\Presentation\Http\Form;

use App\Tenant\Application\Dto\VerifyAuthCodeInput;
use Symfony\Component\HttpFoundation\Request;

final readonly class VerifyAuthCodeForm
{
    public function fromRequest(Request $request): VerifyAuthCodeInput
    {
        return new VerifyAuthCodeInput(
            (string) $request->request->get('email', ''),
            (string) $request->request->get('code', ''),
        );
    }
}
