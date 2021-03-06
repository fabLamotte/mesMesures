<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @ORM\Entity(repositoryClass=UserRepository::class)
 */
class User implements UserInterface
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=180, unique=true)
     */
    private $email;

    /**
     * @ORM\Column(type="json")
     */
    private $roles = [];

    /**
     * @var string The hashed password
     * @ORM\Column(type="string")
     */
    private $password;


    /**
     * @ORM\Column(type="boolean")
     */
    private $password_change_asked;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $firstname;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $lastname;

    /**
     * @ORM\Column(type="datetime")
     */
    private $date_naissance;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $image_profil;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $tokenRecover;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $weightGoal;

    /**
     * @ORM\ManyToOne(targetEntity=Sexe::class, inversedBy="users")
     */
    private $sexe;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $age;

    /**
     * @ORM\ManyToOne(targetEntity=ProfilSportif::class, inversedBy="users")
     */
    private $profilSportif;

    public function __construct(){
        $this->password_change_asked = false;
        $this->image_profil = null;
        $this->tokenRecover = "";
        $this->weightGoal = null;
        $this->sexe = null;
        $this->age = null;
        $this->profilSportif = null;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUsername(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getPassword(): string
    {
        return (string) $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getSalt()
    {
        // not needed when using the "bcrypt" algorithm in security.yaml
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getPasswordChangeAsked(): ?bool
    {
        return $this->password_change_asked;
    }

    public function setPasswordChangeAsked(bool $password_change_asked): self
    {
        $this->password_change_asked = $password_change_asked;

        return $this;
    }

    public function getFirstname(): ?string
    {
        return $this->firstname;
    }

    public function setFirstname(string $firstname): self
    {
        $this->firstname = $firstname;

        return $this;
    }

    public function getLastname(): ?string
    {
        return $this->lastname;
    }

    public function setLastname(string $lastname): self
    {
        $this->lastname = $lastname;

        return $this;
    }

    public function getDateNaissance(): ?\DateTimeInterface
    {
        return $this->date_naissance;
    }

    public function setDateNaissance(\DateTimeInterface $date_naissance): self
    {
        $this->date_naissance = $date_naissance;

        return $this;
    }
    
    public function getImageProfil(): ?string
    {
        return $this->image_profil;
    }

    public function setImageProfil(?string $image_profil): self
    {
        $this->image_profil = $image_profil;

        return $this;
    }

    public function getTokenRecover(): ?string
    {
        return $this->tokenRecover;
    }

    public function setTokenRecover(?string $tokenRecover): self
    {
        $this->tokenRecover = $tokenRecover;

        return $this;
    }

    public function getWeightGoal(): ?string
    {
        return $this->weightGoal;
    }

    public function setWeightGoal(?string $weightGoal): self
    {
        $this->weightGoal = $weightGoal;

        return $this;
    }

    public function getSexe(): ?Sexe
    {
        return $this->sexe;
    }

    public function setSexe(?Sexe $sexe): self
    {
        $this->sexe = $sexe;

        return $this;
    }

    public function getAge(): ?string
    {
        return $this->age;
    }

    public function setAge(string $age): self
    {
        $this->age = $age;

        return $this;
    }

    public function getProfilSportif(): ?ProfilSportif
    {
        return $this->profilSportif;
    }

    public function setProfilSportif(?ProfilSportif $profilSportif): self
    {
        $this->profilSportif = $profilSportif;

        return $this;
    }
}
