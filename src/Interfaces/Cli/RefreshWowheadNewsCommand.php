<?php
/**
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     https://github.com/bderidder/ldm-guild-website
 */

namespace App\Interfaces\Cli;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Cache\InvalidArgumentException;
use Psr\Http\Message\StreamInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class RefreshWowheadNewsCommand
 * @package LaDanse\ServicesBundle\Command
 */
class RefreshWowheadNewsCommand extends Command
{
    public const WOWHEAD_RSS_URL = "http://www.wowhead.com/news&rss";

    public const GET_TIMEOUT = 60; // seconds of timeout

    /** @var CacheItemPoolInterface  */
    private CacheItemPoolInterface $wowheadCache;

    public function __construct(
        CacheItemPoolInterface $wowheadCache,
        string $name = null)
    {
        parent::__construct($name);

        $this->wowheadCache = $wowheadCache;
    }

    /**
     * @return void
     */
    protected function configure(): void
    {
        $this
            ->setName('ladanse:refreshWowheadNews')
            ->setDescription('Refresh RSS news from wowhead')
        ;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return ?int
     *
     * @throws InvalidArgumentException|GuzzleException
     */
    protected function execute(InputInterface $input, OutputInterface $output): ?int
    {
        $context = new CommandExecutionContext(
            $input,
            $output
        );

        $xmlString = $this->getXMLFromUrl($context, self::WOWHEAD_RSS_URL);

        $xml = simplexml_load_string($xmlString, null);

        if ($xml === false)
        {
            $context->error("Failed loading XML: ");

            foreach(libxml_get_errors() as $error)
            {
                $context->error($error->message);
            }

            return -1;
        }

        $itemXMLList = $xml->channel->item;

        $items = [];

        $count = 0;

        foreach($itemXMLList as $itemXML)
        {
            $pubTime = strtotime((string)$itemXML->pubDate);

            $item = (object) array(
                'title'   => (string) $itemXML->title,
                'link'    => (string) $itemXML->link,
                'pubDate' => date('D, d M, H:i', $pubTime)
            );

            $count++;

            if ($count <= 8)
            {
                $items[] = $item;
            }
        }

        $wowheadRssCacheItem = $this->wowheadCache->getItem('wowhead.rss');

        $wowheadRssCacheItem->set($items);

        $this->wowheadCache->save($wowheadRssCacheItem);

        $this->wowheadCache->commit();

        var_dump($items);

        return 0;
    }

    /**
     * @param CommandExecutionContext $context
     * @param $url
     *
     * @return StreamInterface|null
     *
     * @throws GuzzleException
     */
    private function getXMLFromUrl(CommandExecutionContext $context, $url): ?StreamInterface
    {
        $client = new Client();

        try
        {
            $response = $client->request('GET', $url, ['timeout' => self::GET_TIMEOUT]);

            if ($response->getStatusCode() !== 200)
            {
                $context->error("Status code was not 200 but " . $response->getStatusCode());

                return null;
            }

            return $response->getBody();
        }
        catch(\Exception $e)
        {
            $context->error("Got exception while retrieving XML " . $e->getMessage());

            return null;
        }
    }
}