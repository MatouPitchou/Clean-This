<?php
/**
 *  @author Jérémy <jeremydecreton@live.fr>
 * 
 */
namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\OperationsRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

#[ORM\Entity(repositoryClass: OperationsRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Operations
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\ManyToMany(targetEntity: Users::class, inversedBy: 'operations')]
    private Collection $user_id;

    #[ORM\ManyToMany(targetEntity: Users::class, inversedBy: 'operations')]
    #[ORM\JoinTable(name: 'operations_users')]
    private Collection $users;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $description = null;

    #[ORM\Column(length: 255, nullable:true)]
    private ?string $status = null;

    #[ORM\Column(length: 255)]
    private ?string $quote = null;

    #[ORM\Column(length: 255)]
    private ?string $zipcode = null;

    #[ORM\Column(length: 255)]
    private ?string $city = null;

    #[ORM\Column(length: 255)]
    private ?string $street = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $finishedAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: false)]
    private ?\DateTimeInterface $lastModifiedAt = null;

    #[ORM\ManyToOne(inversedBy: 'operations')]
    private ?Services $services = null;

    #[ORM\Column(nullable: false)]
    private ?float $surface = null;

    #[ORM\OneToOne(targetEntity: Invoices::class, inversedBy: 'operation', cascade: ['persist'])]
    #[ORM\JoinColumn(nullable: true)]
    private ?Invoices $invoices = null;

    #[ORM\Column(nullable: true)]
    private ?float $price = null;

// Ajouter les valeurs par défaut d'une opération à la création
    #[ORM\PrePersist]
   public function onPrePersist()
   {
       $this->createdAt = new \DateTimeImmutable("now");
       $this->lastModifiedAt = new \DateTime("now");
       $this->finishedAt = null;
       $this->status = null;
       $this->quote = "Estimation";
       $this->services = null;

   }

// Changer le "lastModifiedAt" quand l'opération est MAJ
    #[ORM\PreUpdate]
   public function onPreUpdate()
   {
        $this->lastModifiedAt = new \DateTime("now");
        if ($this->services !== null) 
        {
            //Je récupère le service, si service "custom", prix prend la valeur de l'input du form autrement, prend le prix DB
            $type = $this->services->getType();
            if($type == 'Custom')
            {   
                if ($this->price == "") 
                {
                    throw new \Exception("Le prix ne peut pas être nul.");
                }
                else
                {
                $this->price = $this->price;
                }
            } 
            elseif ($this->services) 
            {
                $this->price = $this->services->getPrice();
            }
            //Je teste le status du devis
            if($this->quote == "Estimation"){
            //Je change le status du devis pour attendre la validation du client
            $this->quote = "Validation";
            } //si status devis est validé par le client, le status de l'opération devient disponible
            elseif ($this->quote == "Validé") 
            {
                $this->status == "Disponible";
            }

            if ($this->status == "Terminée") {
                $this->finishedAt = new \DateTime("now");
            }

        }
    }

//Construct
    public function __construct()
    {
        $this->user_id = new ArrayCollection();
        $this->users = new ArrayCollection();

    }

//Getter & Setter
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return Collection<int, Users>
     */
    public function getUserId(): Collection
    {
        return $this->user_id;
    }

        /**
     * @return Collection<int, User>
     */
    public function getUsers(): Collection
    {
        return $this->users;
    }

    public function addUserId(Users $userId): static
    {
        if (!$this->user_id->contains($userId)) {
            $this->user_id->add($userId);
        }

        return $this;
    }

    public function removeUserId(Users $userId): static
    {
        $this->user_id->removeElement($userId);

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

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

    public function getQuote(): ?string
    {
        return $this->quote;
    }

    public function setQuote(string $quote): static
    {
        $this->quote = $quote;

        return $this;
    }

    public function getZipcode(): ?string
    {
        return $this->zipcode;
    }

    public function setZipcode(string $zipcode): static
    {
        $this->zipcode = $zipcode;

        return $this;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(string $city): static
    {
        $this->city = $city;

        return $this;
    }

    public function getStreet(): ?string
    {
        return $this->street;
    }

    public function setStreet(string $street): static
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

    public function getFinishedAt(): ?\DateTimeInterface
    {
        return $this->finishedAt;
    }

    public function setFinishedAt(?\DateTimeInterface $finishedAt): static
    {
        $this->finishedAt = $finishedAt;

        return $this;
    }

    public function getLastModifiedAt(): ?\DateTimeInterface
    {
        return $this->lastModifiedAt;
    }

    public function setLastModifiedAt(?\DateTimeInterface $lastModifiedAt): static
    {
        $this->lastModifiedAt = $lastModifiedAt;

        return $this;
    }

    public function getServices(): ?Services
    {
        return $this->services;
    }

    public function setServices(?Services $services): static
    {
        $this->services = $services;

        return $this;
    }

    public function getSurface(): ?float
    {
        return $this->surface;
    }

    public function setSurface(?float $surface): static
    {
        $this->surface = $surface;

        return $this;
    }

    public function getInvoices(): ?Invoices
    {
        return $this->invoices;
    }

    public function setInvoices(Invoices $invoices): static
    {
        // set the owning side of the relation if necessary
        if ($invoices->getOperation() !== $this) {
            $invoices->setOperation($this);
        }

        $this->invoices = $invoices;

        return $this;
    }


    public function getPrice(): ?float
    {
        return $this->price;
    }

    public function setPrice(?float $price): static
    {
        $this->price = $price;

        return $this;
    }

    public function getEmployeName(): string {
        $userNameKey = 'badge_aattribuer_label'; // Utilisez une clé de traduction
        $users = $this->users;
    
        foreach ($users as $user) {
            $userRole = $user->getRoles();
            if ($userRole[0] != "ROLE_USER") {
               $userName = $user->getFirstname();
               return $userName; // Retourne le prénom directement si trouvé
            }
        }
        return $userNameKey; // Retourne la clé de traduction si aucun nom n'est trouvé
    }
}    