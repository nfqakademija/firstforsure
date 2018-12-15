<?php
/**
 * Created by PhpStorm.
 * User: osvaldas
 * Date: 18.12.3
 * Time: 18.35
 */

namespace App\Controller\Admin;

use App\Entity\Message;
use App\Entity\Offer;
use App\Entity\OfferPositionTemplate;
use App\Entity\OfferTemplate;
use App\Entity\Order;
use App\Entity\Position;
use App\Entity\PositionTemplate;
use App\Entity\Template;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AdminController as BaseAdminController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class OrderAdminController extends BaseAdminController
{
    protected function createListQueryBuilder($entityClass, $sortDirection, $sortField = null, $dqlFilter = null)
    {
        $user = $this->getUser();
        if(in_array("ROLE_ADMIN",$user->getRoles()))
        {
            $dqlFilter = "";
        }
        else
        {
            $dqlFilter = "entity.user = ".$user->getId();
        }
        return $this->get('easyadmin.query_builder')->createListQueryBuilder($this->entity, $sortField, $sortDirection, $dqlFilter);
    }

    public function editAction()
    {
        $msgRepo = $this->getDoctrine()->getRepository(Message::class);
        $orderRepo = $this->getDoctrine()->getRepository(Order::class);
        $templRepo = $this->getDoctrine()->getRepository(Template::class);
        $id = $this->request->query->get('id');

        $order = $orderRepo->find($id);

        $title = $order->getTemplate()->getTitle();

        $messages = $msgRepo->findByOfferId($id);

        $posRepo = $this->getDoctrine()->getRepository(Position::class);

        $activeItem = $templRepo->find($order->getTemplate()->getId());

        $activePositionItems = $activeItem->getPositionTemplates();
        $positionItems = $posRepo->findAll();
        $positionTimeItems = $posRepo->findByTime(true);
        $positionNoTimeItems = $posRepo->findByTime(false);

        foreach ($positionItems as $value)
        {
            foreach ($activePositionItems as $value2)
            {
                if($value2->getPosition()->getId() === $value->getId()){
                    $value->setCount($value2->getCount());
                }
            }
        }

        return $this->render('admin/order/edit.html.twig', [
            'messages' => $messages,
            'order' => $order,
            'id' => $id,
            'title' => $title,
            'positionTimeItems' => $positionTimeItems,
            'positionNoTimeItems' => $positionNoTimeItems
        ]);
    }

    /**
     * @Route("/readorder/{md5}", name="readorder")
     */
    public function readorder($md5)
    {
        $em = $this->getDoctrine()->getManager();
        $repo = $this->getDoctrine()->getRepository(Offer::class);
        $orepo = $this->getDoctrine()->getRepository(OfferTemplate::class);
        $mrepo = $this->getDoctrine()->getRepository(Message::class);

        $offer = $repo->findByMd5($md5);

        if (!$offer) {
            return $this->render('admin/offer/error.html.twig', [
                'errorType' => 2
            ]);
        }
        $offer->setStatus(Offer::VIEWED);
        $offerTemplate = $orepo->findCheckedOfferTemplate("CHECKED", $offer->getId());
        $date = new \DateTime();
        $offer->setViewed($date->format('Y-m-d H:i:s'));
        $em->persist($offer);
        $em->flush();

        $messages = $mrepo->findByOfferId($offer->getId());

        return $this->render('admin/order/userorder.html.twig', [
            'offer' => $offer,
            'offerTemplate' => $offerTemplate,
            'messages' => $messages
        ]);
    }

    /**
     *
     * @Route("/orderaccept", name="orderaccept")
     */
    public function orderAccept(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $id = $request->get('orderId');
        $repo = $this->getDoctrine()->getRepository(Order::class);
        $order = $repo->find($id);

        $order->setStatus("Patvirtintas");
        $date = new \DateTime();

        $order->setViewed($date->format("Y-m-d H:i:s"));

        foreach($order->getTemplate()->getPositionTemplates() as $value) {
            $remaining = $value->getPosition()->getRemaining();
            $use = $value->getCount();
            $value->getPosition()->setRemaining($remaining - $use);
            $em->persist($value);
            $em->flush();
        }

        $em->persist($order);
        $em->flush();

        return $this->redirect("/admin/?entity=Order&action=list&menuIndex=1&submenuIndex=-1");
    }

    /**
     *
     * @Route("/clientorderresponse", name="clientorderresponse")
     */
    public function clientResponseSend(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $id = $request->get('orderId');

        $repo = $this->getDoctrine()->getRepository(Offer::class);

        $offer = $repo->find($id);


        $offer->setStatus(Offer::ANSWERED);
        $date = new \DateTime();
        $offer->setViewed($date->format('Y-m-d H:i:s'));

        $em->persist($offer);
        $message = new Message();

        $message->setDate(new \DateTime());
        $message->setText($request->get('msg'));
        $message->setOffer($offer);
        $message->setUsername($request->get('username'));

        $em->persist($message);
        $em->flush();

        return $this->redirectToRoute("admin");
    }

    /**
     *
     * @Route("/orderresponse", name="orderresponse")
     */
    public function responseSend(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $mailer = $this->get('mailer');
        //$transport = new \Swift_SmtpTransport('smtp.gmail.com',,'ssl')

        $id = $request->get('orderId');

        $repo = $this->getDoctrine()->getRepository(Offer::class);

        $offer = $repo->find($id);


        $offer->setStatus(Offer::SENT);
        $date = new \DateTime();
        $offer->setViewed($date->format('Y-m-d H:i:s'));

        $em->persist($offer);
        $message = new Message();

        $message->setDate(new \DateTime());
        $message->setText($request->get('msg'));
        $message->setOffer($offer);
        $message->setUsername("MANAGER");

        $em->persist($message);

        $time = new \DateTime();

        $hash = md5($request->get('username') . $time->format('Y-m-d H:i:s'));

        $offer->setMd5($hash);

        $em->persist($offer);
        $em->flush();

        $message = (new \Swift_Message('Atsakymas į reklamos pasiūlymą'))
            ->setFrom('zrvtzrvt@gmail.com')
            ->setTo($offer->getOffer()->getClientEmail())
            ->setBody(
                $this->renderView(
                // templates/emails/registration.html.twig
                    'admin/offer/mail.html.twig',
                    array('link' => $this->generateUrl('readorder', ['md5' => $offer->getMd5()], UrlGeneratorInterface::ABSOLUTE_URL), 'offer' => $order->getOffer())
                    //array('link' => '127.0.0.1:8000/readorder/' . $order->getOffer()->getMd5(), 'offer' => $order->getOffer())
                ),
                'text/html'
            )
        ;

        $mailer->send($message);
        return $this->redirect('/admin/?entity=Offer&action=list&menuIndex=4&submenuIndex=-1');
    }
}