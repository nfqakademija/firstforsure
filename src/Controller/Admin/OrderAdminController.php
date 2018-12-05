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
use App\Entity\Position;
use App\Entity\PositionTemplate;
use App\Entity\Template;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AdminController as BaseAdminController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

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
     * @Route("/makeordertemplate", name="makeordertemplate")
     */
    public function makeOrderTemplate(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $mailer = $this->get('mailer');

        $orderRepo = $this->getDoctrine()->getRepository(Order::class);
        $active = $request->get('active');

        foreach ($active as $key => $value) {
            if ($value === "0") {
                unset($active[$key]);
            }
        }

        $orderId = $request->get('orderId');

        $order = $orderRepo->find($orderId);
        $order->setStatus("Išsiųstas");
        $date = new \DateTime();
        //$date->modify('+2 hours');
        $order->setViewed($date->format('Y-m-d H:i:s'));

        $template = $order->getTemplate();

        $template->setTitle($request->get('title'));

        $em->persist($template);
        $em->flush();

        $posTemplates = $template->getPositionTemplates();

        if ($active != null) {
            foreach ($posTemplates as $key => $value) {
                $index = $value->getPosition()->getId();
                if (!in_array($index, $active)) {
                    $price = $value->getCount() * $value->getPosition()->getPrice();
                    $reach = $value->getCount() * $value->getPosition()->getReach();
                    $template->minusPrice($price);
                    $template->minusReach($reach);
                    $em->remove($value);
                    $em->flush();
                }
            }
            foreach ($active as $key => $value) {
                $exists = false;
                $position = $this->getDoctrine()->getRepository(Position::class)->find($key);
                $templatePosition = new PositionTemplate();
                $templatePosition->setTemplate($template)
                    ->setPosition($position)
                    ->setCount((int)$request->get('count')[$key]);
                $position->setCount((int)$request->get('count')[$key]);

                foreach ($posTemplates as $key2 => $value2) {
                    if ($value2->getPosition() === $position) {
                        $oldPrice = $value2->getCount() * $value2->getPosition()->getPrice();
                        $oldReach = $value2->getCount() * $value2->getPosition()->getReach();
                        $value2->setPosition($position);
                        $value2->setCount((int)$request->get('count')[$key]);
                        $newPrice = $value2->getCount() * $value2->getPosition()->getPrice();
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
                    $template->addPositionTemplate($templatePosition);

                    $template->addPrice((float)$request->get('sum')[$key]);
                    $template->addReach((float)$request->get('sum2')[$key]);


                    $em->persist($templatePosition);
                    $em->persist($template);
                }
            }
        } else {
            if ($posTemplates !== null) {
                foreach ($posTemplates as $templ) {
                    $template->removePositionTemplate($templ);
                }
                $template->setPrice(0);
                $template->setReach(0);
            }
        }

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

        $em->flush();

        return $this->redirect("/admin/?entity=Order&action=list&menuIndex=3&submenuIndex=-1");
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
        $order[0]->setStatus('Peržiūrėtas');
        $date = new \DateTime();
        $order[0]->setViewed($date->format('Y-m-d H:i:s'));
        $em->persist($order[0]);
        $em->flush();

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

        $repo = $this->getDoctrine()->getRepository(Order::class);

        $order = $repo->find($id);


        $order->setStatus('Atsakytas');
        $date = new \DateTime();
        $order->setViewed($date->format('Y-m-d H:i:s'));

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
        $date = new \DateTime();
        $order->setViewed($date->format('Y-m-d H:i:s'));

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