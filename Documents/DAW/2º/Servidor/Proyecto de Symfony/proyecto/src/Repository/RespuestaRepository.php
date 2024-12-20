<?php

namespace App\Repository;

use App\Entity\Respuesta;
use App\Entity\Pregunta;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Respuesta>
 */
class RespuestaRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Respuesta::class);
    }

    /**
     * Contar cuántos votos se han dado por una opción específica de respuesta para una pregunta.
     *
     * @param Pregunta $pregunta
     * @param int $respuestaId
     * @return int
     */
    public function countVotosPorRespuesta(Pregunta $pregunta, int $respuestaId): int
    {
        return $this->createQueryBuilder('r')
            ->select('count(r.id)')
            ->where('r.pregunta = :pregunta')
            ->andWhere('r.respuestaId = :respuestaId')
            ->setParameter('pregunta', $pregunta)
            ->setParameter('respuestaId', $respuestaId)
            ->getQuery()
            ->getSingleScalarResult(); // Devuelve el número de respuestas (votos) para una opción
    }

    /**
     * Contar cuántos votos se han dado por cada opción de respuesta (1, 2, 3, 4) para una pregunta.
     * Devuelve un array con la cantidad de votos para cada respuesta.
     *
     * @param Pregunta $pregunta
     * @return array
     */
    public function countVotosPorPregunta(Pregunta $pregunta): array
    {
        $votos = [];

        // Contar los votos para cada respuesta (1, 2, 3, 4)
        for ($i = 1; $i <= 4; $i++) {
            $votos[$i] = $this->countVotosPorRespuesta($pregunta, $i);
        }

        return $votos;
    }
}
