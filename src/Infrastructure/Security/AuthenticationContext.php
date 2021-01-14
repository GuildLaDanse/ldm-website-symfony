<?php declare(strict_types=1);
/**
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     https://github.com/GuildLaDanse
 */

namespace App\Infrastructure\Security;

use App\Domain\Entity\Account\Account;

use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class AuthenticationContext
{
    /**
     * @var TokenStorageInterface
     */
    private TokenStorageInterface $tokenStorage;

    /**
     * @param TokenStorageInterface $tokenStorage
     */
    public function __construct(TokenStorageInterface $tokenStorage)
    {
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * @return bool
     */
    public function isAuthenticated(): bool
    {
        return !$this->getAccount()->isAnonymous();
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        if ($this->isAuthenticated())
        {
            return $this->tokenStorage->getToken() !== null ? $this->tokenStorage->getToken()->getUser()->getId() : -1;
        }

        return -1;
    }

    /**
     * @return Account|null
     */
    public function getAccount(): ?Account
    {
        /** @var Account $account */
        $account = $this->tokenStorage->getToken() !== null ? $this->tokenStorage->getToken()->getUser() : null;

        return $account;
    }
}
