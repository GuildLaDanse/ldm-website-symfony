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
 * @Route("/api/gameRaces")
 */
class GameRaceResource extends AbstractRestController
{
    /**
     * @param GameDataService $gameDataService
     *
     * @return JsonSerializedResponse
     *
     * @Route("/", name="getAllGameRaces", options = { "expose" = true }, methods={"GET"})
     */
    public function getAllGameRacesAction(GameDataService $gameDataService): JsonSerializedResponse
    {
        $gameRaces = $gameDataService->getAllGameRaces();

        return new JsonSerializedResponse($gameRaces);
    }
}
