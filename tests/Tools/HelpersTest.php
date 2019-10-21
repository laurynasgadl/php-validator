<?php

namespace Luur\Validator\Tools\Tests;

use Luur\Validator\Tools\Helpers;
use PHPUnit\Framework\TestCase;

class HelpersTest extends TestCase
{
    public function snakeCaseToCamelCaseDataProvider()
    {
        return [
            [
                'snake_case',
                'snakeCase',
            ],
            [
                'snakecase',
                'snakecase',
            ],
            [
                '_snake_case',
                'snakeCase',
            ],
            [
                '_snakecase',
                'snakecase',
            ],
        ];
    }

    /**
     * @dataProvider snakeCaseToCamelCaseDataProvider
     * @param $input
     * @param $expected
     */
    public function testConvertsSnakeCaseToCamelCase($input, $expected)
    {
        $this->assertEquals($expected, Helpers::snakeToCamel($input));
    }

    public function camelCaseToSnakeCaseDataProvider()
    {
        return [
            [
                'snakeCase',
                'snake_case',
            ],
            [
                'snakecase',
                'snakecase',
            ],
            [
                'Snakecase',
                'snakecase',
            ],
            [
                'SnakeCase',
                'snake_case',
            ],
            [
                'snakeCase',
                'snake_case',
            ],
            [
                'snakeCASE',
                'snake_case',
            ],
            [
                'SnakeCase',
                'snake_case',
            ],
        ];
    }

    /**
     * @dataProvider camelCaseToSnakeCaseDataProvider
     * @param $input
     * @param $expected
     */
    public function testConvertsCamelCaseToSnakeCase($input, $expected)
    {
        $this->assertEquals($expected, Helpers::camelToSnake($input));
    }
}
