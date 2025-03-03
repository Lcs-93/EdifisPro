<?php

namespace App\Entity;

use App\Repository\EquipeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: EquipeRepository::class)]
class Equipe
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $nomEquipe = null;

    #[ORM\ManyToOne(inversedBy: 'equipes')]
    private ?User $chefEquipe = null;

    /**
     * @var Collection<int, EquipeUser>
     */
    #[ORM\OneToMany(targetEntity: EquipeUser::class, mappedBy: 'equipe')]
    private Collection $equipeUsers;

    /**
     * @var Collection<int, Affectation>
     */
    #[ORM\OneToMany(targetEntity: Affectation::class, mappedBy: 'equipe')]
    private Collection $affectations;

    public function __construct()
    {
        $this->equipeUsers = new ArrayCollection();
        $this->affectations = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNomEquipe(): ?string
    {
        return $this->nomEquipe;
    }

    public function setNomEquipe(string $nomEquipe): static
    {
        $this->nomEquipe = $nomEquipe;

        return $this;
    }

    public function getChefEquipe(): ?User
    {
        return $this->chefEquipe;
    }

    public function setChefEquipe(?User $chefEquipe): static
    {
        $this->chefEquipe = $chefEquipe;

        return $this;
    }

    /**
     * @return Collection<int, EquipeUser>
     */
    public function getEquipeUsers(): Collection
    {
        return $this->equipeUsers;
    }

    public function addEquipeUser(EquipeUser $equipeUser): static
    {
        if (!$this->equipeUsers->contains($equipeUser)) {
            $this->equipeUsers->add($equipeUser);
            $equipeUser->setEquipe($this);
        }

        return $this;
    }

    public function removeEquipeUser(EquipeUser $equipeUser): static
    {
        if ($this->equipeUsers->removeElement($equipeUser)) {
            // set the owning side to null (unless already changed)
            if ($equipeUser->getEquipe() === $this) {
                $equipeUser->setEquipe(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Affectation>
     */
    public function getAffectations(): Collection
    {
        return $this->affectations;
    }

    public function addAffectation(Affectation $affectation): static
    {
        if (!$this->affectations->contains($affectation)) {
            $this->affectations->add($affectation);
            $affectation->setEquipe($this);
        }

        return $this;
    }

    public function removeAffectation(Affectation $affectation): static
    {
        if ($this->affectations->removeElement($affectation)) {
            // set the owning side to null (unless already changed)
            if ($affectation->getEquipe() === $this) {
                $affectation->setEquipe(null);
            }
        }

        return $this;
    }
}
