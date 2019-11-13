<?php declare(strict_types=1);

namespace SwagPersonalProduct\Service;


use GuzzleHttp\Client;

class ImageGuesser
{
    const SOURCE_URL = 'https://picsum.photos/';
    /**
     * @var Client
     */
    private $picsumClient;

    public function __construct()
    {
        $this->picsumClient = new Client(
            [
                'allow_redirects' => false,
                'base_uri' => self::SOURCE_URL
            ]
        );
    }

    public function fetchRandomImageUrl(int $width = 100, int $height = 100): string
    {
        $location = $this->picsumClient->request('GET', $width . '/' . $height)->getHeader('location')[0];
        return self::SOURCE_URL . $location;
    }
}
