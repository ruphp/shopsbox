<?php

declare(strict_types=1);

namespace App\Tenant\Presentation\Http\Controller;

use App\Tenant\Application\Exception\InvalidAuthCodeInput;
use App\Tenant\Application\UseCase\RequestAuthCodeUseCase;
use App\Tenant\Application\UseCase\VerifyAuthCodeUseCase;
use App\Tenant\Presentation\Http\Form\RequestAuthCodeForm;
use App\Tenant\Presentation\Http\Form\VerifyAuthCodeForm;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
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
    ) {
    }

    #[Route('', name: 'auth_code_form', methods: ['GET'])]
    public function form(): Response
    {
        return new Response($this->twig->render('tenant/auth_code.html.twig'));
    }

    #[Route('/request', name: 'auth_code_request', methods: ['POST'])]
    public function requestCode(Request $request): Response
    {
        try {
            $result = $this->requestAuthCode->execute($this->requestAuthCodeForm->fromRequest($request));
        } catch (InvalidAuthCodeInput $exception) {
            return new Response($this->twig->render('tenant/auth_code.html.twig', [
                'error' => $exception->getMessage(),
                'email' => (string) $request->request->get('email', ''),
            ]), Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        return new Response($this->twig->render('tenant/auth_code.html.twig', [
            'email' => $result->email,
            'requested' => true,
            'expiresAt' => $result->expiresAt,
        ]));
    }

    #[Route('/verify', name: 'auth_code_verify', methods: ['POST'])]
    public function verifyCode(Request $request): Response
    {
        try {
            $result = $this->verifyAuthCode->execute($this->verifyAuthCodeForm->fromRequest($request));
        } catch (InvalidAuthCodeInput $exception) {
            return new Response($this->twig->render('tenant/auth_code.html.twig', [
                'error' => $exception->getMessage(),
                'email' => (string) $request->request->get('email', ''),
                'requested' => true,
            ]), Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $request->getSession()->set('shopsbox_auth_email', $result->email);

        return new Response($this->twig->render('tenant/auth_code.html.twig', [
            'email' => $result->email,
            'verified' => $result->verified,
        ]));
    }
}
