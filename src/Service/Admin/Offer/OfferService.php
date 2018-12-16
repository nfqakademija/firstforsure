<?php
/**
 * Created by PhpStorm.
 * User: osvaldas
 * Date: 18.12.15
 * Time: 16.30
 */

namespace App\Service\Admin\Offer;


use App\Entity\Message;
use App\Entity\Offer;
use App\Entity\OfferPositionTemplate;
use App\Entity\OfferTemplate;
use App\Entity\Position;
use App\Entity\Template;
use App\Repository\OfferRepository;
use App\Repository\OfferTemplateRepository;
use App\Repository\TemplateRepository;
use Doctrine\ORM\EntityManagerInterface;

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
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * OfferService constructor.
     * @param OfferRepository $offerRepository
     * @param TemplateRepository $templateRepository
     * @param OfferTemplateRepository $offerTemplateRepository
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(
        OfferRepository $offerRepository,
        TemplateRepository $templateRepository,
        OfferTemplateRepository $offerTemplateRepository,
        EntityManagerInterface $entityManager)
    {
        $this->offerRepository = $offerRepository;
        $this->templateRepository = $templateRepository;
        $this->offerTemplateRepository = $offerTemplateRepository;
        $this->entityManager = $entityManager;
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

    /**
     * @param Offer $offer
     * @param $status
     */
    public function changeOfferStatus($offer, $status)
    {
        $offer
            ->setStatus($status)
            ->setViewed((new \DateTime())->format('Y-m-d H:i:s'));
    }

    /**
     * @param OfferTemplate $acceptedOT
     */
    public function acceptOffer($acceptedOT)
    {
        $acceptedOT->setStatus(OfferTemplate::CHECKED);
        $this->entityManager->persist($acceptedOT);

        $boughtOffer = $acceptedOT->getOffer();
        $this->changeOfferStatus($boughtOffer, Offer::ANSWERED);

        $this->entityManager->persist($boughtOffer);
    }

    /**
     * @param OfferTemplate $offerTemplate
     */
    public function handleRemaining($offerTemplate)
    {
        foreach($offerTemplate->getOfferPositionTemplates() as $positionTemplate) {
            $remaining = $positionTemplate->getPosition()->getRemaining();
            $use = $positionTemplate->getCount();
            $positionTemplate->getPosition()->setRemaining($remaining - $use);
            $this->entityManager->persist($positionTemplate);
            $this->entityManager->flush();
        }
    }
}