<?php
/**
 * Created by PhpStorm.
 * User: osvaldas
 * Date: 18.12.15
 * Time: 16.30
 */

namespace App\Service\Admin\Offer;


use App\Entity\OfferPositionTemplate;
use App\Entity\OfferTemplate;
use App\Entity\Position;
use App\Entity\Template;
use App\Repository\OfferRepository;
use App\Repository\OfferTemplateRepository;
use App\Repository\TemplateRepository;

class OfferService
{
    /**
     * @var OfferRepository
     */
    private $offerRepository;

    /**
     * @var TemplateRepository
     */
    private $templateRepository;

    /**
     * @var OfferTemplateRepository
     */
    private $offerTemplateRepository;

    /**
     * OfferService constructor.
     * @param OfferRepository $offerRepository
     * @param TemplateRepository $templateRepository
     * @param OfferTemplateRepository $offerTemplateRepository
     */
    public function __construct(
        OfferRepository $offerRepository,
        TemplateRepository $templateRepository,
        OfferTemplateRepository $offerTemplateRepository)
    {
        $this->offerRepository = $offerRepository;
        $this->templateRepository = $templateRepository;
        $this->offerTemplateRepository = $offerTemplateRepository;
    }

    /**
     * @param OfferTemplate $checkedOT
     * @param Position[] $positionItems
     */
    public function setActivePositionItems($checkedOT, $positionItems): void
    {
        if($checkedOT) {
            $activePositionItems = $checkedOT->getOfferPositionTemplates();

            foreach ($positionItems as $positionItem) {
                foreach ($activePositionItems as $activePositionItem) {
                    if ($activePositionItem->getPosition()->getId() === $positionItem->getId()) {
                        $positionItem->setCount($activePositionItem->getCount());
                        $positionItem->setOfferPrice($activePositionItem->getPrice());
                    } else {
                        $positionItem->setOfferPrice($positionItem->getPrice());
                    }
                }
            }
        }
    }

    /**
     * @param Template[] $templateItems
     * @param OfferTemplate[] $activeOfferItems
     * @return array
     */
    public function setActiveTemplateItems($templateItems, $activeOfferItems): array
    {
        foreach ($templateItems as $templateItem) {
            foreach ($activeOfferItems as $activeOfferItem) {
                if ($activeOfferItem->getTemplate()->getId() === $templateItem->getId()) {
                    $templateItem->setActive(true);
                }
            }
        }

        return $templateItems;
    }
}