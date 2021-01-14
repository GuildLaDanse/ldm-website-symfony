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
 * @Route("/api/gameClasses")
 */
class GameClassResource extends AbstractRestController
{
    /**
     * @param GameDataService $gameDataService
     *
     * @return JsonSerializedResponse
     *
     * @Route("/", name="getAllGameClasses", options = { "expose" = true }, methods={"GET"})
     */
    public function getAllGameClassesAction(GameDataService $gameDataService): JsonSerializedResponse
    {
        $gameClasses = $gameDataService->getAllGameClasses();

        return new JsonSerializedResponse($gameClasses);
    }
}
