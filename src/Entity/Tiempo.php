<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Post;
use App\Controller\TiempoController;
use App\Repository\TiempoRepository;
use App\State\TiempoGetProvider;
use App\State\TiempoProcessor;
use App\Validator\Constraints\MinimalLenght;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: TiempoRepository::class)]
#[ApiResource(
    normalizationContext: ['groups' => ['tiempo:read']],
    denormalizationContext: ['groups' => ['tiempo:write']],
)]
//#[ApiResource(provider: TiempoGetProvider::class)]
/*#[Get(controller: TiempoController::class)]*/
class Tiempo
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Groups(['tiempo:read', 'tiempo:write','user:read'])]
    private ?\DateTimeInterface $inicio = null;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'tiempos')]
    #[Groups(['tiempo:read', 'tiempo:write','user:read'])]
    private User $user;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Groups(['tiempo:read', 'tiempo:write','user:read'])]
    private ?\DateTimeInterface $fin = null;
    #[MinimalLenght]
    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['tiempo:write','user:read'])]
    private ?string $descripcion = null;



    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user_id): void
    {
        $this->user = $user_id;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getInicio(): ?\DateTimeInterface
    {
        return $this->inicio;
    }

    public function setInicio(\DateTimeInterface $inicio): static
    {
        $this->inicio = $inicio;

        return $this;
    }

    public function getFin(): ?\DateTimeInterface
    {
        return $this->fin;
    }

    public function setFin(\DateTimeInterface $fin): static
    {
        $this->fin = $fin;

        return $this;
    }

    public function getDescripcion(): ?string
    {
        return $this->descripcion;
    }

    public function setDescripcion(?string $descripcion): static
    {
        $this->descripcion = $descripcion;

        return $this;
    }
}
