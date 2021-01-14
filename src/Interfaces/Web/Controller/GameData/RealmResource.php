<?php declare(strict_types=1);
/**
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     https://github.com/GuildLaDanse
 */

namespace App\Interfaces\Web\Controller\GameData;

use App\Infrastructure\Modules\ServiceException;
use App\Infrastructure\Rest\AbstractRestController;
use App\Infrastructure\Rest\JsonSerializedResponse;
use App\Infrastructure\Rest\ResourceHelper;
use App\Core\Modules\GameData\DTO\PatchRealm;
use App\Core\Modules\GameData\GameDataService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/api/realms")
 */
class RealmResource extends AbstractRestController
{
    /**
     * @param GameDataService $gameDataService
     *
     * @return JsonSerializedResponse
     *
     * @Route("/", name="getAllRealms", options = { "expose" = true }, methods={"GET"})
     */
    public function getAllRealmsAction(GameDataService $gameDataService): JsonSerializedResponse
    {
        $realms = $gameDataService->getAllRealms();

        return new JsonSerializedResponse($realms);
    }

    /**
     * @param Request $request
     * @param GameDataService $gameDataService
     *
     * @return JsonSerializedResponse
     *
     * @Route("/", name="postRealm", methods={"POST"})
     */
    public function postRealmAction(Request $request, GameDataService $gameDataService): JsonSerializedResponse
    {
        try
        {
            /** @var PatchRealm $patchRealm */
            $patchRealm = $this->getDtoFromContent($request, PatchRealm::class);

            $dtoRealm = $gameDataService->postRealm($patchRealm);

            return new JsonSerializedResponse($dtoRealm);
        }
        catch(ServiceException $serviceException)
        {
            return ResourceHelper::createErrorResponse(
                $request,
                $serviceException->getCode(),
                $serviceException->getMessage()
            );
        }
    }
}
