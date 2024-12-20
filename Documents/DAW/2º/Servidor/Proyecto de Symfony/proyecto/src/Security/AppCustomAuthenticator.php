<?php

namespace App\Security;

use App\Entity\Usuario;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AbstractLoginFormAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\CsrfTokenBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\PasswordCredentials;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Doctrine\ORM\EntityManagerInterface;  

class AppCustomAuthenticator extends AbstractLoginFormAuthenticator
{
    public const LOGIN_ROUTE = 'app_login';

    private UrlGeneratorInterface $urlGenerator;
    private EntityManagerInterface $entityManager;  // Variable para el EntityManager

    public function __construct(UrlGeneratorInterface $urlGenerator, EntityManagerInterface $entityManager)
    {
        $this->urlGenerator = $urlGenerator;
        $this->entityManager = $entityManager;  // Inyectamos el EntityManager
    }

    public function authenticate(Request $request): Passport
    {
        $email = $request->request->get('email');
        $password = $request->request->get('password');

        // Guardar el email en la sesión para mostrarlo en el login
        $request->getSession()->set('LAST_USERNAME', $email);

        return new Passport(
            new UserBadge($email, function($userIdentifier) {
                // Buscar el usuario por su email
                $user = $this->entityManager->getRepository(Usuario::class)->findOneByEmail($userIdentifier);

                if (!$user) {
                    throw new CustomUserMessageAuthenticationException('El correo electrónico no está registrado.');
                }

                // Verificar si el usuario está verificado
                if (!$user->isVerified()) {
                    throw new CustomUserMessageAuthenticationException('Debes confirmar tu cuenta antes de iniciar sesión.');
                }

                return $user;
            }),
            new PasswordCredentials($password),
            [
                new CsrfTokenBadge('authenticate', $request->request->get('_csrf_token')),
            ]
        );
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        // Aquí redirigimos manualmente a la página de inicio después de un inicio de sesión exitoso
        return new RedirectResponse($this->urlGenerator->generate('app_home')); // Cambia 'app_home' por la ruta a la que quieres redirigir al usuario
    }

    protected function getLoginUrl(Request $request): string
    {
        return $this->urlGenerator->generate(self::LOGIN_ROUTE);
    }
}
