<?php

namespace Like\Codeception;

use GuzzleHttp\Client;
use LogicException;

class UploadImage
{
    const URL = 'https://api.imgbb.com/1/upload';
    const EXPIRATION_IN_SECONDS = 10080; // One week

    /** @var string */
    private $token;

    public function __construct($token)
    {
        $this->token = $token;
    }

    public function upload($fileToUpload)
    {
        $url = self::URL .
            '?expiration=' . self::EXPIRATION_IN_SECONDS .
            '&key=' . $this->token;

        $client = new Client();
        $response = $client->request('POST', $url, [
            'multipart' => [
                [
                    'name'     => 'image',
                    'contents' => fopen($fileToUpload, 'r'),
                ],
            ],
        ]);

        $body = (string) $response->getBody();
        if (! $body) {
            throw new LogicException('Body is empty.');
        }

        $json = json_decode($body, true);
        if (! is_array($json)) {
            throw new LogicException('Body is json. Body: ' . $body);
        }

        if (! isset($json['data']) || ! isset($json['data']['url'])) {
            throw new LogicException('Body is valid json. Body: ' . $body);
        }

        return $json['data']['url'];
    }
}
