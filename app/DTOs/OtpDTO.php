<?php

namespace App\DTOs;

use App\Enums\CurrencyCodeEnum;

class OtpDTO
{
    public string $recipient;
    public string $channel;
    public string $actionType;
    public int $userID;

    public function __construct(
        string $recipient,
        string $channel,
        string $actionType,
        int $userID
    ) {
        $this->recipient = $recipient;
        $this->channel = $channel;
        $this->actionType = $actionType;
        $this->userID = $userID;
    }
}