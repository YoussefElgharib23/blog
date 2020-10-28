<?php

namespace App\Entity;

use App\Repository\IpRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=IpRepository::class)
 * @ORM\Table(name="ips")
 */
class Ip
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $ipAddress;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $ipType;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getIpAddress(): ?string
    {
        return $this->ipAddress;
    }

    public function setIpAddress(string $ipAddress): self
    {
        $this->ipAddress = $ipAddress;

        return $this;
    }

    public function getIpType(): ?string
    {
        return $this->ipType;
    }

    public function setIpType(string $ipType): self
    {
        $this->ipType = $ipType;

        return $this;
    }
}
