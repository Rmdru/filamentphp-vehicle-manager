<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\StatusNotificationOk;
use App\Enums\VehicleStatus;
use App\Models\Vehicle;
use App\Support\StatusNotification;

class VehicleStatusService
{
    private array $types = [];

    public function __construct()
    {
        $this->types = StatusNotification::types();
    }

    public function getNotifications(Vehicle $vehicle): array
    {
        $notifications = [];

        if (in_array($vehicle->status, [VehicleStatus::Suspended->value, VehicleStatus::Sold->value, VehicleStatus::Destroyed->value])) {
            $notifications[] = $this->createNotification(
                type: 'success',
                text: __('No information available'),
                key: 'no-info',
                icon: 'fas-smile'
            );
            
            return $notifications;
        }

        $notificationMappings = StatusNotification::configuration();

        foreach ($notificationMappings as $key => $mapping) {
            $status = $vehicle->{$mapping['statusKey']} ?? null;

            if (empty($status)) {
                continue;
            }

            $this->processNotification($notifications, $status ?? null, $mapping, $key);
        }

        if (empty($notifications)) {
            $notifications[] = $this->createNotification(
                type: 'success',
                text: collect(StatusNotificationOk::cases())->map(fn ($case) => $case->getLabel())->random(),
                key: 'ok',
                icon: 'fas-smile'
            );
        }

        return $notifications;
    }

    public function getBadge(Vehicle $vehicle): array
    {
        $notifications = $this->getNotifications($vehicle);

        if (empty($notifications)) {
            return [];
        }

        $notification = collect($notifications)->sortBy('priority')->first();

        if (! isset($this->types[$notification['type']])) {
            return [];
        }

        return $this->types[$notification['type']];
    }

    private function processNotification(array &$notifications, ?array $status, array $mapping, string $key): void
    {
        if (! isset($status)) {
            return;
        }

        $thresholds = $mapping['thresholds'];
        $thresholdType = $mapping['thresholdType'];
        $thresholdCompareKeyTime = $mapping['thresholdCompareKeyTime'] ?? 'time';
        $messages = $mapping['messages'];
        $icon = $mapping['icon'];
        $hasModal = $status['hasModal'] ?? false;
        $data = $status['data'] ?? [];
        
        $compareValueTime = $status[$thresholdCompareKeyTime] ?? null;
        $recordCount = $status['recordCount'] ?? 0;

        if ($this->meetsCriteria('critical', $thresholds, $thresholdType, $compareValueTime, $recordCount)) {
            $notifications[] = $this->createNotification('critical', $messages['critical'], $key, $icon, $hasModal, $data);
            return;
        }

        if ($this->meetsCriteria('warning', $thresholds, $thresholdType, $compareValueTime, $recordCount)) {
            $notifications[] = $this->createNotification('warning', $messages['warning'], $key, $icon, $hasModal, $data);
            return;
        }

        if ($this->meetsCriteria('info', $thresholds, $thresholdType, $compareValueTime, $recordCount)) {
            $notifications[] = $this->createNotification('info', $messages['info'], $key, $icon, $hasModal, $data);
        }
    }

    private function meetsCriteria(
        string $level,
        array $thresholds,
        string $thresholdType,
        ?float $compareValueTime,
        float $recordCount
    ): bool {
        if (! isset($thresholds[$level])) {
            return false;
        }

        return match($thresholdType) {
            'time' => $compareValueTime < $thresholds[$level],
            'recordCount' => $recordCount > $thresholds[$level],
            default => false,
        };
    }

    private function createNotification(
        string $type,
        string $text,
        string $key,
        string $icon = '',
        bool $hasModal = false,
        array $data = []
    ): array {
        return array_merge($this->types[$type], [
            'key' => $key,
            'type' => $type,
            'text' => $text,
            'icon' => $icon,
            'typeIcon' => $this->types[$type]['icon'],
            'priority' => $this->types[$type]['priority'],
            'hasModal' => $hasModal,
            'data' => $data,
        ]);
    }
}