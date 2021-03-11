<?php

namespace Like\Codeception;

class Lang
{
    const LANG_DEFAULT = 'pt-br';

    private static $langs = [
        'pt-br' => [
            'success' => ':sunglasses: Parabéns, seus testes passaram...',
            'fail' => ':rage: Que pena, ocorreram %u erro(s) em seus testes!',
            'footer' => 'Testes foram realizados usando %s',
        ],
        'en-us' => [
            'success' => ':sunglasses: Congratulations, your tests have passed...',
            'fail' => ':rage: Your tests failed, there were %u errors!',
            'footer' => 'Tests were performed using %s',
        ],
    ];

    public static function getLang($lang)
    {
        if (! isset(self::$langs[$lang])) {
            $lang = self::LANG_DEFAULT;
        }

        return self::$langs[$lang];
    }
}
