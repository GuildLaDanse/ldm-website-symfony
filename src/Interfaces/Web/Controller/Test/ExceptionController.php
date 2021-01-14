<?php declare(strict_types=1);
/**
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     https://github.com/GuildLaDanse
 */

namespace App\Interfaces\Web\Controller\Test;


use App\Infrastructure\Modules\ServiceException;
use App\Infrastructure\Rest\AbstractRestController;
use App\Infrastructure\Rest\ParameterUtils;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/api/test")
 */
class ExceptionController extends AbstractRestController
{
    /**
     * @return Response
     *
     * @Route("/throwServiceException", name="throwServiceExceptionAction", options = { "expose" = true }, methods={"GET"})
     *
     * @throws ServiceException
     */
    public function throwServiceExceptionAction(): Response
    {
        throw new ServiceException(
            'This is a test exception from throwServiceExceptionAction',
            Response::HTTP_ALREADY_REPORTED);
    }

    /**
     * @param string $someIntegerValue
     *
     * @return Response
     *
     * @Route("/throwTypeError/{someIntegerValue}", name="throwTypeErrorAction", options = { "expose" = true }, methods={"GET"})
     *
     * @throws ServiceException
     */
    public function throwParameterTypeExceptionAction(string $someIntegerValue): Response
    {
        ParameterUtils::isIntegerOrThrow($someIntegerValue, 'someIntegerValue');

        return new Response($someIntegerValue);
    }
}