<?php

namespace App\Entity;

use App\Repository\EquipeUserRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

#[ORM\Entity(repositoryClass: EquipeUserRepository::class)]
class EquipeUser
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'equipeUsers')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $utilisateur = null;

    #[ORM\ManyToOne(inversedBy: 'equipeUsers')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Equipe $equipe = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $dateDebut = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $dateFin = null;

    #[Assert\Callback]
    public function validateAffectation(ExecutionContextInterface $context)
    {
        $utilisateur = $this->getUtilisateur();
        $dateDebut = $this->getDateDebut();
        $dateFin = $this->getDateFin();

        if (!$utilisateur || !$dateDebut || !$dateFin) {
            return; // On évite les erreurs si des valeurs sont nulles
        }

        foreach ($utilisateur->getEquipeUsers() as $affectationExistante) {
            $autreEquipe = $affectationExistante->getEquipe();
            $autreDebut = $affectationExistante->getDateDebut();
            $autreFin = $affectationExistante->getDateFin();

            // Vérifier si c'est la même équipe (ne pas bloquer l'édition)
            if ($autreEquipe !== $this->getEquipe()) {
                // Vérification stricte du chevauchement
                if (
                    ($dateDebut < $autreFin && $dateFin > $autreDebut)  // Vrai chevauchement
                ) {
                    $context->buildViolation("L'utilisateur {$utilisateur->getNom()} est déjà affecté à une autre équipe entre {$autreDebut->format('d/m/Y')} et {$autreFin->format('d/m/Y')}.")
                        ->atPath('utilisateur')
                        ->addViolation();

                    return;
                }
            }
        }
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUtilisateur(): ?User
    {
        return $this->utilisateur;
    }

    public function setUtilisateur(?User $utilisateur): static
    {
        $this->utilisateur = $utilisateur;
        return $this;
    }

    public function getEquipe(): ?Equipe
    {
        return $this->equipe;
    }

    public function setEquipe(?Equipe $equipe): static
    {
        $this->equipe = $equipe;
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
}
