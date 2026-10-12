<?php

declare(strict_types=1);

namespace App\Tenant\Presentation\Http\Controller;

use App\Tenant\Application\Exception\InvalidAuthCodeInput;
use App\Tenant\Application\UseCase\RequestAuthCodeUseCase;
use App\Tenant\Application\UseCase\VerifyAuthCodeUseCase;
use App\Tenant\Application\UseCase\ConfirmUserPhoneUseCase;
use App\Tenant\Presentation\Http\Form\RequestAuthCodeForm;
use App\Tenant\Presentation\Http\Form\VerifyAuthCodeForm;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Throwable;
use Twig\Environment;

#[Route('/auth/code')]
final readonly class AuthCodeController
{
    public function __construct(
        private Environment $twig,
        private RequestAuthCodeUseCase $requestAuthCode,
        private VerifyAuthCodeUseCase $verifyAuthCode,
        private RequestAuthCodeForm $requestAuthCodeForm,
        private VerifyAuthCodeForm $verifyAuthCodeForm,
        private CsrfTokenManagerInterface $csrfTokenManager,
        private ConfirmUserPhoneUseCase $confirmUserPhone,
    ) {
    }

    #[Route('', name: 'auth_code_form', methods: ['GET'])]
    public function form(Request $request): Response
    {
        if (!$request->query->getBoolean('phone_required')) {
            return new RedirectResponse('/auth/login');
        }

        return new Response($this->twig->render('tenant/auth_code.html.twig', [
            'phoneRequired' => $request->query->getBoolean('phone_required'),
            'channel' => $request->query->getBoolean('phone_required') ? 'phone' : 'email',
        ]));
    }

    #[Route('/request', name: 'auth_code_request', methods: ['POST'])]
    public function requestCode(Request $request): Response
    {
        $phoneRequired = $request->request->getBoolean('phone_required');
        if (!$phoneRequired) {
            return new RedirectResponse('/auth/login');
        }
        $this->requireValidToken($request, 'registration_phone_request');

        try {
            $result = $this->requestAuthCode->execute($this->requestAuthCodeForm->fromRequest($request));
        } catch (InvalidAuthCodeInput $exception) {
            return new Response($this->twig->render('tenant/auth_code.html.twig', [
                'error' => $exception->getMessage(),
                'email' => (string) $request->request->get('email', ''),
                'phone' => (string) $request->request->get('phone', ''),
                'channel' => $phoneRequired ? 'phone' : 'email',
                'phoneRequired' => $phoneRequired,
            ]), Response::HTTP_UNPROCESSABLE_ENTITY);
        } catch (Throwable) {
            return new Response($this->twig->render('tenant/auth_code.html.twig', [
                'error' => $phoneRequired
                    ? 'Не удалось передать SMS на отправку. Попробуйте немного позже.'
                    : 'Не удалось отправить код. Попробуйте немного позже.',
                'email' => (string) $request->request->get('email', ''),
                'phone' => (string) $request->request->get('phone', ''),
                'channel' => $phoneRequired ? 'phone' : 'email',
                'phoneRequired' => $phoneRequired,
            ]), Response::HTTP_BAD_GATEWAY);
        }

        return new Response($this->twig->render('tenant/auth_code.html.twig', [
            'email' => $result->email,
            'phone' => $result->phone,
            'channel' => $result->channel,
            'recipient' => $result->recipient,
            'requested' => true,
            'expiresAt' => $result->expiresAt,
            'phoneRequired' => $phoneRequired,
        ]));
    }

    #[Route('/verify', name: 'auth_code_verify', methods: ['POST'])]
    public function verifyCode(Request $request): Response
    {
        $phoneRequired = $request->request->getBoolean('phone_required');
        if (!$phoneRequired) {
            return new RedirectResponse('/auth/login');
        }
        $this->requireValidToken($request, 'registration_phone_verify');

        try {
            $result = $this->verifyAuthCode->execute($this->verifyAuthCodeForm->fromRequest($request));
        } catch (InvalidAuthCodeInput $exception) {
            return new Response($this->twig->render('tenant/auth_code.html.twig', [
                'error' => $exception->getMessage(),
                'email' => (string) $request->request->get('email', ''),
                'phone' => (string) $request->request->get('phone', ''),
                'channel' => $phoneRequired ? 'phone' : 'email',
                'requested' => true,
                'phoneRequired' => $phoneRequired,
            ]), Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        if ($result->phone !== '') {
            $request->getSession()->set('shopsbox_auth_phone', $result->phone);
            $request->getSession()->set('shopsbox_auth_phone_verified_at', time());

            $ownerEmail = $request->getSession()->get('shopsbox_auth_email');
            if (is_string($ownerEmail) && $ownerEmail !== '' && $this->confirmUserPhone->execute(
                $ownerEmail,
                $result->phone,
                $request->getClientIp(),
                $request->headers->get('User-Agent'),
            )) {
                return new RedirectResponse('/owner');
            }
        }
        $request->getSession()->set('shopsbox_auth_channel', $result->channel);

        return new RedirectResponse('/owner/registration');
    }

    private function requireValidToken(Request $request, string $id): void
    {
        if (!$this->csrfTokenManager->isTokenValid(new CsrfToken($id, (string) $request->request->get('_token')))) {
            throw new AccessDeniedHttpException('Invalid CSRF token.');
        }
    }
}
