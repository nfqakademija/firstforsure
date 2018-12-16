<?php

namespace App\Service\Admin\Dashboard;

use App\Entity\Offer;
use App\Models\OfferStatus;
use App\Models\OrderStatus;
use App\Repository\OfferRepository;
use App\Repository\OrderRepository;

/**
 * Class CounterService
 * @package App\Service\Admin\Dashboard
 */
class CounterService
{
    /**
     * @var OfferRepository
     */
    private $offerRepository;

    /**
     * @var OrderRepository
     */
    private $orderRepository;

    /**
     * @var DataCounterFactory
     */
    private $dataCounterFactory;

    /**
     * CounterService constructor.
     * @param OfferRepository $offerRepository
     * @param OrderRepository $orderRepository
     * @param DataCounterFactory $dataCounterFactory
     */
    public function __construct(
        OfferRepository $offerRepository,
        OrderRepository $orderRepository,
        DataCounterFactory $dataCounterFactory
    )
    {
        $this->offerRepository = $offerRepository;
        $this->orderRepository = $orderRepository;
        $this->dataCounterFactory = $dataCounterFactory;
    }

    /**
     * @param int $userId
     * @return array
     */
    public function getOfferCounts($userId): array
    {
        $entity = 'Offer';
        return [
            Offer::ANSWERED => $this->dataCounterFactory->create(
                $entity,
                Offer::ANSWERED,
                $this->offerRepository->findCountByStatus(Offer::ANSWERED, $userId),
                Offer::ANSWERED),
            Offer::VIEWED => $this->dataCounterFactory->create(
                $entity,
                Offer::VIEWED,
                $this->offerRepository->findCountByStatus(Offer::VIEWED, $userId),
                Offer::VIEWED),
            Offer::SENT => $this->dataCounterFactory->create(
                $entity,
                Offer::SENT,
                $this->offerRepository->findCountByStatus(Offer::SENT, $userId),
                Offer::SENT),
        ];
    }

    /**
     * @param int $userId
     * @return array
     */
    public function getOrderCounts($userId): array
    {
        $entity = 'Order';
        return [
            Offer::CONFIRMED => $this->dataCounterFactory->create(
                $entity,
                Offer::CONFIRMED,
                $this->offerRepository->findCountByStatus(Offer::CONFIRMED, $userId),
                Offer::CONFIRMED),
        ];
    }


}
