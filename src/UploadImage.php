<?php

namespace Like\Codeception;

use GuzzleHttp\Client;

class UploadImage
{
    const URL = 'https://api.imgbb.com/1/upload';

    /** @var string */
    private $token;

    public function __construct($token)
    {
        $this->token = $token;
    }

    public function upload($fileToUpload)
    {
        $client = new Client();
        $response = $client->request('POST', self::URL . "?expiration=600&key={$this->token}", [
            'multipart' => [
                [
                    'name'     => 'image',
                    'contents' => fopen($fileToUpload, 'r'),
                ],
            ],
        ]);

        $body = (string) $response->getBody();
        $json = json_decode($body, true);
        return $json['data']['url'];
    }
}
