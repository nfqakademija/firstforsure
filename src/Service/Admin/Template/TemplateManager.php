<?php
/**
 * Created by PhpStorm.
 * User: osvaldas
 * Date: 18.12.9
 * Time: 16.59
 */

namespace App\Service\Admin\Template;

use App\Entity\PositionTemplate;
use App\Entity\Template;
use App\Helpers\ActiveAttributeFilter;
use App\Repository\PositionRepository;
use App\Repository\TemplateRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;

class TemplateManager
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
     * @var PositionRepository
     */
    private $positionRepo;

    /**
     * TemplateService constructor.
     * @param EntityManagerInterface $entityManager
     * @param TemplateRepository $templateRepo
     * @param PositionRepository $positionRepo
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        TemplateRepository $templateRepo,
        PositionRepository $positionRepo)
    {
        $this->entityManager = $entityManager;
        $this->templateRepo = $templateRepo;
        $this->positionRepo = $positionRepo;
    }

    /**
     * @param Request $request
     */
    public function makeTemplate(Request $request)
    {
        $active = ActiveAttributeFilter::filter($request->get('active'));
        $template = $this->setTemplateValues($request, $request->get('id'));

        if (count($active) > 0) {
            $posTemplates = $this->checkActivePosTemplates($template, $active);
            $this->updatePositionTemplates($request, $active, $template, $posTemplates);
        } else {
            $this->removePositionTemplates($template);
        }

        $this->entityManager->flush();
    }

    /**
     * @param Request $request
     * @param $templateId
     * @return Template|null
     */
    private function setTemplateValues(Request $request, $templateId): Template
    {
        if ($templateId) {
            $template = $this->templateRepo->find($templateId);
        } else {
            $template = new Template();
        }
        $template->setTitle($request->get('title'));
        $template->setStatus(Template::BASE);

        $this->entityManager->persist($template);
        $this->entityManager->flush();

        return $template;
    }

    /**
     * @param Template $template
     * @param $active
     * @return mixed
     */
    private function checkActivePosTemplates($template, $active)
    {
        $posTemplates = $template->getPositionTemplates();
        foreach ($posTemplates as $posTemplate) {
            $index = $posTemplate->getPosition()->getId();
            if (!in_array($index, $active)) {
                $price = $posTemplate->getCount() * $posTemplate->getPosition()->getPrice();
                $reach = $posTemplate->getCount() * $posTemplate->getPosition()->getReach();
                $template->minusPrice($price);
                $template->minusReach($reach);
                $this->entityManager->remove($posTemplate);
                $this->entityManager->flush();
            }
        }
        return $posTemplates;
    }

    /**
     * @param Request $request
     * @param $active
     * @param Template $template
     * @param PositionTemplate[] $posTemplates
     */
    private function updatePositionTemplates(Request $request, $active, $template, $posTemplates): void
    {
        foreach ($active as $key => $value) {
            $count = (int)$request->get('count')[$key];
            if ($count > 0) {
                $exists = false;
                $position = $this->positionRepo->find($key);

                foreach ($posTemplates as $posTemplate) {
                    if ($posTemplate->getPosition() === $position) {
                        $posTemplate->setPosition($position);
                        $posTemplate->setCount($count);
                        $template
                            ->minusPrice($posTemplate->getCount() * $posTemplate->getPosition()->getPrice())
                            ->addPrice($posTemplate->getCount() * $posTemplate->getPosition()->getPrice())
                            ->minusReach($posTemplate->getCount() * $posTemplate->getPosition()->getReach())
                            ->addReach($posTemplate->getCount() * $posTemplate->getPosition()->getReach());
                        $exists = true;
                        break;
                    }
                }
                if (!$exists) {
                    $templatePosition = (new PositionTemplate())
                        ->setTemplate($template)
                        ->setPosition($position)
                        ->setCount($count)
                        ->setPrice((float)$request->get('sum')[$key]);

                    $template
                        ->addPositionTemplate($templatePosition)
                        ->addPrice((float)$request->get('sum')[$key])
                        ->addReach((float)$request->get('sum2')[$key]);

                    $this->entityManager->persist($templatePosition);
                    $this->entityManager->persist($template);
                }
            }
        }
    }

    /**
     * @param Template $template
     */
    private function removePositionTemplates($template): void
    {
        if ($template->getPositionTemplates() !== null) {
            foreach ($template->getPositionTemplates() as $templ) {
                $template->removePositionTemplate($templ);
            }
            $template->setPrice(0);
            $template->setReach(0);
        }
    }
}