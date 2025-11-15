<?php

namespace App\Services;

use App\DTOs\OtpDTO;
use App\Enums\OtpActionTypeEnum;
use App\Enums\PenaltyEnum;
use App\Models\Otp;
use App\Models\User;
use App\Models\UserActivityAttempt;
use Symfony\Component\HttpKernel\Exception\HttpException;

class OtpService
{
    public function __construct(private ActivityPenaltyService $activityPenaltyService) 
    {

    }

    public function generateOtp(OtpDTO $otpDto): string
    {
        $otp = Otp::where([
                    'recipient' => $otpDto->recipient, 
                    'action_type' => $otpDto->actionType, 
                    'is_used' => false, 
                    'user_id' => $otpDto->userID
                ])
                ->where('expires_at', '>', now())
                ->first();

        if ($otp) {
            return $otp->token;
        }

        $token = rand(100000, 999999);

        Otp::create([
            'recipient' => $otpDto->recipient,
            'channel' => $otpDto->channel,
            'action_type' => $otpDto->actionType,
            'token' => $token,
            'user_id' => $otpDto->userID,
            'expires_at' => now()->addMinutes(10),
            'attempts' => 0
        ]);

        return $token;
    }

    private function determinePenaltyActionByOtpActionType(string $actionType): string|null
    {
        return match ($actionType) {
            OtpActionTypeEnum::RESET_PASSWORD->value => PenaltyEnum::SUSPEND_USER_ACCOUNT->value,
            default => null,
        };
    }

    public function validateOtp(string $recipient, string $actionType, string $token): bool
    {
        $otp = Otp::where([
                    'recipient' => $recipient, 
                    'action_type' => $actionType,
                    'is_used' => false
                ])
                ->where('expires_at', '>', now())
                ->first();

        if ($otp) {
            //check previous attempts
            $previousAttempt = UserActivityAttempt::where(['user_id' => $otp->user_id, 'activity_type' => $actionType])->first();

            if(!empty($previousAttempt) && $previousAttempt->attempts >= $previousAttempt->max_attempts) {
                
                if(!empty($previousAttempt->penalty_action)) {
                    $penaltyMessage = $this->activityPenaltyService->applyPenalty($otp->user_id, $previousAttempt->penalty_action);
                    $previousAttempt->attempts = 0;
                    $previousAttempt->save();

                    throw new HttpException(422, 'Too many incorrect attempts. '.$penaltyMessage);
                }

                return false;
            }elseif($otp->token != $token) {
                $penalty = $this->determinePenaltyActionByOtpActionType($otp->action_type);

                if (empty($previousAttempt) && !empty($penalty)) {
                    UserActivityAttempt::create([
                        'user_id' => $otp->user_id,
                        'activity_type' => $actionType,
                        'attempts' => 1,
                        'max_attempts' => 5,
                        'penalty_action' => $penalty
                    ]);
                }else if(!empty($previousAttempt)) {
                    $previousAttempt->attempts += 1;
                    $previousAttempt->save();
                }
                
                return false;
            }

            //reset attempts on successful validation
            if(!empty($previousAttempt)) {
                $previousAttempt->attempts = 0;
                $previousAttempt->save();
            }

            return true;
        }

        return false;
    }

    public function invalidateOtps(string $recipient, string $actionType): void
    {
        Otp::where([
            'recipient' => $recipient, 
            'action_type' => $actionType,
            'is_used' => false
        ])->update(['is_used' => true]);
    }
}