<?php



declare(strict_types = 1);

namespace shopgui\plugins\utils;



use function str_replace;



trait Translate {



    /**

     * @param array|string $key

     * @param array|string $value

     * @param string $texter

     * @return string

     */

    public function translate(array|string $key, array|string $value, string $texter): string {

        return str_replace($key ?? [], $value ?? [], $texter ?? "");

    }

}
