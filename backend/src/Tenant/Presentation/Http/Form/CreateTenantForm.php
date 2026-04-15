<?php

declare(strict_types=1);

namespace App\Tenant\Presentation\Http\Form;

use App\Tenant\Application\Dto\CreateTenantInput;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

final class CreateTenantForm
{
    public function fromRequest(Request $request): CreateTenantInput
    {
        $payload = json_decode((string) $request->getContent(), true);
        if (!is_array($payload)) {
            throw new BadRequestHttpException('Invalid JSON payload.');
        }

        $tenantName = trim((string) ($payload['tenant_name'] ?? ''));
        $billingEmail = trim((string) ($payload['billing_email'] ?? ''));
        $storeName = trim((string) ($payload['store_name'] ?? ''));
        $storeSlug = trim((string) ($payload['store_slug'] ?? ''));
        $storeDomain = trim((string) ($payload['store_domain'] ?? ''));

        if ($tenantName === '' || $billingEmail === '' || $storeName === '' || $storeSlug === '' || $storeDomain === '') {
            throw new BadRequestHttpException('Missing required fields.');
        }

        $defaultCurrency = trim((string) ($payload['default_currency'] ?? 'RUB'));
        $timezone = trim((string) ($payload['timezone'] ?? 'Asia/Yekaterinburg'));

        return new CreateTenantInput(
            $tenantName,
            $billingEmail,
            $storeName,
            $storeSlug,
            $storeDomain,
            $defaultCurrency,
            $timezone,
        );
    }
}
