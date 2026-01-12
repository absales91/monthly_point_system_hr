<?php

namespace App\Services;

use App\Models\Notification;
use Kreait\Firebase\Factory;

class NotificationService
{
    protected static function messaging()
    {
        return (new Factory)
            ->withServiceAccount(storage_path('app/firebase.json'))
            ->createMessaging();
    }

    public static function send(
        $employee,
        string $title,
        string $body,
        string $type,
        $referenceId = null
    ) {
        // 1️⃣ Save notification in DB (in-app history)
        Notification::create([
            'employee_id' => $employee->id,
            'title' => $title,
            'body' => $body,
            'type' => $type,
            'reference_id' => $referenceId,
            'is_read' => false,
        ]);

        // 2️⃣ Send FCM push notification
        if (!empty($employee->fcm_token)) {

            $message = [
                'token' => $employee->fcm_token,
                'notification' => [
                    'title' => $title,
                    'body' => $body,
                ],
                'data' => [
                    'type' => $type,
                    'reference_id' => (string) $referenceId,
                ],
            ];

            self::messaging()->send($message);
        }
    }
}
