<?php declare(strict_types=1);
/**
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     https://github.com/GuildLaDanse
 */

namespace App\Infrastructure\Authorization\Policies;

use App\Infrastructure\Authorization\PolicySet;
use App\Core\Modules\Activity\ActivityType;

class PolicyCatalog
{
    /**
     * @var array
     */
    private array $topPolicies;

    public function __construct()
    {
        $this->initPolicies();
    }

    public function getPolicies()
    {
        return $this->topPolicies;
    }

    private function initPolicies()
    {
        $this->topPolicies = [];

        $this->topPolicies[] = new PolicySet(
            'Create Event Policy Set',
            ActivityType::EVENT_CREATE,
            [
                new AllCanCreateEventWhenOrganiser(),
                new OfficerCanCreateEventRule()
            ]
        );

        $this->topPolicies[] = new PolicySet(
            'Edit Event Policy Set',
            ActivityType::EVENT_EDIT,
            [
                new CreatorCanEditEventRule(),
                new OfficerCanEditEventRule()
            ]
        );

        $this->topPolicies[] = new PolicySet(
            'Edit Event Policy Set',
            ActivityType::EVENT_DELETE,
            [
                new CreatorCanEditEventRule(),
                new OfficerCanEditEventRule()
            ]
        );

        $this->topPolicies[] = new PolicySet(
            'Create Sign-Up Policy Set',
            ActivityType::SIGNUP_CREATE,
            [
                new AllowAllRule()
            ]
        );

        $this->topPolicies[] = new PolicySet(
            'Edit Sign-Up Policy Set',
            ActivityType::SIGNUP_EDIT,
            [
                new CreatorCanEditSignUpRule(),
                new OfficerCanEditSignUpRule()
            ]
        );

        $this->topPolicies[] = new PolicySet(
            'Delete Sign-Up Policy Set',
            ActivityType::SIGNUP_DELETE,
            array(
                new CreatorCanEditSignUpRule(),
                new OfficerCanEditSignUpRule()
            )
        );

        $this->topPolicies[] = new PolicySet(
            'Event State Change Policy Set',
            ActivityType::EVENT_PUT_STATE,
            [
                new CreatorCanChangeEventStateRule()
            ]
        );

        $this->topPolicies[] = new PolicySet(
            'Game Data Policy Set - Realm',
            ActivityType::REALM_CREATE,
            [
                new AllCanCreateGameDataRule()
            ]
        );

        $this->topPolicies[] = new PolicySet(
            'Game Data Policy Set - Guild',
            ActivityType::GUILD_CREATE,
            array(
                new AllCanCreateGameDataRule()
            )
        );

        $this->topPolicies[] = new PolicySet(
            'Event - List',
            ActivityType::EVENT_LIST,
            [
                new AllowAllRule()
            ]
        );

        $this->topPolicies[] = new PolicySet(
            'Event - View Single',
            ActivityType::EVENT_VIEW,
            [
                new AllowAllRule()
            ]
        );

        $this->topPolicies[] = new PolicySet(
            'Character Set - Claim',
            ActivityType::CLAIM_EDIT,
            [
                new ClaimerCanEditClaimRule(),
                new AllowCommandRule()
            ]
        );

        $this->topPolicies[] = new PolicySet(
            'Character Set - Claim',
            ActivityType::CLAIM_REMOVE,
            [
                new ClaimerCanEditClaimRule()
            ]
        );

        $this->topPolicies[] = new PolicySet(
        'Discord Access - Auth Code',
        ActivityType::AUTHZ_DISCORD_REQUEST_AUTHCODE,
        [
            new SelfCanRequestAuthCode()
        ]);

        $this->topPolicies[] = new PolicySet(
        'Discord Access - Connect Status',
        ActivityType::AUTHZ_DISCORD_CONNECT_STATUS,
        [
            new SelfCanRequestDiscordConnectStatus()
        ]);

        $this->topPolicies[] = new PolicySet(
        'Discord Access - Disconnect',
        ActivityType::AUTHZ_DISCORD_DISCONNECT,
        [
            new SelfCanDisconnectDiscord()
        ]);
    }
}