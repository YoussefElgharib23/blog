<?php

namespace App\MessageHandler;

use App\Entity\Ip;
use App\Message\StoreIp;
use App\Repository\IpRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class StoreIpHandler implements MessageHandlerInterface
{
    /**
     * @var EntityManagerInterface
     */
    private EntityManagerInterface $entityManager;
    /**
     * @var IpRepository
     */
    private IpRepository $ipRepository;


    public function __construct(EntityManagerInterface $entityManager, IpRepository $ipRepository)
    {
        $this->entityManager = $entityManager;
        $this->ipRepository = $ipRepository;
    }

    public function __invoke(StoreIp $storeIp)
    {
        $ip = $this->ipRepository->findOneBy(['ipAddress' => $storeIp->getIpAddress(), 'ipType' => 'USER']);
        if ( $ip === null ) {
            $this->entityManager->persist(
                (new Ip())->setIpAddress($storeIp->getIpAddress())->setIpType($storeIp->getIpType())
            );
            $this->entityManager->flush();
        }
    }
}