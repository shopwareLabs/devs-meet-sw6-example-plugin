<?php declare(strict_types=1);

namespace SwagPersonalProduct\Test\Service;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Uri;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Shopware\Core\Framework\Test\TestCaseHelper\ReflectionHelper;
use SwagPersonalProduct\Service\ImageGuesser;

class ImageGuesserTest extends TestCase
{
    public function testItCreatesClient(): void
    {
        $imageGuesser = new ImageGuesser();

        /** @var Client $client */
        $client = ReflectionHelper::getProperty(ImageGuesser::class, 'picsumClient')->getValue($imageGuesser);

        static::assertNotNull($client);
        static::assertInstanceOf(Client::class, $client);

        /** @var Uri $uri */
        $uri = $client->getConfig('base_uri');
        static::assertInstanceOf(Uri::class, $uri);
        static::assertSame('https', $uri->getScheme());
        static::assertSame('picsum.photos', $uri->getHost());
        static::assertFalse($client->getConfig('allow_redirects'));
    }

    public function testItFiresRequest(): void
    {
        $client = $this->createMock(Client::class);
        $imageGuesser = $this->getImageGuesser($client);

        $client->expects(static::once())->method('request')->willReturn($this->createMock(ResponseInterface::class));

        $imageGuesser->fetchRandomImageUrl();
    }

    public function testReturnsSourceUrlAndLocation(): void
    {
        $client = $this->createMock(Client::class);
        $imageGuesser = $this->getImageGuesser($client);

        $response = $this->createMock(ResponseInterface::class);
        $response->expects(static::once())->method('getHeader')->with('location')->willReturn(['/id/123/300/400']);

        $client->expects(static::once())->method('request')->willReturn($response);

        $url = $imageGuesser->fetchRandomImageUrl();

        static::assertSame(ImageGuesser::SOURCE_URL . '/id/123/300/400', $url);
    }

    private function getImageGuesser(Client $client): ImageGuesser
    {
        $imageGuesser = new ImageGuesser();

        ReflectionHelper::getProperty(ImageGuesser::class, 'picsumClient')->setValue($imageGuesser, $client);

        return $imageGuesser;
    }
}
