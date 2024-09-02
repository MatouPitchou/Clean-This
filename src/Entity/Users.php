<?php
/*
*
*   @author: Dylan Rohart, Mathilde Breux
*
*/


namespace App\Entity;

use App\Repository\UsersRepository;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Mapping\ClassMetadata;

#[ORM\Entity(repositoryClass: UsersRepository::class)]
#[UniqueEntity(fields: ['email'], message: 'There is already an account with this email')]
class Users implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180, unique: true)]
    #[Assert\Regex(
        pattern: '/^([a-zA-Z0-9._%-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,})$/',        
        match: true,        
        message: 'Entrez une adresse mail correcte',

    )]
    private ?string $email = null;

    /**
     * @var list<string> The user roles
     */
    #[ORM\Column]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column (nullable: true)]
    private ?string $password = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $google_id = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $lastname = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $firstname = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $phone = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $zipcode = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $city = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $street = null;

    #[ORM\Column]
    private ?bool $is_verified = false;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $activeAt = null;

    #[ORM\ManyToMany(targetEntity: Operations::class, mappedBy: 'user_id')]
    private Collection $operations;


    // #[ORM\OneToOne(targetEntity: ResetPasswordRequest::class, mappedBy: 'user', cascade: ['remove'])]
    // private ?ResetPasswordRequest $resetPasswordRequest = null;

    public function __construct()
    {
        $this->operations = new ArrayCollection();
        $this->createdAt = new DateTimeImmutable();
        $this->roles = ['ROLE_USER'];
    }

    public function __toString()
    {
        return $this->getFullName();
    }
    
    public function getFullName()
    {
        return $this->getLastname().' '.$this->getFirstname();
    }   

    public function getFullAddress()
    {
        return $this->getCity().', '.$this->getStreet();
    }
   
    public static function loadValidatorMetadata(ClassMetadata $metadata): void
    {
        $metadata->addPropertyConstraint('password', new Assert\Length([
            'min' => 6,
            'max' => 200,
            'minMessage' => 'Le mot de passe doit faire au moins {{ limit }} caractères',
            'maxMessage' => 'Le mot de passe ne peut pas faire plus de {{ limit }} caractères',
        ]));
    }

    public function getId(): ?int
    {
        return $this->id;
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

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->getFullName();
    }

    /**
     * @see UserInterface
     *
     * @return list<string>
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    /**
     * @param list<string> $roles
     */
    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getGoogleId(): ?string
    {
        return $this->google_id;
    }

    public function setGoogleId(?string $google_id): static
    {
        $this->google_id = $google_id;

        return $this;
    }

    public function getLastname(): ?string
    {
        return $this->lastname;
    }

    public function setLastname(?string $lastname): static
    {
        $this->lastname = $lastname;

        return $this;
    }

    public function getFirstname(): ?string
    {
        return $this->firstname;
    }

    public function setFirstname(?string $firstname): static
    {
        $this->firstname = $firstname;

        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(?string $phone): static
    {
        $this->phone = $phone;

        return $this;
    }

    public function getZipcode(): ?string
    {
        return $this->zipcode;
    }

    public function setZipcode(?string $zipcode): static
    {
        $this->zipcode = $zipcode;

        return $this;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(?string $city): static
    {
        $this->city = $city;

        return $this;
    }

    public function getStreet(): ?string
    {
        return $this->street;
    }

    public function setStreet(?string $street): static
    {
        $this->street = $street;

        return $this;
    }


    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getActiveAt(): ?\DateTimeInterface
    {
        return $this->activeAt;
    }

    public function setActiveAt(?\DateTimeInterface $activeAt): static
    {
        $this->activeAt = $activeAt;

        return $this;
    }

    /**
     * @return Collection<int, Operations>
     */
    public function getOperations(): Collection
    {
        return $this->operations;
    }

    public function addOperation(Operations $operation): static
    {
        if (!$this->operations->contains($operation)) {
            $this->operations->add($operation);
            $operation->addUserId($this);
        }

        return $this;
    }

    public function removeOperation(Operations $operation): static
    {
        if ($this->operations->removeElement($operation)) {
            $operation->removeUserId($this);
        }

        return $this;
    }
    /**
     * Get the value of is_verified
     */ 
    public function getIs_verified(): ?bool
    {
        return $this->is_verified;
    }

    /**
     * Set the value of is_verified
     *
     * 
     */ 
    public function setIs_verified(bool $is_verified): self
    {
        $this->is_verified = $is_verified;

        return $this;
    }
    
    /**
     * @var string|null
     */
    private $generatedPassword = null;

    public function getGeneratedPassword(): ?string
    {
        return $this->generatedPassword;
    }

    public function setGeneratedPassword(?string $generatedPassword): self
    {
        $this->generatedPassword = $generatedPassword;

        return $this;
    }

    // public function getResetPasswordRequest(): ?ResetPasswordRequest
    // {
    //     return $this->resetPasswordRequest;
    // }

    // public function setResetPasswordRequest(?ResetPasswordRequest $resetPasswordRequest): self
    // {
    //     $this->resetPasswordRequest = $resetPasswordRequest;
    //     return $this;
    // }

}
