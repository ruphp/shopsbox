<?php

declare(strict_types=1);

namespace App\Tenant\Presentation\Http\Controller;

use App\Tenant\Application\Exception\InvalidAuthCodeInput;
use App\Tenant\Application\UseCase\RequestAuthCodeUseCase;
use App\Tenant\Application\UseCase\VerifyAuthCodeUseCase;
use App\Tenant\Presentation\Http\Form\RequestAuthCodeForm;
use App\Tenant\Presentation\Http\Form\VerifyAuthCodeForm;
use Symfony\Component\HttpFoundation\RedirectResponse;
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
    public function form(Request $request): Response
    {
        return new Response($this->twig->render('tenant/auth_code.html.twig', [
            'phoneRequired' => $request->query->getBoolean('phone_required'),
            'channel' => $request->query->getBoolean('phone_required') ? 'phone' : 'email',
        ]));
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
                'phone' => (string) $request->request->get('phone', ''),
                'channel' => (string) $request->request->get('channel', 'email'),
                'phoneRequired' => $request->request->getBoolean('phone_required'),
            ]), Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        return new Response($this->twig->render('tenant/auth_code.html.twig', [
            'email' => $result->email,
            'phone' => $result->phone,
            'channel' => $result->channel,
            'recipient' => $result->recipient,
            'requested' => true,
            'expiresAt' => $result->expiresAt,
            'phoneRequired' => $request->request->getBoolean('phone_required'),
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
                'phone' => (string) $request->request->get('phone', ''),
                'channel' => (string) $request->request->get('channel', 'email'),
                'requested' => true,
                'phoneRequired' => $request->request->getBoolean('phone_required'),
            ]), Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        if ($result->email !== '') {
            $request->getSession()->set('shopsbox_auth_email', $result->email);
        }
        if ($result->phone !== '') {
            $request->getSession()->set('shopsbox_auth_phone', $result->phone);
        }
        $request->getSession()->set('shopsbox_auth_channel', $result->channel);

        if ($request->request->getBoolean('phone_required')) {
            return new RedirectResponse('/register');
        }

        return new Response($this->twig->render('tenant/auth_code.html.twig', [
            'email' => $result->email,
            'phone' => $result->phone,
            'channel' => $result->channel,
            'recipient' => $result->recipient,
            'verified' => $result->verified,
        ]));
    }
}
