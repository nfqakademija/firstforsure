<?php
/**
 * Created by PhpStorm.
 * User: osvaldas
 * Date: 18.12.9
 * Time: 16.27
 */

namespace App\Service\Admin\Dashboard;


use App\Models\DataCounter;

class DataCounterFactory
{
    /**
     * @param string $entity
     * @param string $status
     * @param int $countValue
     * @param string $title
     * @return \App\Models\DataCounter
     */
    public function create($entity, $status, $countValue, $title): DataCounter
    {
        return (new DataCounter())
            ->setStatus($status)
            ->setEntity($entity)
            ->setCountValue($countValue)
            ->setTitle($title);
    }
}