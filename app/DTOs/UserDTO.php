<?php

namespace App\DTOs;

use App\Enums\CurrencyCodeEnum;

class UserDTO
{
    public string $first_name;
    public string $last_name;
    public string $email;
    public string $password;
    public string $phone;
    public string $type;
    public string $status;
    public CurrencyCodeEnum $currency_code;

    public function __construct(
        string $first_name,
        string $last_name,
        string $email,
        string $password,
        string $phone,
        string $type,
        string $status,
        CurrencyCodeEnum $currency_code
    ) {
        $this->first_name = $first_name;
        $this->last_name = $last_name;
        $this->email = $email;
        $this->password = $password;
        $this->phone = $phone;
        $this->type = $type;
        $this->status = $status;
        $this->currency_code = $currency_code;
    }
}