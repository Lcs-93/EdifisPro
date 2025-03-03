<?php

namespace App\Entity;

use App\Repository\ChantierRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ChantierRepository::class)]
class Chantier
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $lieu = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $dateDebut = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $dateFin = null;

    #[ORM\Column(length: 255)]
    private ?string $status = null;

    /**
     * @var Collection<int, CompetenceChantier>
     */
    #[ORM\OneToMany(targetEntity: CompetenceChantier::class, mappedBy: 'chantier')]
    private Collection $competenceChantiers;

    /**
     * @var Collection<int, Affectation>
     */
    #[ORM\OneToMany(targetEntity: Affectation::class, mappedBy: 'chantier')]
    private Collection $affectations;

    public function __construct()
    {
        $this->competenceChantiers = new ArrayCollection();
        $this->affectations = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLieu(): ?string
    {
        return $this->lieu;
    }

    public function setLieu(string $lieu): static
    {
        $this->lieu = $lieu;

        return $this;
    }

    public function getDateDebut(): ?\DateTimeInterface
    {
        return $this->dateDebut;
    }

    public function setDateDebut(\DateTimeInterface $dateDebut): static
    {
        $this->dateDebut = $dateDebut;

        return $this;
    }

    public function getDateFin(): ?\DateTimeInterface
    {
        return $this->dateFin;
    }

    public function setDateFin(\DateTimeInterface $dateFin): static
    {
        $this->dateFin = $dateFin;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;

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
            $competenceChantier->setChantier($this);
        }

        return $this;
    }

    public function removeCompetenceChantier(CompetenceChantier $competenceChantier): static
    {
        if ($this->competenceChantiers->removeElement($competenceChantier)) {
            // set the owning side to null (unless already changed)
            if ($competenceChantier->getChantier() === $this) {
                $competenceChantier->setChantier(null);
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
            $affectation->setChantier($this);
        }

        return $this;
    }

    public function removeAffectation(Affectation $affectation): static
    {
        if ($this->affectations->removeElement($affectation)) {
            // set the owning side to null (unless already changed)
            if ($affectation->getChantier() === $this) {
                $affectation->setChantier(null);
            }
        }

        return $this;
    }
}
