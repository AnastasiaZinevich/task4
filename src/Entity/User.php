<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
/**
 * @ORM\Entity
 * @UniqueEntity(fields="email", message="There is already an account with this email")
 */
#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_USERNAME', fields: ['username'])]
#[UniqueEntity(fields: ['username'], message: 'There is already an account with this username')]
#[UniqueEntity(fields: ['email'], message: 'There is already an account with this email')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180)]
    private ?string $username = null;

    #[ORM\Column(nullable: true)]
    private ?string $email = null;

    #[ORM\Column]
    private ?string $password = null;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    #[ORM\Column]
    private $lastLogin;
   
 /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    #[ORM\Column]
    private $registrationDate;


    #[ORM\Column]
    private $state;
    /**
     * @var list<string> The user roles
     */
    #[ORM\Column]
    private array $roles = [];

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): self
    {
        $this->email = $email;
        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    public function getUserIdentifier(): string
    {
        return (string) $this->username;
    }

    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    public function getSalt(): ?string
    {
        return null;
    }

    public function eraseCredentials()
    {
        // Метод должен быть пустым, так как не используется хеширование пароля
    }

     /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    

     public function updateLastLoginTimestamp(): void
     {
         $this->lastLogin = new \DateTime();
     }
     
     public function getLastLogin(): ?\DateTimeInterface
     {
         return $this->lastLogin ? new \DateTime($this->lastLogin) : null;
     }
     
     /**
      * @param \DateTimeInterface $lastLogin
      */
     public function setLastLogin(\DateTimeInterface $lastLogin): void
     {
         $this->lastLogin = $lastLogin;
     }
    
 
     
     public function getRegistrationDate(): ?\DateTimeInterface
    {
        // If $registrationDate is already a DateTime object, return it
        if ($this->registrationDate instanceof \DateTimeInterface) {
            return $this->registrationDate;
        }

        // If $registrationDate is a string, convert it to a DateTime object
        if (is_string($this->registrationDate)) {
            return new \DateTime($this->registrationDate);
        }

        // If $registrationDate is null or of an unexpected type, return null
        return null;
    }

    public function setRegistrationDate($registrationDate): self
    {
        if (is_string($registrationDate)) {
            $registrationDate = new \DateTime($registrationDate);
        }

        $this->registrationDate = $registrationDate;
        return $this;
    }
    public function setRegistrationDateу(\DateTime $registrationDate): self
    {
        $this->registrationDate = $registrationDate->format('Y-m-d H:i:s');
        return $this;
    }
   
    public function getRegistrationDateу(): ?\DateTimeInterface
    {
        return $this->registrationDate;
    }
    public function getStatus(): ?string
    {
        return $this->state;
    }

    public function setStatus(string $state): self
    {
        $this->state = $state;

        return $this;
    }
    public function getState(): ?string
    {
        return $this->state;
    }

    public function setState(string $state): self
    {
        $this->state = $state;
        return $this;
    }
}
