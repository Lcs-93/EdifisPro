<?php

namespace App\Entity;

use App\Repository\CompetenceRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CompetenceRepository::class)]
class Competence
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $nomCompetence = null;

    /**
     * @var Collection<int, CompetenceUser>
     */
    #[ORM\OneToMany(targetEntity: CompetenceUser::class, mappedBy: 'competence')]
    private Collection $competenceUsers;

    /**
     * @var Collection<int, CompetenceChantier>
     */
    #[ORM\OneToMany(targetEntity: CompetenceChantier::class, mappedBy: 'competence')]
    private Collection $competenceChantiers;

    public function __construct()
    {
        $this->competenceUsers = new ArrayCollection();
        $this->competenceChantiers = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNomCompetence(): ?string
    {
        return $this->nomCompetence;
    }

    public function setNomCompetence(string $nomCompetence): static
    {
        $this->nomCompetence = $nomCompetence;

        return $this;
    }

    /**
     * @return Collection<int, CompetenceUser>
     */
    public function getCompetenceUsers(): Collection
    {
        return $this->competenceUsers;
    }

    public function addCompetenceUser(CompetenceUser $competenceUser): static
    {
        if (!$this->competenceUsers->contains($competenceUser)) {
            $this->competenceUsers->add($competenceUser);
            $competenceUser->setCompetence($this);
        }

        return $this;
    }

    public function removeCompetenceUser(CompetenceUser $competenceUser): static
    {
        if ($this->competenceUsers->removeElement($competenceUser)) {
            // set the owning side to null (unless already changed)
            if ($competenceUser->getCompetence() === $this) {
                $competenceUser->setCompetence(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, CompetenceChantier>
     */
    public function getCompetenceChantiers(): Collection
    {
        return $this->competenceChantiers;
    }

    public function addCompetenceChantier(CompetenceChantier $competenceChantier): static
    {
        if (!$this->competenceChantiers->contains($competenceChantier)) {
            $this->competenceChantiers->add($competenceChantier);
            $competenceChantier->setCompetence($this);
        }

        return $this;
    }

    public function removeCompetenceChantier(CompetenceChantier $competenceChantier): static
    {
        if ($this->competenceChantiers->removeElement($competenceChantier)) {
            // set the owning side to null (unless already changed)
            if ($competenceChantier->getCompetence() === $this) {
                $competenceChantier->setCompetence(null);
            }
        }

        return $this;
    }
}
