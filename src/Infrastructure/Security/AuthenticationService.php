<?php declare(strict_types=1);
/**
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     https://github.com/GuildLaDanse
 */

namespace App\Infrastructure\Security;

class AuthenticationService
{
    /**
     * @var AuthenticationContext
     */
    private AuthenticationContext $authenticationContext;

    /**
     * @param AuthenticationContext $authenticationContext
     */
    public function __construct(AuthenticationContext $authenticationContext)
    {
        $this->authenticationContext = $authenticationContext;
    }

    /**
     * @return AuthenticationContext
     */
    public function getCurrentContext(): AuthenticationContext
    {
        return $this->authenticationContext;
    }
}
