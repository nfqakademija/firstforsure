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
use App\Entity\Order;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AdminController as BaseAdminController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class OrderAdminController extends BaseAdminController
{
    public function editAction()
    {
        $msgRepo = $this->getDoctrine()->getRepository(Message::class);
        $orderRepo = $this->getDoctrine()->getRepository(Order::class);
        $id = $this->request->query->get('id');

        $order = $orderRepo->find($id);

        $messages = $msgRepo->findByOfferId($id);

        return $this->render('admin/order/edit.html.twig', [
            'messages' => $messages,
            'order' => $order
        ]);
    }

    /**
     * @Route("/readorder/{md5}", name="readorder")
     */
    public function readorder($md5)
    {
        $em = $this->getDoctrine()->getManager();
        $repo = $this->getDoctrine()->getRepository(Offer::class);
        $orepo = $this->getDoctrine()->getRepository(Order::class);
        $mrepo = $this->getDoctrine()->getRepository(Message::class);

        $offer = $repo->findByMd5($md5);

        $order = $orepo->findByOffer($offer[0]);

        if (!$offer) {
            return $this->render('admin/offer/error.html.twig', [
                'errorType' => 2
            ]);
        }
        //$order[0]->setStatus('Peržiūrėtas');

        $messages = $mrepo->findByOfferId($order[0]->getId());

        return $this->render('admin/order/userorder.html.twig', [
            'order' => $order[0],
            'offer' => $offer[0],
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

        foreach($order->getTemplate()->getPositionTemplates() as $value) {
            $remaining = $value->getPosition()->getRemaining();
            $use = $value->getCount();
            $value->getPosition()->setRemaining($remaining - $use);
            $em->persist($value);
            $em->flush();
        }

        $em->persist($order);
        $em->flush();

        return $this->redirectToRoute("admin");
    }

    /**
     *
     * @Route("/clientorderresponse", name="clientorderresponse")
     */
    public function clientResponseSend(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $id = $request->get('orderId');

        $repo = $this->getDoctrine()->getRepository(Order::class);

        $order = $repo->find($id);


        $order->setStatus('Atsakytas');

        $em->persist($order);
        $message = new Message();

        $message->setDate(new \DateTime());
        $message->setText($request->get('msg'));
        $message->setOrder($order);
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

        $repo = $this->getDoctrine()->getRepository(Order::class);

        $order = $repo->find($id);


        $order->setStatus('Išsiųstas');

        $em->persist($order);
        $message = new Message();

        $message->setDate(new \DateTime());
        $message->setText($request->get('msg'));
        $message->setOrder($order);
        $message->setUsername("MANAGER");

        $em->persist($message);

        $time = new \DateTime();

        $hash = md5($request->get('username') . $time->format('Y-m-d H:i:s'));

        $order->getOffer()->setMd5($hash);

        $em->persist($order);
        $em->flush();

        $message = (new \Swift_Message('Atsakymas į reklamos pasiūlymą'))
            ->setFrom('zrvtzrvt@gmail.com')
            ->setTo($order->getOffer()->getClientEmail())
            ->setBody(
                $this->renderView(
                // templates/emails/registration.html.twig
                    'admin/offer/mail.html.twig',
                    array('link' => '127.0.0.1:8000/readorder/' . $order->getOffer()->getMd5(), 'offer' => $order->getOffer())
                ),
                'text/html'
            )/*
             * If you also want to include a plaintext version of the message
            ->addPart(
                $this->renderView(
                    'emails/registration.txt.twig',
                    array('name' => $name)
                ),
                'text/plain'
            )
            */
        ;

        $mailer->send($message);
        return $this->redirect('/admin/?entity=Offer&action=list&menuIndex=4&submenuIndex=-1');
    }
}