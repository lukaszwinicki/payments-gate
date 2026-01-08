<?php

namespace App\Dtos\Dashboard;

readonly class TransactionNotificationsListDto
{
    /** @param TransactionNotificationDto[] $notifications */
    public function __construct(
        public array $notifications
    ) {
    }
}
