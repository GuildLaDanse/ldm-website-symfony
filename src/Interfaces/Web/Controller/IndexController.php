<?php declare(strict_types=1);
/**
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     https://github.com/GuildLaDanse
 */

namespace App\Interfaces\Web\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class IndexController extends AbstractController
{
    /**
     * @Route("/", name="index")
     */
    public function index(): Response
    {
        $user = $this->getUser();

        if ($user)
        {
            return $this->render('menu/menu.html.twig');
        }

        return $this->render('index.html.twig');
    }

    /**
     * @Route("/secured", name="secured")
     */
    public function secured(): Response
    {
        return $this->render('secured.html.twig');
    }
}