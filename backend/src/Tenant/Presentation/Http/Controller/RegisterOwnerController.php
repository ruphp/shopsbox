<?php

declare(strict_types=1);

namespace App\Tenant\Presentation\Http\Controller;

use App\Tenant\Application\Exception\InvalidTenantInput;
use App\Tenant\Application\Contracts\OwnerRegistrationRepository;
use App\Tenant\Application\UseCase\RegisterOwnerUseCase;
use App\Tenant\Presentation\Http\Form\RegisterOwnerForm;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Throwable;
use Twig\Environment;

final readonly class RegisterOwnerController
{
    private const REGISTRATION_SESSION_TTL_SECONDS = 1800;

    private const RESERVED_STORE_SLUGS = [
        'demo',
        'www',
        'api',
        'admin',
        'app',
        'static',
        'assets',
        'cdn',
        'mail',
        'support',
        'help',
        'billing',
        'status',
        'shopsbox',
    ];

    public function __construct(
        private Environment $twig,
        private RegisterOwnerUseCase $registerOwner,
        private RegisterOwnerForm $form,
        private LoggerInterface $logger,
        private OwnerRegistrationRepository $registrationRepository,
        private CsrfTokenManagerInterface $csrfTokenManager,
    ) {
    }

    #[Route('/register', name: 'owner_register_form', methods: ['GET'])]
    public function start(Request $request): Response
    {
        $phone = $this->verifiedPhone($request);
        if ($phone === null) {
            return new Response($this->twig->render('tenant/auth_code.html.twig', [
                'phoneRequired' => true,
                'channel' => 'phone',
            ]));
        }

        return new RedirectResponse('/owner/registration');
    }

    #[Route('/owner/registration', name: 'owner_registration_form', methods: ['GET'])]
    public function form(Request $request): Response
    {
        $phone = $this->verifiedPhone($request);
        if ($phone === null) {
            return new RedirectResponse('/register');
        }

        return new Response($this->twig->render('tenant/register_owner.html.twig', [
            'phone' => $phone,
            'email' => (string) $request->getSession()->get('shopsbox_auth_email', ''),
        ]));
    }

    #[Route('/owner/registration', name: 'owner_register_submit', methods: ['POST'])]
    public function submit(Request $request): Response
    {
        if (!$this->csrfTokenManager->isTokenValid(new CsrfToken('owner_registration', (string) $request->request->get('_token')))) {
            throw new AccessDeniedHttpException('Invalid CSRF token.');
        }

        $phone = $this->verifiedPhone($request);
        if ($phone === null) {
            return new RedirectResponse('/register');
        }

        if ($request->request->get('terms') !== '1' || $request->request->get('privacy') !== '1') {
            $this->logger->notice('Owner registration rejected: legal terms not accepted.', [
                'phone' => $phone,
                'email' => (string) $request->request->get('email', ''),
                'terms_accepted' => $request->request->get('terms') === '1',
                'privacy_accepted' => $request->request->get('privacy') === '1',
            ]);

            return $this->renderError($request, $phone, 'Нужно принять пользовательское соглашение и политику конфиденциальности.');
        }

        try {
            $result = $this->registerOwner->execute($this->form->fromRequest($request, $phone));
        } catch (InvalidTenantInput $exception) {
            $this->logger->notice('Owner registration rejected: invalid input.', [
                'phone' => $phone,
                'email' => (string) $request->request->get('email', ''),
                'field' => $exception->field,
                'message' => $exception->getMessage(),
            ]);

            return $this->renderError($request, $phone, $exception->getMessage());
        } catch (Throwable $exception) {
            $this->logger->error('Owner registration failed unexpectedly.', [
                'phone' => $phone,
                'email' => (string) $request->request->get('email', ''),
                'exception' => $exception::class,
                'message' => $exception->getMessage(),
            ]);

            return $this->renderError($request, $phone, 'Не удалось завершить регистрацию. Проверьте данные или попробуйте позже.');
        }

        $request->getSession()->set('shopsbox_auth_email', $result->email);
        $this->logger->info('Owner registration completed.', [
            'phone' => $phone,
            'email' => $result->email,
            'tenant_id' => $result->tenantId,
            'store_id' => $result->storeId,
            'user_id' => $result->userId,
            'terms_accepted' => true,
            'privacy_accepted' => true,
        ]);

        return new RedirectResponse('/owner');
    }

    #[Route('/owner/registration/slug/check', name: 'owner_register_slug_check', methods: ['GET'])]
    public function checkSlug(Request $request): JsonResponse
    {
        $slug = strtolower(trim((string) $request->query->get('slug', '')));

        if (!preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $slug)) {
            return new JsonResponse([
                'available' => false,
                'message' => 'Используйте латинские буквы, цифры и дефисы.',
            ]);
        }

        if (in_array($slug, self::RESERVED_STORE_SLUGS, true)) {
            return new JsonResponse([
                'available' => false,
                'message' => 'Этот логин зарезервирован.',
            ]);
        }

        $domain = $slug . '.shopsbox.ru';
        if ($this->registrationRepository->storeSlugExists($slug) || $this->registrationRepository->storeDomainExists($domain)) {
            return new JsonResponse([
                'available' => false,
                'message' => 'Этот логин уже занят.',
            ]);
        }

        return new JsonResponse([
            'available' => true,
            'message' => sprintf('Свободно: %s', $domain),
        ]);
    }

    private function renderError(Request $request, string $phone, string $error): Response
    {
        return new Response($this->twig->render('tenant/register_owner.html.twig', [
            'error' => $error,
            'phone' => $phone,
            'email' => (string) $request->request->get('email', ''),
            'ownerName' => (string) $request->request->get('owner_name', ''),
            'storeName' => (string) $request->request->get('store_name', ''),
            'storeSlug' => (string) $request->request->get('store_slug', ''),
            'timezone' => (string) $request->request->get('timezone', '+05:00'),
        ]), Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    private function verifiedPhone(Request $request): ?string
    {
        $phone = $request->getSession()->get('shopsbox_auth_phone');
        $verifiedAt = $request->getSession()->get('shopsbox_auth_phone_verified_at');

        if (!is_int($verifiedAt) || $verifiedAt < time() - self::REGISTRATION_SESSION_TTL_SECONDS) {
            return null;
        }

        return is_string($phone) && preg_match('/^\+79\d{9}$/', $phone) === 1 ? $phone : null;
    }
}
