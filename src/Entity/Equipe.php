<?php

namespace App\Entity;

use App\Repository\EquipeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

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

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Assert\NotNull(message: 'La date de début est obligatoire.')]
    private ?\DateTimeInterface $dateDebut = null;
    
    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Assert\NotNull(message: 'La date de fin est obligatoire.')]
    private ?\DateTimeInterface $dateFin = null;
    
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
    public static function loadValidatorMetadata(ClassMetadata $metadata)
{
    $metadata->addConstraint(new Assert\Callback('validateDates'));
}

public function validateDates(ExecutionContextInterface $context)
{
    if ($this->dateDebut && $this->dateFin) {
        if ($this->dateFin <= $this->dateDebut) {
            $context->buildViolation('La date de fin doit être postérieure à la date de début.')
                ->atPath('dateFin')
                ->addViolation();
        }
    }
}


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
