<?php

namespace Like\Codeception;

class Lang
{
    const DEFAULT = 'pt-br';

    private static $langs = [
        'pt-br' => [
            'success' => ':sunglasses: Parabéns, seus testes passaram...',
            'fail' => ':rage: Seus testes falharam, aconteceram %u erros',
            'footer' => 'Testes foram realizados usando %s',
        ],
        'us' => [
            'success' => ':sunglasses: Congratulations, your tests have passed...',
            'fail' => ':rage: Your tests failed, there were %u errors',
            'footer' => 'Tests were performed using %s',
        ],
    ];

    public static function getLang($lang)
    {
        if (! isset(self::$langs[$lang])) {
            $lang = self::DEFAULT;
        }

        return self::$langs[$lang];
    }
}
