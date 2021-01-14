<?php

namespace App\Domain\Entity\Account;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @ORM\Entity(repositoryClass="App\Domain\Repository\Account\AccountRepository")
 * @ORM\Table(name="Account")
 */
class Account implements UserInterface
{
    private const ROLE_DEFAULT = 'ROLE_USER';

    /**
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     *
     * @var int
     */
    private int $id;

    /**
     * @ORM\Column(name="username", type="string", length=180, nullable=false)
     *
     * @var string
     */
    private string $username;

    /**
     * @ORM\Column(name="username_canonical", type="string", length=180, nullable=false)
     *
     * @var string
     */
    private string $usernameCanonical;

    /**
     * @ORM\Column(name="email", type="string", length=180, nullable=false)
     *
     * @var string
     */
    private string $email;

    /**
     * @ORM\Column(name="email_canonical", type="string", length=180, nullable=false)
     *
     * @var string
     */
    private string $emailCanonical;

    /**
     * @ORM\Column(name="enabled", type="boolean", nullable=false)
     *
     * @var bool
     */
    private bool $enabled;

    /**
     * @ORM\Column(name="salt", type="string", length=255, nullable=true)
     *
     * @var ?string
     */
    private ?string $salt;

    /**
     * @ORM\Column(name="password", type="string", length=255, nullable=false)
     *
     * @var string
     */
    private string $password;

    /**
     * @ORM\Column(name="last_login", type="datetime", nullable=true)
     *
     * @var ?DateTime
     */
    private ?DateTime $lastLogin;

    /**
     * @ORM\Column(name="confirmation_token", type="string", length=180, nullable=true)
     *
     * @var ?string
     */
    private ?string $confirmationToken;

    /**
     * @ORM\Column(name="password_requested_at", type="datetime", nullable=true)
     *
     * @var ?DateTime
     */
    private ?DateTime $passwordRequestedAt;

    /**
     * @ORM\Column(name="roles", type="array", nullable=false)
     *
     * @var array
     */
    private array $roles;

    /**
     * @ORM\Column(name="displayName", type="string", length=32, nullable=false)
     *
     * @var string
     */
    private string $displayName;

    /**
     * Constructor
     */
    public function __construct()
    {
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return Account
     */
    public function setId(int $id): Account
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return string
     */
    public function getUsername(): string
    {
        return $this->username;
    }

    /**
     * @param string $username
     * @return Account
     */
    public function setUsername(string $username): Account
    {
        $this->username = $username;
        return $this;
    }

    /**
     * @return string
     */
    public function getUsernameCanonical(): string
    {
        return $this->usernameCanonical;
    }

    /**
     * @param string $usernameCanonical
     * @return Account
     */
    public function setUsernameCanonical(string $usernameCanonical): Account
    {
        $this->usernameCanonical = $usernameCanonical;
        return $this;
    }

    /**
     * @return string
     */
    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * @param string $email
     * @return Account
     */
    public function setEmail(string $email): Account
    {
        $this->email = $email;
        return $this;
    }

    /**
     * @return string
     */
    public function getEmailCanonical(): string
    {
        return $this->emailCanonical;
    }

    /**
     * @param string $emailCanonical
     * @return Account
     */
    public function setEmailCanonical(string $emailCanonical): Account
    {
        $this->emailCanonical = $emailCanonical;
        return $this;
    }

    /**
     * @return bool
     */
    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    /**
     * @param bool $enabled
     * @return Account
     */
    public function setEnabled(bool $enabled): Account
    {
        $this->enabled = $enabled;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getSalt(): ?string
    {
        return $this->salt;
    }

    /**
     * @param string|null $salt
     * @return Account
     */
    public function setSalt(?string $salt): Account
    {
        $this->salt = $salt;
        return $this;
    }

    /**
     * @return string
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    /**
     * @param string $password
     * @return Account
     */
    public function setPassword(string $password): Account
    {
        $this->password = $password;
        return $this;
    }

    /**
     * @return DateTime|null
     */
    public function getLastLogin(): ?DateTime
    {
        return $this->lastLogin;
    }

    /**
     * @param DateTime|null $lastLogin
     * @return Account
     */
    public function setLastLogin(?DateTime $lastLogin): Account
    {
        $this->lastLogin = $lastLogin;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getConfirmationToken(): ?string
    {
        return $this->confirmationToken;
    }

    /**
     * @param string|null $confirmationToken
     * @return Account
     */
    public function setConfirmationToken(?string $confirmationToken): Account
    {
        $this->confirmationToken = $confirmationToken;
        return $this;
    }

    /**
     * @return DateTime|null
     */
    public function getPasswordRequestedAt(): ?DateTime
    {
        return $this->passwordRequestedAt;
    }

    /**
     * @param DateTime|null $passwordRequestedAt
     * @return Account
     */
    public function setPasswordRequestedAt(?DateTime $passwordRequestedAt): Account
    {
        $this->passwordRequestedAt = $passwordRequestedAt;
        return $this;
    }

    /**
     * @return string
     */
    public function getDisplayName(): string
    {
        return $this->displayName;
    }

    /**
     * @param string $displayName
     * @return Account
     */
    public function setDisplayName(string $displayName): Account
    {
        $this->displayName = $displayName;
        return $this;
    }

    public function getRoles(): array
    {
        $roles = $this->roles;

        // we need to make sure to have at least one role
        $roles[] = self::ROLE_DEFAULT;

        return array_unique($roles);
    }

    public function addRole(string $role): Account
    {
        $role = strtoupper($role);

        if ($role === static::ROLE_DEFAULT)
        {
            return $this;
        }

        if (!in_array($role, $this->roles, true))
        {
            $this->roles[] = $role;
        }

        return $this;
    }

    public function hasRole(string $role): bool
    {
        return in_array(strtoupper($role), $this->getRoles(), true);
    }

    public function removeRole(string $role): Account
    {
        if (false !== $key = array_search(strtoupper($role), $this->roles, true))
        {
            unset($this->roles[$key]);
            $this->roles = array_values($this->roles);
        }

        return $this;
    }

    public function eraseCredentials(): void
    {
        // nothing to do, we do not store the plain password
    }
}
