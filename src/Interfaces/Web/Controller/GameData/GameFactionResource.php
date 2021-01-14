<?php declare(strict_types=1);
/**
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     https://github.com/GuildLaDanse
 */

namespace App\Interfaces\Web\Controller\GameData;

use App\Infrastructure\Rest\AbstractRestController;
use App\Infrastructure\Rest\JsonSerializedResponse;
use App\Core\Modules\GameData\GameDataService;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/api/gameFactions")
 */
class GameFactionResource extends AbstractRestController
{
    /**
     * @param GameDataService $gameDataService
     *
     * @return JsonSerializedResponse
     *
     * @Route("/", name="getAllGameFactions", options = { "expose" = true }, methods={"GET"})
     */
    public function getAllGameFactionsAction(GameDataService $gameDataService): JsonSerializedResponse
    {
        $gameFactions = $gameDataService->getAllGameFactions();

        return new JsonSerializedResponse($gameFactions);
    }
}
