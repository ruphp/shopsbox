<?php

declare(strict_types=1);

namespace App\Tenant\Presentation\Http\Form;

use App\Tenant\Application\Dto\RequestAuthCodeInput;
use Symfony\Component\HttpFoundation\Request;

final readonly class RequestAuthCodeForm
{
    public function fromRequest(Request $request): RequestAuthCodeInput
    {
        return new RequestAuthCodeInput((string) $request->request->get('email', ''));
    }
}
