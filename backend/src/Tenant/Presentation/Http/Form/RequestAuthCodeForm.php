<?php

declare(strict_types=1);

namespace App\Tenant\Presentation\Http\Form;

use App\Tenant\Application\Dto\RequestAuthCodeInput;
use Symfony\Component\HttpFoundation\Request;

final readonly class RequestAuthCodeForm
{
    public function fromRequest(Request $request): RequestAuthCodeInput
    {
        $channel = (string) $request->request->get('channel', 'email');
        $recipient = (string) $request->request->get('recipient', '');

        return new RequestAuthCodeInput(
            $channel === 'phone' ? (string) $request->request->get('email', '') : $recipient,
            $channel === 'phone' ? $recipient : (string) $request->request->get('phone', ''),
            $channel,
            $request->request->getBoolean('phone_required'),
        );
    }
}
