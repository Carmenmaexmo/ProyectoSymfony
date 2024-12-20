<?php

namespace App\Controller;

use App\Entity\Pregunta;
use App\Entity\Respuesta;  
use App\Form\PreguntaFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\RespuestaRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Dompdf\Dompdf;  
use Dompdf\Options; 

#[Route('/admin/preguntas')]
class AdminController extends AbstractController
{
    // Ruta para obtener los datos de las respuestas de una pregunta
    #[Route('/pregunta/{id}/respuestas/data', name: 'pregunta_respuestas_data')]
    public function obtenerDatosRespuestas(Pregunta $pregunta, RespuestaRepository $respuestaRepository): JsonResponse
    {
        // Obtener los votos para cada respuesta (1, 2, 3, 4)
        $votos = $respuestaRepository->countVotosPorPregunta($pregunta);  // Asegúrate de que esta función devuelve un array con votos

        // Comprobar si los votos existen para cada respuesta y asignar 0 si no hay
        $respuestasData = [
            'respuesta1' => isset($votos[1]) ? $votos[1] : 0, 
            'respuesta2' => isset($votos[2]) ? $votos[2] : 0, 
            'respuesta3' => isset($votos[3]) ? $votos[3] : 0, 
            'respuesta4' => isset($votos[4]) ? $votos[4] : 0
        ];

        return new JsonResponse([
            'pregunta' => $pregunta->getTexto(), // Nombre de la pregunta
            'respuestas' => $respuestasData, // Cantidad de respuestas para cada opción
        ]);
    }

    #[Route('/new', name: 'app_pregunta_new')]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $pregunta = new Pregunta();
        $form = $this->createForm(PreguntaFormType::class, $pregunta);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Validar si ya existe una pregunta activa en el rango de fechas
            $fechaInicio = $pregunta->getFechaInicio();
            $fechaFin = $pregunta->getFechaFin();
        
            // Buscar preguntas en conflicto con el rango de fechas
            $preguntasConflictivas = $entityManager->getRepository(Pregunta::class)
                ->createQueryBuilder('p')
                ->where('p.fechaInicio <= :fechaFin')
                ->andWhere('p.fechaFin >= :fechaInicio')
                ->setParameter('fechaInicio', $fechaInicio)
                ->setParameter('fechaFin', $fechaFin)
                ->getQuery()
                ->getResult();
        
            // Verificar si hay conflictos
            if (count($preguntasConflictivas) > 0) {
                $this->addFlash('error', 'Ya existe una pregunta activa en el rango de fechas seleccionado.');
                return $this->redirectToRoute('app_pregunta_new'); // Redirigir para ajustar las fechas
            }
        
            // Continuar con la lógica existente
            $respuesta1 = $pregunta->getRespuesta1();
            $respuesta2 = $pregunta->getRespuesta2();
            $respuesta3 = $pregunta->getRespuesta3();
            $respuesta4 = $pregunta->getRespuesta4();
            $solucion = $pregunta->getSolucion();
        
            $respuestasDisponibles = [];
            if (!empty($respuesta1)) $respuestasDisponibles[] = '1';
            if (!empty($respuesta2)) $respuestasDisponibles[] = '2';
            if (!empty($respuesta3)) $respuestasDisponibles[] = '3';
            if (!empty($respuesta4)) $respuestasDisponibles[] = '4';
        
            if (!in_array($solucion, $respuestasDisponibles)) {
                $this->addFlash('error', 'La solución debe ser uno de los números de las respuestas disponibles.');
                return $this->redirectToRoute('app_pregunta_new'); // Redirigir para mostrar el error
            }
        
            // Persistir la pregunta en la base de datos
            $entityManager->persist($pregunta);
            $entityManager->flush();
        
            // Mensaje de éxito
            $this->addFlash('success', 'La pregunta ha sido creada correctamente.');
        
