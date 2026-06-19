<?php

namespace App\Services;

use App\Models\Notification;
use Kreait\Firebase\Factory;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    protected static function messaging()
    {
        return (new Factory)
            ->withServiceAccount(base_path('storage/app/firebase.json'))
            ->createMessaging();
    }

    public static function send(
        $user,
        string $title,
        string $body,
        string $type,
        $referenceId = null
    ) {
        Notification::create([
            'employee_id' => $user->id,
            'title' => $title,
            'body' => $body,
            'type' => $type,
            'reference_id' => $referenceId,
            'is_read' => false,
        ]);

        if (!empty($user->fcm_token)) {
           


            try {

                $message = [
                    'token' => $user->fcm_token,
                    'notification' => [
                        'title' => $title,
                        'body' => $body,
                    ],
                    'data' => [
                        'type' => $type,
                        'reference_id' => (string) ($referenceId ?? ''),
                    ],
                ];

                self::messaging()->send($message);

            } catch (\Exception $e) {

                Log::error('FCM Error: '.$e->getMessage());

            }
        }
    }
}