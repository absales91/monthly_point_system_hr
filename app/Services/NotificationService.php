<?php

namespace App\Services;

use App\Models\Notification;
use Kreait\Firebase\Factory;

class NotificationService
{
    protected static function messaging()
    {
        return (new Factory)
            ->withServiceAccount(public_path('../storage/app/firebase.json'))
            ->createMessaging();
    }

    public static function send(
        $user,
        string $title,
        string $body,
        string $type,
        $referenceId = null
    ) {
        // 1️⃣ Save notification in DB (in-app history)
        Notification::create([
            'employee_id' => $user->id,   // 🔥 FIXED
            'title' => $title,
            'body' => $body,
            'type' => $type,
            'reference_id' => $referenceId,
            'is_read' => false,
        ]);

        // 2️⃣ Send FCM push notification
        if (!empty($user->fcm_token) || $user->fcm_token !== null) {

            $message = [
                'token' => $user->fcm_token,
                'notification' => [
                    'title' => $title,
                    'body' => $body,
                ],
                'data' => [
                    'type' => $type,
                    'reference_id' => (string) $referenceId ?? '',
                ],
            ];

            self::messaging()->send($message);
        }
    }
}
