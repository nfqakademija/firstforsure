<?php

namespace App\Service\Admin\Dashboard;

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
    public function getOrderCounts($userId): array
    {
        return [
            'answered' => $this->dataCounterFactory->create(
                'Order',
                OrderStatus::ANSWERED,
                $this->offerRepository->findCountByStatus(OrderStatus::ANSWERED, $userId),
                'Viewed'),
            'accepted' => $this->dataCounterFactory->create(
                'Order',
                OrderStatus::CONFIRMED,
                $this->offerRepository->findCountByStatus(OrderStatus::CONFIRMED, $userId),
                'Confirmed'),
            'viewed' => $this->dataCounterFactory->create(
                'Order',
                OrderStatus::VIEWED,
                $this->offerRepository->findCountByStatus(OrderStatus::CONFIRMED, $userId),
                'Viewed'),
            'sent' => $this->dataCounterFactory->create(
                'Order',
                OrderStatus::SENT,
                $this->offerRepository->findCountByStatus(OrderStatus::CONFIRMED, $userId),
                'Sent'),
        ];
    }

    /**
     * @param int $userId
     * @return array
     */
    public function getOfferCounts($userId): array
    {
        return [
            'viewed' => $this->dataCounterFactory->create(
                'Offer',
                OfferStatus::VIEWED,
                $this->offerRepository->findCountByStatus(OfferStatus::VIEWED, $userId),
                'Viewed'),
            'sent' => $this->dataCounterFactory->create(
                'Offer',
                OfferStatus::SENT,
                $this->offerRepository->findCountByStatus(OfferStatus::SENT, $userId),
                'Sent'),
        ];
    }


}
