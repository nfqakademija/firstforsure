<?php
/**
 * Created by PhpStorm.
 * User: osvaldas
 * Date: 18.12.12
 * Time: 19.11
 */

namespace App\Helpers;


class ActiveAttributeFilter
{
    /**
     * @param $active
     * @return array
     */
    public static function filter($active): array
    {
        foreach ($active as $key => $value) {
            if ($value === "0") {
                unset($active[$key]);
            }
        }
        return $active;
    }
}