<?php

declare(strict_types=1);

namespace App\Tenant\Presentation\Http\Controller;

use App\Tenant\Application\UseCase\CreateTenantUseCase;
use App\Tenant\Application\Exception\InvalidTenantInput;
use App\Tenant\Presentation\Http\Form\CreateTenantForm;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Attribute\Route;

final class CreateTenantController
{
    public function __construct(
        private readonly CreateTenantUseCase $useCase,
        private readonly CreateTenantForm $form,
    ) {
    }

    #[Route('/tenants', name: 'tenant_create', methods: ['POST'])]
    public function __invoke(Request $request): JsonResponse
    {
        try {
            $input = $this->form->fromRequest($request);
            $result = $this->useCase->execute($input);
        } catch (BadRequestHttpException $exception) {
            return new JsonResponse([
                'error' => 'bad_request',
                'message' => $exception->getMessage(),
            ], JsonResponse::HTTP_BAD_REQUEST);
        } catch (InvalidTenantInput $exception) {
            return new JsonResponse([
                'error' => 'validation_error',
                'field' => $exception->field,
                'message' => $exception->getMessage(),
            ], JsonResponse::HTTP_BAD_REQUEST);
        }

        return new JsonResponse([
            'tenant_id' => $result->tenantId,
            'store_id' => $result->storeId,
        ], JsonResponse::HTTP_CREATED);
    }
}
