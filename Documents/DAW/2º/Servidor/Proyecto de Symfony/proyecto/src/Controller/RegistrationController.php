<?php

namespace App\Controller;

use App\Entity\Usuario;
use App\Form\RegistrationFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

class RegistrationController extends AbstractController
{
    #[Route('/register', name: 'app_register')]
    public function register(
        Request $request,
        UserPasswordHasherInterface $userPasswordHasher,
        EntityManagerInterface $entityManager,
        MailerInterface $mailer
    ): Response {
        $user = new Usuario();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var string $plainPassword */
            $plainPassword = $form->get('plainPassword')->getData();

            // Codificar la contraseña
            $user->setPassword($userPasswordHasher->hashPassword($user, $plainPassword));

            // Establecer al usuario como no verificado
            $user->setIsVerified(false);  // El usuario inicialmente no está verificado
            $confirmationToken = bin2hex(random_bytes(32));  // Generar token
            $user->setConfirmationToken($confirmationToken);  // Asignar token al usuario

            // Persistir en la base de datos
            $entityManager->persist($user);
            $entityManager->flush();

            // Generar la URL de confirmación
            $confirmationUrl = sprintf(
                'http://localhost:8000/confirm-email/%s',
                $confirmationToken
            );

            // Crear y enviar el correo
            $email = (new Email())
                ->from('formulario@preguntas.com')
                ->to($user->getEmail())
                ->subject('Confirma tu cuenta')
                ->html(sprintf(
                    "<p>Gracias por registrarte en nuestra plataforma. Por favor, confirma tu cuenta haciendo clic en el siguiente enlace:</p>
                    <a href='%s'>Confirmar cuenta</a>",
                    $confirmationUrl
                ));

            $mailer->send($email);

            $this->addFlash('success', 'Se ha enviado un correo de confirmación. Por favor, revisa tu bandeja de entrada.');
            return $this->redirectToRoute('app_login');
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }

    #[Route('/confirm-email/{token}', name: 'app_confirm_email')]
    public function confirmEmail(string $token, EntityManagerInterface $entityManager): Response
    {
        // Buscar el usuario por el token de confirmación
        $user = $entityManager->getRepository(Usuario::class)->findOneBy(['confirmationToken' => $token]);

        if (!$user) {
            $this->addFlash('error', 'El token de confirmación es inválido o ya ha sido utilizado.');
            return $this->redirectToRoute('app_register');
        }

        // Confirmar la cuenta y limpiar el token
        $user->setIsVerified(true);  // Establecer la cuenta como verificada
        $user->setConfirmationToken(null); // Eliminar el token
        $entityManager->flush(); // Guardar los cambios

        $this->addFlash('success', 'Tu cuenta ha sido confirmada. Ahora puedes iniciar sesión.');
        return $this->redirectToRoute('app_login');
    }
}
