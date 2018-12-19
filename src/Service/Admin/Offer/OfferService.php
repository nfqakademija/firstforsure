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
        if ($checkedOT) {
            $activePositionItems = $checkedOT->getOfferPositionTemplates();

            foreach ($activePositionItems as $activePositionItem) {
                if (in_array($activePositionItem->getPosition(), $positionItems)) {
                    $key = array_search($activePositionItem->getPosition(), $positionItems);
                    $positionItems[$key]->setCount($activePositionItem->getCount());
                    $positionItems[$key]->setOfferPrice($activePositionItem->getPrice());
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

        $this->entityManager->persist($offer);
        $this->entityManager->flush();
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
        foreach ($offerTemplate->getOfferPositionTemplates() as $positionTemplate) {
            $remaining = $positionTemplate->getPosition()->getRemaining();
            $use = $positionTemplate->getCount();
            $positionTemplate->getPosition()->setRemaining($remaining - $use);
            $this->entityManager->persist($positionTemplate);
            $this->entityManager->flush();
        }
    }

    /**
     * @param Position[] $positionsHasTime
     * @param Position[] $positionsHasNoTime
     * @param OfferTemplate $offerTemplate
     */
    public function getUnusedPositions($positionsHasTime, $positionsHasNoTime, $offerTemplate): void
    {
        foreach ($offerTemplate->getOfferPositionTemplates() as $ot) {
            if (($key = array_search($ot->getPosition(), $positionsHasTime)) !== false) {
                unset($positionsHasTime[$key]);
            } elseif (($key = array_search($ot->getPosition(), $positionsHasNoTime)) !== false) {
                unset($positionsHasNoTime[$key]);
            }
        }
    }

    /**
     * @param OfferTemplate $offerTemplate
     * @return bool
     */
    public function canConfirm($offerTemplate): bool
    {
        $isConfirm = true;
        foreach ($offerTemplate->getOfferPositionTemplates() as $positionTemplate) {
            $remaining = $positionTemplate->getPosition()->getRemaining();
            $use = $positionTemplate->getCount();
            if ($remaining < $use) {
                $isConfirm = false;
                break;
            }
        }
        return $isConfirm;
    }
}