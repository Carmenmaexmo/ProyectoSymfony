<?php

namespace App\Controller;

use App\Entity\Respuesta;
use App\Repository\PreguntaRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Pregunta;
use App\Repository\RespuestaRepository;
use Symfony\Component\HttpFoundation\JsonResponse;

class UserController extends AbstractController
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


    // Mostrar preguntas activas
    #[Route('/preguntas', name: 'app_preguntas_activas')]
    public function listarPreguntasActivas(
        PreguntaRepository $preguntaRepository,
        EntityManagerInterface $entityManager
    ): Response {
        $now = new \DateTime(); // Fecha y hora actual
        $usuario = $this->getUser();

        // Obtener preguntas activas
        $preguntasActivas = $preguntaRepository->findPreguntasActivas($now);

        // Comprobar si el usuario ha respondido a cada pregunta
        $preguntasConEstado = [];
        foreach ($preguntasActivas as $pregunta) {
            $respuesta = $entityManager->getRepository(Respuesta::class)->findOneBy([
                'usuario' => $usuario,
                'pregunta' => $pregunta,
            ]);

            $preguntasConEstado[] = [
                'pregunta' => $pregunta,
                'yaRespondida' => $respuesta !== null, // Si hay una respuesta, ya está respondida
            ];
        }

        return $this->render('user/preguntas.html.twig', [
            'preguntasConEstado' => $preguntasConEstado,
        ]);
    }


    // Formulario para responder una pregunta
#[Route('/pregunta/{id}/responder', name: 'app_responder_pregunta')]
public function responderPregunta(
    int $id,
    PreguntaRepository $preguntaRepository,
    EntityManagerInterface $entityManager,
    Request $request
): Response {
    $usuario = $this->getUser();

    // Verificar si el usuario está autenticado
    if (!$usuario) {
        throw $this->createAccessDeniedException('Debes iniciar sesión para responder a una pregunta.');
    }

    // Obtener la pregunta
    $pregunta = $preguntaRepository->find($id);
    if (!$pregunta) {
        throw $this->createNotFoundException('La pregunta no existe.');
    }

    // Verificar si la pregunta está activa
    $now = new \DateTime();
    if ($pregunta->getFechaInicio() > $now || $pregunta->getFechaFin() < $now) {
        $this->addFlash('error', 'Esta pregunta no está activa.');

    }

    // Verificar si ya respondió a esta pregunta
    $respuestaExistente = $entityManager->getRepository(Respuesta::class)->findOneBy([
        'usuario' => $usuario,
        'pregunta' => $pregunta,
    ]);

    // Si ya respondió a la pregunta, mostrar el mensaje de error y evitar el envío
    if ($respuestaExistente) {
        return $this->render('user/responder.html.twig', [
            'pregunta' => $pregunta,
            'yaRespondida' => true, // Indicamos que ya fue respondida
        ]);
    }

    // Procesar el formulario de respuesta
    if ($request->isMethod('POST')) {
        $respuestaSeleccionada = $request->request->get('respuesta'); // Capturar respuesta seleccionada

        if (!$respuestaSeleccionada) {
            $this->addFlash('error', 'Debes seleccionar una respuesta.');
            return $this->redirectToRoute('app_responder_pregunta', ['id' => $id]);
        }

        // Crear nueva respuesta
        $nuevaRespuesta = new Respuesta();
        $nuevaRespuesta->setUsuario($usuario);
        $nuevaRespuesta->setPregunta($pregunta);
        $nuevaRespuesta->setRespuestaId((int)$respuestaSeleccionada);

        // Guardar en la base de datos
        $entityManager->persist($nuevaRespuesta);
        $entityManager->flush();

        $this->addFlash('success', 'Tu respuesta ha sido guardada correctamente.');
        return $this->redirectToRoute('app_home');
    }

    return $this->render('user/responder.html.twig', [
        'pregunta' => $pregunta,
        'yaRespondida' => false, // La pregunta no ha sido respondida aún
    ]);
}



    // Ver respuestas anteriores
    #[Route('/respuestas', name: 'app_respuestas_anteriores')]
    public function verRespuestasAnteriores(EntityManagerInterface $entityManager): Response
    {
        $usuario = $this->getUser();

        // Verificar si el usuario está autenticado
        if (!$usuario) {
            throw $this->createAccessDeniedException('Debes iniciar sesión para ver tus respuestas.');
        }

        // Obtener respuestas del usuario
        $respuestas = $entityManager->getRepository(Respuesta::class)->findBy(['usuario' => $usuario]);

        return $this->render('user/respuestas_anteriores.html.twig', [
            'respuestas' => $respuestas,
        ]);
    }
}
