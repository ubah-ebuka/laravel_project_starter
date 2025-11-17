<?php

namespace App\Http\Requests\Admin;

use App\Enums\PenaltyEnum;
use App\Models\User;
use App\Models\UserActivityAttempt;
use App\Services\ActivityPenaltyService;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Symfony\Component\HttpKernel\Exception\HttpException;

class UserLoginRequest extends FormRequest
{
    private ?User $user;

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'email'],
            'password' => ['required', 'string']
        ];
    }

    public function messages(): array
    {
        return [
            'email.required' => 'Email address is required.',
            'email.email' => 'Email address must be a valid email format.',
            'password.required' => 'Password is required.',
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator) {
                $user = User::where(['email' => $this->input('email'), 'type' => 'admin', 'status' => 'active'])->first();

                if (!$user) {
                    $validator->errors()->add('email', 'This email address does not exist or is not active.');
                    return;
                }

                $previousAttempt = UserActivityAttempt::where(['user_id' => $user->id, 'activity_type' => "user-login"])->first();

                if (!Hash::check($this->input('password'), $user->password)) {
                    if(!empty($previousAttempt)) {
                        if($previousAttempt->attempts >= $previousAttempt->max_attempts) {
                            if(!empty($previousAttempt->penalty_action)){
                                $penaltyMessage = (new ActivityPenaltyService())->applyPenalty($user->id, $previousAttempt->penalty_action);
                                $previousAttempt->attempts = 0;
                                $previousAttempt->save();

                                throw new HttpException(401, 'Too many incorrect login attempts. '.$penaltyMessage);
                                return;
                            }
                        }else{
                            $previousAttempt->attempts += 1;
                            $previousAttempt->save();
                        }
                    }else{
                        UserActivityAttempt::create([
                            'user_id' => $user->id,
                            'activity_type' => 'user-login',
                            'attempts' => 1,
                            'max_attempts' => 3,
                            'penalty_action' => PenaltyEnum::SUSPEND_USER_ACCOUNT->value
                        ]);
                    }

                    $validator->errors()->add('password', 'The provided password is incorrect.');
                    return;
                }

                if(!empty($previousAttempt)) {
                    $previousAttempt->attempts = 0;
                    $previousAttempt->save();
                }

                $this->user = $user;
            }
        ];
    }

    public function getValidatedUser(): ?User
    {
        return $this->user;
    }
}
