<?php

namespace App\Controller;

use App\Entity\Pregunta; // Importar la entidad Pregunta
use Doctrine\ORM\EntityManagerInterface; // Importar EntityManagerInterface
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    #[Route('/home', name: 'app_home')]
    public function index(EntityManagerInterface $entityManager): Response
    {
        // Obtener la fecha actual
        $now = new \DateTime();

      // Si el usuario es Admin, mostramos todas las preguntas
        if ($this->isGranted('ROLE_ADMIN')) {
            $preguntas = $entityManager->getRepository(Pregunta::class)->findAll();
        } else {
            // Si el usuario no es Admin, solo mostramos las preguntas activas
            $preguntas = $entityManager->getRepository(Pregunta::class)
                ->createQueryBuilder('p')
                ->where('p.fechaInicio <= :now')
                ->andWhere('p.fechaFin >= :now')
                ->setParameter('now', $now)
                ->getQuery()
                ->getResult();
        }


        // Verificar si no hay preguntas activas
        $noHayPreguntasActivas = count($preguntas) === 0;

        // Mostrar diferentes vistas según el rol
        return $this->render('home/index.html.twig', [
            'preguntas' => $preguntas,
            'esAdmin' => $this->isGranted('ROLE_ADMIN'),
            'noHayPreguntasActivas' => $noHayPreguntasActivas, // Pasar esta variable
        ]);
    }
}