            return $this->redirectToRoute('app_pregunta_new');
        }

        return $this->render('admin/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    // Método para listar las preguntas
    #[Route('/', name: 'app_pregunta_list')]
    public function list(EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $preguntas = $entityManager->getRepository(Pregunta::class)->findAll();

        return $this->render('admin/show.html.twig', [
            'preguntas' => $preguntas,
        ]);
    }

    // Método para ver una pregunta específica
    #[Route('/{id}', name: 'app_pregunta_show', methods: ['GET'])]
    public function show(Pregunta $pregunta, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
    
        // Obtener las respuestas relacionadas con esta pregunta
        $respuestas = $entityManager->getRepository(Respuesta::class)
            ->findBy(['pregunta' => $pregunta]);
    
        return $this->render('admin/show_pregunta.html.twig', [
            'pregunta' => $pregunta,
            'respuestas' => $respuestas, // Pasar las respuestas a la vista
        ]);
    }

    #[Route('/pregunta/{id}/pdf', name: 'pregunta_pdf')]
    public function generarPDF(Pregunta $pregunta, EntityManagerInterface $entityManager): Response
    {
        // Obtener las respuestas relacionadas con esta pregunta
        $respuestas = $entityManager->getRepository(Respuesta::class)->findBy(['pregunta' => $pregunta]);

        // Renderizar la vista como HTML estático (sin ejecutar JS)
        $html = $this->renderView('admin/show_pregunta.html.twig', [
            'pregunta' => $pregunta,
            'respuestas' => $respuestas,
        ]);

        // Configurar DomPDF
        $options = new Options();
        $options->set('defaultFont', 'Arial');
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait'); // Tamaño de papel
        $dompdf->render(); // Renderizar el PDF

        // Devolver el PDF como respuesta
        return new Response(
            $dompdf->output(),
            200,
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="pregunta_detalle.pdf"',
            ]
        );
    }

    // Método para editar una pregunta
    #[Route('/{id}/edit', name: 'app_pregunta_edit')]
    public function edit(Pregunta $pregunta, Request $request, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $form = $this->createForm(PreguntaFormType::class, $pregunta);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Validar solapamiento de fechas al editar
            $fechaInicio = $pregunta->getFechaInicio();
            $fechaFin = $pregunta->getFechaFin();

            $preguntaExistente = $entityManager->getRepository(Pregunta::class)
                ->createQueryBuilder('p')
                ->where('p.fechaInicio <= :fechaFin')
                ->andWhere('p.fechaFin >= :fechaInicio')
                ->andWhere('p.id != :id') // Excluir la pregunta actual
                ->setParameter('fechaInicio', $fechaInicio)
                ->setParameter('fechaFin', $fechaFin)
                ->setParameter('id', $pregunta->getId())
                ->getQuery()
                ->getOneOrNullResult();

            if ($preguntaExistente) {
                $this->addFlash('error', 'Ya existe una pregunta activa en el rango de fechas seleccionado.');
                return $this->render('admin/edit.html.twig', [
                    'form' => $form->createView(),
                    'pregunta' => $pregunta,
                ]);
            }

            // Validar la solución
            $respuesta1 = $pregunta->getRespuesta1();
            $respuesta2 = $pregunta->getRespuesta2();
            $respuesta3 = $pregunta->getRespuesta3();
            $respuesta4 = $pregunta->getRespuesta4();
            $solucion = $pregunta->getSolucion();

            $respuestasDisponibles = [];
            if (!empty($respuesta1)) $respuestasDisponibles[] = '1';
            if (!empty($respuesta2)) $respuestasDisponibles[] = '2';
            if (!empty($respuesta3)) $respuestasDisponibles[] = '3';
            if (!empty($respuesta4)) $respuestasDisponibles[] = '4';

            if (!in_array($solucion, $respuestasDisponibles)) {
                $this->addFlash('error', 'La solución debe ser uno de los números de las respuestas disponibles.');
                return $this->render('admin/edit.html.twig', [
                    'form' => $form->createView(),
                    'pregunta' => $pregunta,
                ]);
            }

            $entityManager->flush();
            $this->addFlash('success', 'La pregunta ha sido actualizada correctamente.');
            return $this->redirectToRoute('app_home');
        }

        return $this->render('admin/edit.html.twig', [
            'form' => $form->createView(),
            'pregunta' => $pregunta,
        ]);
    }

    // Método para eliminar una pregunta
   // Método para eliminar una pregunta
#[Route('/{id}/delete', name: 'app_pregunta_delete', methods: ['POST'])]
public function delete(Request $request, Pregunta $pregunta, EntityManagerInterface $entityManager): Response
{
    $this->denyAccessUnlessGranted('ROLE_ADMIN'); // Verifica que el usuario tiene permisos de administrador

    // Verificar el token CSRF
    if ($this->isCsrfTokenValid('delete' . $pregunta->getId(), $request->request->get('_token'))) {
        try {
            // Eliminar la pregunta
            $entityManager->remove($pregunta);
            $entityManager->flush();

            // Mensaje de éxito
            $this->addFlash('success', 'La pregunta ha sido eliminada correctamente.');
        } catch (\Doctrine\DBAL\Exception\ForeignKeyConstraintViolationException $e) {
            // Mensaje de error si no se puede eliminar debido a respuestas asociadas
            $this->addFlash('error', 'No es posible eliminar esta pregunta ya que tiene respuestas de usuarios asociadas.');
        }
    } else {
        // Si el token no es válido, muestra un error
        $this->addFlash('error', 'No se pudo eliminar la pregunta.');
    }

    // Redirige de vuelta a la lista de preguntas
    return $this->redirectToRoute('app_home');
}


}
