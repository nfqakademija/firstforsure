<?php
/**
 * Created by PhpStorm.
 * User: osvaldas
 * Date: 18.12.12
 * Time: 19.20
 */

namespace App\Service\Admin\Template;


use App\Entity\Position;
use App\Entity\PositionTemplate;

class TemplateService
{
    /**
     * @param Position[] $positionItems
     * @param PositionTemplate[] $activePositionItems
     */
    public function setPositionCounts($positionItems, $activePositionItems){
        foreach ($positionItems as $value)
        {
            foreach ($activePositionItems as $value2)
            {
                if($value2->getPosition()->getId() === $value->getId()){
                    $value->setCount($value2->getCount());
                }
            }
        }
    }
}