<?php
/**
 * Created by PhpStorm.
 * User: osvaldas
 * Date: 18.12.12
 * Time: 18.56
 */

namespace App\Service\Admin\Offer;


use App\Entity\Offer;
use App\Entity\OfferPositionTemplate;
use App\Entity\OfferTemplate;
use App\Helpers\ActiveAttributeFilter;
use App\Repository\OfferRepository;
use App\Repository\TemplateRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;

class OfferManager
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var TemplateRepository
     */
    private $templateRepo;

    /**
     * @var OfferRepository
     */
    private $offerRepo;

    /**
     * OfferManager constructor.
     * @param EntityManagerInterface $entityManager
     * @param TemplateRepository $templateRepo
     * @param OfferRepository $offerRepo
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        TemplateRepository $templateRepo,
        OfferRepository $offerRepo)
    {
        $this->entityManager = $entityManager;
        $this->templateRepo = $templateRepo;
        $this->offerRepo = $offerRepo;
    }


    public function makeOffer(Request $request, $user, $status)
    {
        $active = ActiveAttributeFilter::filter($request->get('active'));

        $offerId = $request->get('id');

        $offer = $this->setOfferValues($request, $offerId, $user, $status);
        $offerTemplates = $offer->getOfferTemplates();

        if (count($active) > 0) {
            $this->removeInactive($offerTemplates, $active);

            $this->updateOffer($active, $offerTemplates, $offer);
            $this->entityManager->flush();
        }
    }

    /**
     * @param Request $request
     * @param $offerId
     * @param $user
     * @param $status
     * @return Offer|null
     */
    public function setOfferValues(Request $request, $offerId, $user, $status)
    {
        if ($offerId) {
            $offer = $this->offerRepo->find($offerId);
        } else {
            $offer = new Offer();
        }
        $offer
            ->setClientEmail($request->get('clientEmail'))
            ->setClientName($request->get('clientName'))
            ->setStatus($status)
            ->setUser($user)
            ->setViewed((new \DateTime())->format('Y-m-d H:i:s'));

        if ($request->get('message')) {
            $offer->setMessage($request->get('message'));
        }

        $offer->setMd5(md5($request->get('clientEmail') . (new \DateTime())->format('Y-m-d H:i:s')));
        $this->entityManager->persist($offer);
        $this->entityManager->flush();
        return $offer;
    }

    /**
     * @param OfferTemplate[] $offerTemplates
     * @param $active
     * @return int|string
     */
    public function removeInactive($offerTemplates, $active)
    {
        foreach ($offerTemplates as $offerTemplate) {
            $index = $offerTemplate->getTemplate()->getId();
            if (!in_array($index, $active)) {
                $this->entityManager->remove($offerTemplate);
                $this->entityManager->flush();
            }
        }
        return $offerTemplates;
    }

    /**
     * @param $active
     * @param OfferTemplate[] $offerTemplates
     * @param Offer $offer
     */
    public function updateOffer($active, $offerTemplates, $offer): void
    {
        foreach ($active as $key => $value) {
            $exists = false;
            $template = $this->templateRepo->find($key);
            foreach ($offerTemplates as $offerTemplate) {
                if ($offerTemplate->getTemplate() === $template) {
                    $exists = true;
                    break;
                }
            }
            if (!$exists) {
                $template = $this->templateRepo->find($key);
                $templateOffer = (new OfferTemplate())
                    ->setOffer($offer)
                    ->setTemplate($template)
                    ->setStatus("AddedToOffer")
                    ->setPrice($template->getPrice())
                    ->setReach($template->getReach());
                $this->entityManager->persist($templateOffer);
                $this->entityManager->flush();
                foreach ($template->getPositionTemplates() as $positionTemplate) {
                    $offerPositionTemplate = (new OfferPositionTemplate())
                        ->setOffer($offer)
                        ->setOfferTemplate($templateOffer)
                        ->setPosition($positionTemplate->getPosition())
                        ->setPrice($positionTemplate->getPosition()->getPrice())
                        ->setCount($positionTemplate->getCount());

                    $this->entityManager->persist($offerPositionTemplate);
                    $this->entityManager->flush();

                    $templateOffer->addOfferPositionTemplate($offerPositionTemplate);

                    $this->entityManager->persist($templateOffer);

                }
                $offer->addOfferTemplate($templateOffer);

                $this->entityManager->persist($templateOffer);
                $this->entityManager->persist($offer);
            }
        }
    }
}