<?php

declare(strict_types=1);

namespace App\Tenant\Presentation\Http\Controller;

use App\Tenant\Application\Dto\PasswordLoginInput;
use App\Tenant\Application\Dto\RequestAuthCodeInput;
use App\Tenant\Application\Dto\ResetPasswordInput;
use App\Tenant\Application\Dto\VerifyAuthCodeInput;
use App\Tenant\Application\Exception\InvalidAuthCodeInput;
use App\Tenant\Application\Exception\InvalidPasswordInput;
use App\Tenant\Application\UseCase\LoginWithPasswordUseCase;
use App\Tenant\Application\UseCase\RequestAuthCodeUseCase;
use App\Tenant\Application\UseCase\ResetPasswordUseCase;
use App\Tenant\Application\UseCase\VerifyAuthCodeUseCase;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Throwable;
use Twig\Environment;

final readonly class PasswordAuthController
{
    private const RESET_SESSION_TTL_SECONDS = 600;

    public function __construct(
        private Environment $twig,
        private LoginWithPasswordUseCase $login,
        private RequestAuthCodeUseCase $requestCode,
        private VerifyAuthCodeUseCase $verifyCode,
        private ResetPasswordUseCase $resetPassword,
        private CsrfTokenManagerInterface $csrfTokenManager,
    ) {
    }

    #[Route('/auth/login', name: 'auth_login_form', methods: ['GET'])]
    public function loginForm(Request $request): Response
    {
        if (is_string($request->getSession()->get('shopsbox_auth_email'))) {
            return new RedirectResponse('/owner');
        }

        return new Response($this->twig->render('tenant/password_login.html.twig'));
    }

    #[Route('/auth/login', name: 'auth_login_submit', methods: ['POST'])]
    public function loginSubmit(Request $request): Response
    {
        $this->requireValidToken($request, 'password_login');
        $email = (string) $request->request->get('email', '');

        try {
            $user = $this->login->execute(new PasswordLoginInput($email, (string) $request->request->get('password', '')));
        } catch (InvalidPasswordInput $exception) {
            return new Response($this->twig->render('tenant/password_login.html.twig', [
                'email' => $email,
                'error' => $exception->getMessage(),
            ]), Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $request->getSession()->migrate(true);
        $request->getSession()->set('shopsbox_auth_email', $user->email);
        if ($user->verifiedPhone !== null) {
            $request->getSession()->set('shopsbox_auth_phone', $user->verifiedPhone);
            $request->getSession()->set('shopsbox_auth_phone_verified_at', time());
        }

        return new RedirectResponse('/owner');
    }

    #[Route('/auth/password/forgot', name: 'auth_password_forgot', methods: ['GET'])]
    public function forgotForm(): Response
    {
        return new Response($this->twig->render('tenant/password_forgot.html.twig'));
    }

    #[Route('/auth/password/forgot', name: 'auth_password_forgot_request', methods: ['POST'])]
    public function forgotRequest(Request $request): Response
    {
        $this->requireValidToken($request, 'password_forgot');
        $email = strtolower(trim((string) $request->request->get('email', '')));

        try {
            $result = $this->requestCode->execute(new RequestAuthCodeInput(
                $email,
                requestIp: $request->getClientIp() ?? '',
            ));
        } catch (InvalidAuthCodeInput $exception) {
            return new Response($this->twig->render('tenant/password_forgot.html.twig', [
                'email' => $email,
                'error' => $exception->getMessage(),
            ]), Response::HTTP_UNPROCESSABLE_ENTITY);
        } catch (Throwable) {
            return new Response($this->twig->render('tenant/password_forgot.html.twig', [
                'email' => $email,
                'error' => 'Не удалось отправить код. Попробуйте немного позже.',
            ]), Response::HTTP_BAD_GATEWAY);
        }

        return new Response($this->twig->render('tenant/password_forgot.html.twig', [
            'email' => $email,
            'requested' => true,
            'expiresAt' => $result->expiresAt,
        ]));
    }

    #[Route('/auth/password/verify', name: 'auth_password_verify', methods: ['POST'])]
    public function verifyResetCode(Request $request): Response
    {
        $this->requireValidToken($request, 'password_verify');
        $email = strtolower(trim((string) $request->request->get('email', '')));

        try {
            $result = $this->verifyCode->execute(new VerifyAuthCodeInput(
                $email,
                (string) $request->request->get('code', ''),
                '',
                'email',
                $request->getClientIp(),
                $request->headers->get('User-Agent'),
            ));
        } catch (InvalidAuthCodeInput $exception) {
            return new Response($this->twig->render('tenant/password_forgot.html.twig', [
                'email' => $email,
                'requested' => true,
                'error' => $exception->getMessage(),
            ]), Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $request->getSession()->migrate(true);
        $request->getSession()->set('shopsbox_password_reset_email', $result->email);
        $request->getSession()->set('shopsbox_password_reset_verified_at', time());

        return new RedirectResponse('/auth/password/reset');
    }

    #[Route('/auth/password/reset', name: 'auth_password_reset_form', methods: ['GET'])]
    public function resetForm(Request $request): Response
    {
        if ($this->resetEmail($request) === null) {
            return new RedirectResponse('/auth/password/forgot');
        }

        return new Response($this->twig->render('tenant/password_reset.html.twig'));
    }

    #[Route('/auth/password/reset', name: 'auth_password_reset_submit', methods: ['POST'])]
    public function resetSubmit(Request $request): Response
    {
        $this->requireValidToken($request, 'password_reset');
        $email = $this->resetEmail($request);
        if ($email === null) {
            return new RedirectResponse('/auth/password/forgot');
        }

        try {
            $this->resetPassword->execute(new ResetPasswordInput(
                $email,
                (string) $request->request->get('password', ''),
                (string) $request->request->get('password_confirmation', ''),
            ));
        } catch (InvalidPasswordInput $exception) {
            return new Response($this->twig->render('tenant/password_reset.html.twig', [
                'error' => $exception->getMessage(),
            ]), Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $request->getSession()->remove('shopsbox_password_reset_email');
        $request->getSession()->remove('shopsbox_password_reset_verified_at');
        $request->getSession()->getFlashBag()->add('password_success', 'Пароль изменён. Войдите с новым паролем.');

        return new RedirectResponse('/auth/login');
    }

    #[Route('/auth/logout', name: 'auth_logout', methods: ['POST'])]
    public function logout(Request $request): Response
    {
        $this->requireValidToken($request, 'logout');
        $request->getSession()->remove('shopsbox_auth_email');
        $request->getSession()->remove('shopsbox_auth_phone');
        $request->getSession()->remove('shopsbox_auth_phone_verified_at');
        $request->getSession()->invalidate();

        return new RedirectResponse('/auth/login');
    }

    private function resetEmail(Request $request): ?string
    {
        $email = $request->getSession()->get('shopsbox_password_reset_email');
        $verifiedAt = $request->getSession()->get('shopsbox_password_reset_verified_at');
        if (!is_string($email) || !is_int($verifiedAt) || $verifiedAt < time() - self::RESET_SESSION_TTL_SECONDS) {
            return null;
        }

        return $email;
    }

    private function requireValidToken(Request $request, string $id): void
    {
        if (!$this->csrfTokenManager->isTokenValid(new CsrfToken($id, (string) $request->request->get('_token')))) {
            throw new AccessDeniedHttpException('Invalid CSRF token.');
        }
    }
}
