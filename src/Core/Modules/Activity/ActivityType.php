<?php declare(strict_types=1);
/**
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     https://github.com/GuildLaDanse
 */

namespace App\Core\Modules\Activity;

/**
 * A list of constants to be used as values for ActivityEvent.type
 */
class ActivityType
{
    const CALENDAR_VIEW = 'Calendar.View'; // Read Only
    const CALENDAR_ICAL = 'Calendar.iCal'; // Read Only

    const EVENT_VIEW      = 'Event.View';     // Read Only
    const EVENT_EDIT      = 'Event.Edit';     // State Change
    const EVENT_DELETE    = 'Event.Delete';   // State Change
    const EVENT_CREATE    = 'Event.Create';   // State Change
    const EVENT_PUT_STATE = 'Event.PutState'; // State Change
    const EVENT_LIST      = 'Event.List';     // Read Only

    const EVENT_TODAY   = 'Event.Today';    // Read Only

    const SIGNUP_CREATE = 'Signup.Create'; // State Change
    const SIGNUP_EDIT   = 'Signup.Edit';   // State Change
    const SIGNUP_DELETE = 'Signup.Delete'; // State Change

    const CLAIMS_LIST   = 'Claims.List';   // Read Only

    const ABOUT_VIEW    = 'About.View';    // Read Only

    const ANGULAR_VIEW  = 'Angular.View';  // Read Only

    const GALLERY_VIEW  = 'Gallery.View';  // Read Only

    const CHARACTER_CREATE  = "Character.Create"; // State Change
    const CHARACTER_UPDATE  = "Character.Update"; // State Change
    const CHARACTER_REMOVE  = "Character.Remove"; // State Change
    const CHARACTER_TRACK   = "Character.Track"; // State Change
    const CHARACTER_UNTRACK = "Character.Untrack"; // State Change

    const CLAIM_VIEW    = 'Claim.View';   // Read Only
    const CLAIM_CREATE  = 'Claim.Create'; // State Change
    const CLAIM_EDIT    = 'Claim.Edit';   // State Change
    const CLAIM_REMOVE  = 'Claim.Remove'; // Read Only

    const MEMBER_VIEW   = 'Member.View';  // Read Only

    const FORUM_VIEW         = 'Forum.View';         // Read Only
    const FORUM_TOPIC_CREATE = "Forum.Topic.Create"; // State Change
    const FORUM_TOPIC_REMOVE = "Forum.Topic.Remove"; // State Change
    const FORUM_TOPIC_UPDATE = "Forum.Topic.Update"; // State Change
    const FORUM_POST_CREATE  = "Forum.Post.Create";  // State Change
    const FORUM_POST_UPDATE  = "Forum.Post.Update";  // State Change

    const HELP_VIEW     = 'Help.View';    // Read Only

    const MENU_VIEW     = 'Menu.View';    // Read Only

    const PRIVACY_VIEW  = 'Privacy.View'; // Read Only

    const REGISTRATION_CREATE = 'Registration.Create'; // State Change

    const SETTINGS_VIEW             = 'Settings.View';                 // Read Only
    const SETTINGS_PROFILE_VIEW     = 'Settings.Profile.View';         // Read Only
    const SETTINGS_PROFILE_UPDATE   = 'Settings.Profile.Update';       // State Change
    const SETTINGS_PASSWORD_VIEW    = 'Settings.Password.View';        // Read Only
    const SETTINGS_PASSWORD_UPDATE  = 'Settings.Password.Update';      // State Change
    const SETTINGS_CALEXPORT_VIEW   = 'Settings.CalExport.View';       // Read Only
    const SETTINGS_CALEXPORT_UPDATE = 'Settings.CalExport.Update';     // State Change
    const SETTINGS_CALEXPORT_RESET  = 'Settings.CalExport.Reset';      // State Change
    const SETTINGS_NOTIF_UPDATE     = 'Settings.Notifications.Update'; // State Change

    const CHATVOICE_VIEW    = 'ChatVoice.View';    // Read Only
    const CHATVOICE_BOTHELP = 'ChatVoice.BotHelp'; // Read Only

    const FEEDBACK_VIEW  = 'Feedback.View';  // Read Only
    const FEEDBACK_POST  = 'Feedback.Post';  // State Change

    const BATTLENET_OAUTH_VIEW       = 'Battlenet.OAuth.View';       // Read Only
    const BATTLENET_OAUTH_VERIFY     = 'Battlenet.OAuth.Verify';     // Read Only
    const BATTLENET_OAUTH_DISCONNECT = 'Battlenet.OAuth.Disconnect'; // State Change

    const REDIRECT = "Redirect"; // Read Only

    const REALM_CREATE = "Realm.Create"; // State Change
    const GUILD_CREATE = "Guild.Create"; // State Change

    const QUERY_CHARACTERS_BY_CRITERIA        = "Query.CharactersByCriteriaQuery";       // Read Only
    const QUERY_GET_CHARACTER_BY_ID           = "Query.GetCharacterByIdQuery";           // Read Only
    const QUERY_CHARACTERS_CLAIMED_BY_ACCOUNT = "Query.CharactersClaimedByAccountQuery"; // Read Only
    const QUERY_GET_ALL_CHARACTERS_IN_GUILD   = "Query.GetAllCharactersInGuildQuery";    // Read Only
    const QUERY_CHARACTERS_BY_KEYWORDS        = "Query.CharactersByKeywordsQuery";       // Read Only

    const AUTHZ_DISCORD_INFORM           = "Authorization.Discord.Inform";          // Read Only
    const AUTHZ_DISCORD_REQUEST_AUTHCODE = "Authorization.Discord.RequestAuthCode"; // State Change
    const AUTHZ_DISCORD_GRANT_TOKEN      = "Authorization.Discord.GrantToken";      // State Change
    const AUTHZ_DISCORD_DISCONNECT       = "Authorization.Discord.GrantToken";      // State Change
    const AUTHZ_DISCORD_CONNECT_STATUS   = "Authorization.Discord.ConnectStatus";   // Read Only
}