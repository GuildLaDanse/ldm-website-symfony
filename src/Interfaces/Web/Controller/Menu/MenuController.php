<?php declare(strict_types=1);
/**
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     https://github.com/GuildLaDanse
 */

namespace App\Interfaces\Web\Controller\Menu;


use Psr\Cache\CacheItemPoolInterface;
use Psr\Cache\InvalidArgumentException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MenuController extends AbstractController
{
    /**
     * @Route("/menu", name="menu")
     */
    public function menu(): Response
    {
        return $this->redirectToRoute('index');
    }

    public function eventTile(): Response
    {
        return $this->render('menu/_event_tile.html.twig');
    }

    public function forumTile(): Response
    {
        return $this->render('menu/_forum_tile.html.twig');
    }

    /**
     * @param CacheItemPoolInterface $wowheadCache
     *
     * @return Response
     */
    public function wowhead(CacheItemPoolInterface $wowheadCache): Response
    {
        try
        {
            $items = $wowheadCache->getItem('wowhead.rss')->get();
        }
        catch (InvalidArgumentException $e)
        {
            $items = [];
        }

        return $this->render('menu/_wowhead.html.twig',
            [
                "wowheadNews" => $items
            ]);
    }
}