<?php
/**
 * Created by PhpStorm.
 * User: osvaldas
 * Date: 18.12.12
 * Time: 18.56
 */

namespace App\Service\Admin\Offer;


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


//    public function makeOffer(Request $request)
//    {
//        $active = ActiveAttributeFilter::filter($request->get('active'));
//        foreach ($active as $key => $value) {
//            if ($value === "0") {
//                unset($active[$key]);
//            }
//        }
//
//        $offerId = $request->get('id');
//
//        if ($offerId == 0) {
//            $offer = new Offer();
//        } else {
//            $offer = $offerRepo->find($offerId);
//        }
//
//        $offer->setClientEmail($request->get('clientEmail'));
//        $offer->setClientName($request->get('clientName'));
//        $offer->setMessage($request->get('message'));
//        $offer->setStatus('Sukurtas');
//        $offer->setUser($this->getUser());
//        $date = new \DateTime();
//        $offer->setViewed($date->format('Y-m-d H:i:s'));
//
//        $em->persist($offer);
//
//        $time = new \DateTime();
//
//        $hash = md5($request->get('clientEmail') . $time->format('Y-m-d H:i:s'));
//
//        $offer->setMd5($hash);
//        $em->flush();
//        $offerTemplates = $offer->getOfferTemplates();
//
//        if ($active !== null) {
//            foreach ($offerTemplates as $key => $value) {
//                $index = $value->getTemplate()->getId();
//                if (!in_array($index, $active)) {
//                    $em->remove($value);
//                    $em->flush();
//                }
//            }
//
//            foreach ($active as $key => $value) {
//                $exists = false;
//                $template = $templRepo->find($key);
//                foreach ($offerTemplates as $key2 => $value2) {
//                    if ($value2->getTemplate() === $template) {
//                        $exists = true;
//                        break;
//                    }
//                }
//                if (!$exists) {
//                    $template = $this->getDoctrine()->getRepository(Template::class)->find($key);
//                    $templateOffer = new OfferTemplate();
//                    $templateOffer->setOffer($offer)
//                        ->setTemplate($template);
//                    $templateOffer->setStatus("AddedToOffer");
//                    $offer->addOfferTemplate($templateOffer);
//
//                    $em->persist($templateOffer);
//                    $em->persist($offer);
//                }
//            }
//            $em->flush();
//            return $this->redirect("/admin/?entity=Offer&action=list&menuIndex=4&submenuIndex=-1");
//            //return $this->redirectToRoute('sendmail', ['md5' => $offer->getMd5()]);
//        }
//    }
}