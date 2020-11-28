<?php

namespace App\Message;

class StoreIp
{
    private string $ipAddress;

    private string $ipType;

    public function __construct(string $ipAddress, string $ipType)
    {
        $this->ipAddress = $ipAddress;
        $this->ipType = $ipType;
    }

    /**
     * @return string
     */
    public function getIpType(): string
    {
        return $this->ipType;
    }

    /**
     * @return string
     */
    public function getIpAddress(): string
    {
        return $this->ipAddress;
    }
}