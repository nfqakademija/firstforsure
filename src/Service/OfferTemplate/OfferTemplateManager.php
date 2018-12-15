<?php
/**
 * Created by PhpStorm.
 * User: osvaldas
 * Date: 18.12.15
 * Time: 16.05
 */

namespace App\Service\OfferTemplate;


use App\Entity\Message;
use App\Entity\Offer;
use App\Entity\OfferPositionTemplate;
use App\Entity\OfferTemplate;
use App\Helpers\ActiveAttributeFilter;
use App\Repository\OfferRepository;
use App\Repository\OfferTemplateRepository;
use App\Repository\PositionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;

class OfferTemplateManager
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var OfferRepository
     */
    private $offerRepository;

    /**
     * @var OfferTemplateRepository
     */
    private $offerTemplateRepository;

    /**
     * @var PositionRepository
     */
    private $positionRepository;

    /**
     * OfferTemplateManager constructor.
     * @param EntityManagerInterface $entityManager
     * @param OfferRepository $offerRepository
     * @param OfferTemplateRepository $offerTemplateRepository
     * @param PositionRepository $positionRepository
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        OfferRepository $offerRepository,
        OfferTemplateRepository $offerTemplateRepository,
        PositionRepository $positionRepository)
    {
        $this->entityManager = $entityManager;
        $this->offerRepository = $offerRepository;
        $this->offerTemplateRepository = $offerTemplateRepository;
        $this->positionRepository = $positionRepository;
    }


    public function editTemplate(Request $request){

        $active = ActiveAttributeFilter::filter($request->get('active'));

        $offerId = $request->get('orderId');

        $offer = $this->offerRepository->find($offerId);
        $offer
            ->setStatus(Offer::SENT)
            ->setViewed((new \DateTime())->format('Y-m-d H:i:s'));

        $template = $this->offerTemplateRepository->findCheckedOfferTemplate("CHECKED", $offerId);
        $posTemplates = $template->getOfferPositionTemplates();

        if (count($active) > 0) {
            $this->removeInactive($posTemplates, $active, $template);
            $this->updateOffer($request, $active, $template, $offer, $posTemplates);
            $this->entityManager->flush();
        } else {
            $this->clearOfferTemplate($posTemplates, $template);
        }

        $message = (new Message())
            ->setDate(new \DateTime())
            ->setText($request->get('msg'))
            ->setOffer($offer)
            ->setUsername("MANAGER")
        ;

        $this->entityManager->persist($message);

        $time = new \DateTime();

        $hash = md5($request->get('username') . $time->format('Y-m-d H:i:s'));

        $offer->setMd5($hash);

        $this->entityManager->persist($offer);
        $this->entityManager->flush();

        return $offer;
    }

    /**
     * @param OfferPositionTemplate[] $posTemplates
     * @param $active
     * @param OfferTemplate $template
     */
    public function removeInactive($posTemplates, $active, $template): void
    {
        foreach ($posTemplates as $posTemplate) {
            $index = $posTemplate->getPosition()->getId();
            if (!in_array($index, $active)) {
                $price = $posTemplate->getCount() * $posTemplate->getPosition()->getOfferPrice();
                $reach = $posTemplate->getCount() * $posTemplate->getPosition()->getReach();
                $template->minusPrice($price);
                $template->minusReach($reach);
                $this->entityManager->remove($posTemplate);
                $this->entityManager->flush();
            }
        }
    }

    /**
     * @param Request $request
     * @param $active
     * @param OfferTemplate $template
     * @param Offer $offer
     * @param OfferPositionTemplate[] $posTemplates
     */
    public function updateOffer(Request $request, $active, $template, $offer, $posTemplates): void
    {
        foreach ($active as $key => $value) {
            $exists = false;
            $position = $this->positionRepository->find($key);
            $templatePosition = new OfferPositionTemplate();
            $templatePosition->setOfferTemplate($template)
                ->setPosition($position)
                ->setCount((int)$request->get('count')[$key]);
            $templatePosition->setOffer($offer);
            $templatePosition->setPrice($position->getPrice());
            $position->setCount((int)$request->get('count')[$key]);

            foreach ($posTemplates as $value2) {
                if ($value2->getPosition() === $position) {
                    $oldPrice = $value2->getCount() * $value2->getPosition()->getOfferPrice();
                    $oldReach = $value2->getCount() * $value2->getPosition()->getReach();
                    $value2->setPosition($position);
                    $value2->setCount((int)$request->get('count')[$key]);
                    $newPrice = $value2->getCount() * $value2->getPosition()->getOfferPrice();
                    $newReach = $value2->getCount() * $value2->getPosition()->getReach();
                    $template->minusPrice($oldPrice);
                    $template->addPrice($newPrice);
                    $template->minusReach($oldReach);
                    $template->addReach($newReach);
                    $exists = true;
                    break;
                }
            }
            if (!$exists) {
                $template->addOfferPositionTemplate($templatePosition);

                $template->addPrice((float)$request->get('sum')[$key]);
                $template->addReach((float)$request->get('sum2')[$key]);


                $this->entityManager->persist($templatePosition);
                $this->entityManager->persist($template);
            }
        }
    }

    /**
     * @param OfferPositionTemplate[] $posTemplates
     * @param OfferTemplate $template
     */
    public function clearOfferTemplate($posTemplates, $template): void
    {
        if ($posTemplates !== null) {
            foreach ($posTemplates as $templ) {
                $template->removeOfferPositionTemplate($templ);
            }
            $template->setPrice(0);
            $template->setReach(0);
        }
    }
}