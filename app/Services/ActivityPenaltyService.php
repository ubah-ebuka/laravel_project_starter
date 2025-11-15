<?php 

namespace App\Services;

use App\Enums\PenaltyEnum;
use App\Models\User;

class ActivityPenaltyService
{
    public function applyPenalty(int $userId, string $penaltyAction): string
    {
        $user = User::find($userId);
        $message = 'Penalty applied';

        switch ($penaltyAction) {
            case PenaltyEnum::SUSPEND_USER_ACCOUNT->value:
                $user->status = 'suspended';
                $user->save();
                $message = 'User account has been suspended';
                break;
        }

        return $message;
    }
}