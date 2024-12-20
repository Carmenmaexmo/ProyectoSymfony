<?php

// src/Entity/Pregunta.php

namespace App\Entity;

use App\Repository\PreguntaRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PreguntaRepository::class)]
class Pregunta
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $texto = null;

    #[ORM\Column(length: 255)]
    private string $respuesta1;  // No puede ser null

    #[ORM\Column(length: 255)]
    private string $respuesta2;  // No puede ser null

    #[ORM\Column(nullable: true)]
    private ?string $respuesta3 = null;  // Puede ser null

    #[ORM\Column(nullable: true)]
    private ?string $respuesta4 = null;  // Puede ser null

    // Relación con las respuestas (1:N)
    #[ORM\OneToMany(mappedBy: 'pregunta', targetEntity: Respuesta::class)]
    private $respuestas;

    #[ORM\Column(type: "datetime")]
    private ?\DateTimeInterface $fechaInicio;

    #[ORM\Column(type: "datetime")]
    private ?\DateTimeInterface $fechaFin;

    // Agregar la propiedad solucion
    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $solucion = null;

    public function __construct()
    {
        $this->respuestas = new \Doctrine\Common\Collections\ArrayCollection();
        // Asignar valores predeterminados a respuesta1 y respuesta2 si es necesario
        $this->respuesta1 = '';
        $this->respuesta2 = '';
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTexto(): ?string
    {
        return $this->texto;
    }

    public function setTexto(string $texto): self
    {
        $this->texto = $texto;
        return $this;
    }

    public function getRespuesta1(): ?string
    {
        return $this->respuesta1;
    }

    public function setRespuesta1(string $respuesta1): self
    {
        // Asegurarse de que respuesta1 nunca sea null
        if ($respuesta1 === null) {
            throw new \InvalidArgumentException("Respuesta1 no puede ser null");
        }
        $this->respuesta1 = $respuesta1;
        return $this;
    }

    public function getRespuesta2(): ?string
    {
        return $this->respuesta2;
    }

    public function setRespuesta2(string $respuesta2): self
    {
        // Asegurarse de que respuesta2 nunca sea null
        if ($respuesta2 === null) {
            throw new \InvalidArgumentException("Respuesta2 no puede ser null");
        }
        $this->respuesta2 = $respuesta2;
        return $this;
    }

    public function getRespuesta3(): ?string
    {
        return $this->respuesta3;
    }

    public function setRespuesta3(?string $respuesta3): self
    {
        $this->respuesta3 = $respuesta3;
        return $this;
    }

    public function getRespuesta4(): ?string
    {
        return $this->respuesta4;
    }

    public function setRespuesta4(?string $respuesta4): self
    {
        $this->respuesta4 = $respuesta4;
        return $this;
    }

    // Métodos para manejar las respuestas
    public function addRespuesta(Respuesta $respuesta): self
    {
        if (!$this->respuestas->contains($respuesta)) {
            $this->respuestas[] = $respuesta;
            $respuesta->setPregunta($this);  // Establecer la pregunta en la respuesta
        }
        return $this;
    }

    public function getRespuestas()
    {
        return $this->respuestas;
    }

    // Métodos para fechaInicio
    public function getFechaInicio(): ?\DateTimeInterface
    {
        return $this->fechaInicio;
    }

    public function setFechaInicio(\DateTimeInterface $fechaInicio): self
    {
        $this->fechaInicio = $fechaInicio;
        return $this;
    }

    // Métodos para fechaFin
    public function getFechaFin(): ?\DateTimeInterface
    {
        return $this->fechaFin;
    }

    public function setFechaFin(\DateTimeInterface $fechaFin): self
    {
        $this->fechaFin = $fechaFin;
        return $this;
    }

    // Métodos para la propiedad solucion
    public function getSolucion(): ?int
    {
        return $this->solucion;
    }

    public function setSolucion(?int $solucion): self
    {
        $this->solucion = $solucion;
        return $this;
    }
}