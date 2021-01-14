<?php declare(strict_types=1);
/**
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     https://github.com/GuildLaDanse
 */

namespace App\Infrastructure\Rest;

use JMS\Serializer\SerializerBuilder;
use Symfony\Component\HttpFoundation\Response;

/**
 * Response represents an HTTP response in JSON format.
 *
 * The Json content is created using JMS\Serializer\SerializerBuilder
 */
class JsonSerializedResponse extends Response
{
    /**
     * @var string
     */
    protected string $data;

    /**
     * Creates a JsonResponse representing the given $object.
     * $object must be serializable by JMS\Serializer\SerializerBuilder
     *
     * @param mixed $object  The response object
     * @param int   $status  The response status code
     * @param array $headers An array of response headers
     */
    public function __construct($object = null, $status = 200, $headers = [])
    {
        parent::__construct('', $status, $headers);

        $this->setData($object);
    }

    /**
     * {@inheritdoc}
     */
    public static function create(?string $content = '', $status = 200, $headers = [])
    {
        return new static($content, $status, $headers);
    }

    /**
     * Sets the data to be sent as JSON.
     *
     * @param mixed $object
     *
     * @return JsonSerializedResponse
     */
    public function setData($object): JsonSerializedResponse
    {
        $serializer = SerializerBuilder::create()->build();
        $jsonContent = $serializer->serialize($object, 'json');

        $this->data = $jsonContent;

        $this->headers->set('Content-Type', 'application/json');

        return $this->setContent($this->data);
    }
}
