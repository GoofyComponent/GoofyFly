<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
#[UniqueEntity(fields: ['email'], message: 'There is already an account with this email')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180, unique: true)]
    private ?string $email = null;

    #[ORM\Column]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    private ?string $password = null;

    #[ORM\Column(length: 30)]
    private ?string $firstname = null;

    #[ORM\Column(length: 50)]
    private ?string $lastname = null;

    #[ORM\OneToOne(mappedBy: 'User', cascade: ['persist', 'remove'])]
    private ?FtpCredentials $ftpCredentials = null;

    #[ORM\OneToOne(mappedBy: 'user', cascade: ['persist', 'remove'])]
    private ?SshCredentials $sshCredentials = null;

    #[ORM\OneToOne(mappedBy: 'user', cascade: ['persist', 'remove'])]
    private ?MysqlCredentials $mysqlCredentials = null;

    #[ORM\OneToOne(mappedBy: 'User', cascade: ['persist', 'remove'])]
    private ?LinuxCredentials $linuxCredentials = null;

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
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     */
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

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
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

    public function getFtpCredentials(): ?FtpCredentials
    {
        return $this->ftpCredentials;
    }

    public function setFtpCredentials(FtpCredentials $ftpCredentials): self
    {
        // set the owning side of the relation if necessary
        if ($ftpCredentials->getUser() !== $this) {
            $ftpCredentials->setUser($this);
        }

        $this->ftpCredentials = $ftpCredentials;

        return $this;
    }

    public function getSshCredentials(): ?SshCredentials
    {
        return $this->sshCredentials;
    }

    public function setSshCredentials(SshCredentials $sshCredentials): self
    {
        // set the owning side of the relation if necessary
        if ($sshCredentials->getUser() !== $this) {
            $sshCredentials->setUser($this);
        }

        $this->sshCredentials = $sshCredentials;

        return $this;
    }

    public function getMysqlCredentials(): ?MysqlCredentials
    {
        return $this->mysqlCredentials;
    }

    public function setMysqlCredentials(MysqlCredentials $mysqlCredentials): self
    {
        // set the owning side of the relation if necessary
        if ($mysqlCredentials->getUser() !== $this) {
            $mysqlCredentials->setUser($this);
        }

        $this->mysqlCredentials = $mysqlCredentials;

        return $this;
    }

    public function getLinuxCredentials(): ?LinuxCredentials
    {
        return $this->linuxCredentials;
    }

    public function setLinuxCredentials(LinuxCredentials $linuxCredentials): self
    {
        // set the owning side of the relation if necessary
        if ($linuxCredentials->getUser() !== $this) {
            $linuxCredentials->setUser($this);
        }

        $this->linuxCredentials = $linuxCredentials;

        return $this;
    }
}
