<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
class User
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $nom = null;

    #[ORM\Column(length: 255)]
    private ?string $prenom = null;

    #[ORM\Column(length: 255)]
    private ?string $email = null;

    #[ORM\Column(length: 255)]
    private ?string $password = null;

    #[ORM\ManyToOne(inversedBy: 'users')]
    private ?Role $role = null;

    /**
     * @var Collection<int, CompetenceUser>
     */
    #[ORM\OneToMany(targetEntity: CompetenceUser::class, mappedBy: 'utilisateur')]
    private Collection $competenceUsers;

    /**
     * @var Collection<int, Equipe>
     */
    #[ORM\OneToMany(targetEntity: Equipe::class, mappedBy: 'chefEquipe')]
    private Collection $equipes;

    /**
     * @var Collection<int, EquipeUser>
     */
    #[ORM\OneToMany(targetEntity: EquipeUser::class, mappedBy: 'utilisateur')]
    private Collection $equipeUsers;

    public function __construct()
    {
        $this->competenceUsers = new ArrayCollection();
        $this->equipes = new ArrayCollection();
        $this->equipeUsers = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): static
    {
        $this->nom = $nom;

        return $this;
    }

    public function getPrenom(): ?string
    {
        return $this->prenom;
    }

    public function setPrenom(string $prenom): static
    {
        $this->prenom = $prenom;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    public function getRole(): ?Role
    {
        return $this->role;
    }

    public function setRole(?Role $role): static
    {
        $this->role = $role;

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
            $competenceUser->setUtilisateur($this);
        }

        return $this;
    }

    public function removeCompetenceUser(CompetenceUser $competenceUser): static
    {
        if ($this->competenceUsers->removeElement($competenceUser)) {
            // set the owning side to null (unless already changed)
            if ($competenceUser->getUtilisateur() === $this) {
                $competenceUser->setUtilisateur(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Equipe>
     */
    public function getEquipes(): Collection
    {
        return $this->equipes;
    }

    public function addEquipe(Equipe $equipe): static
    {
        if (!$this->equipes->contains($equipe)) {
            $this->equipes->add($equipe);
            $equipe->setChefEquipe($this);
        }

        return $this;
    }

    public function removeEquipe(Equipe $equipe): static
    {
        if ($this->equipes->removeElement($equipe)) {
            // set the owning side to null (unless already changed)
            if ($equipe->getChefEquipe() === $this) {
                $equipe->setChefEquipe(null);
            }
        }

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
            $equipeUser->setUtilisateur($this);
        }

        return $this;
    }

    public function removeEquipeUser(EquipeUser $equipeUser): static
    {
        if ($this->equipeUsers->removeElement($equipeUser)) {
            // set the owning side to null (unless already changed)
            if ($equipeUser->getUtilisateur() === $this) {
                $equipeUser->setUtilisateur(null);
            }
        }

        return $this;
    }
}
