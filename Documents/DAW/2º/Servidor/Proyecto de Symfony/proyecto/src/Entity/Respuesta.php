<?php

namespace App\Entity;

use App\Repository\RespuestaRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RespuestaRepository::class)]
class Respuesta
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    // Relación con el usuario que ha dado esta respuesta (N:1)
    #[ORM\ManyToOne(targetEntity: Usuario::class, inversedBy: 'respuestas')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Usuario $usuario = null;

    // Relación con la pregunta (N:1)
    #[ORM\ManyToOne(targetEntity: Pregunta::class, inversedBy: 'respuestas')]
    #[ORM\JoinColumn(nullable: false)]  
    private ?Pregunta $pregunta = null;

    // Guardamos el ID de la respuesta seleccionada (de las 4 posibles respuestas)
    #[ORM\Column]
    private ?int $respuestaId = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUsuario(): ?Usuario
    {
        return $this->usuario;
    }

    public function setUsuario(Usuario $usuario): self
    {
        $this->usuario = $usuario;
        return $this;
    }

    public function getPregunta(): ?Pregunta
    {
        return $this->pregunta;
    }

    public function setPregunta(Pregunta $pregunta): self
    {
        $this->pregunta = $pregunta;
        return $this;
    }

    public function getRespuestaId(): ?int
    {
        return $this->respuestaId;
    }

    public function setRespuestaId(int $respuestaId): self
    {
        $this->respuestaId = $respuestaId;
        return $this;
    }
    
}

