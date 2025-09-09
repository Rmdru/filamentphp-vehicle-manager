<?php

namespace App\Livewire;

use Illuminate\View\View;
use Livewire\Component;

class RoadBadge extends Component
{
    public $roadType;
    public $road;
    public $country;

    public function mount($roadType, $road, $country): void
    {
        $this->roadType = $roadType;
        $this->road = $road;
        $this->country = $country;
    }

    public function render(): View
    {
        $badges = [];

        $roads = $this->getRoadConfig();

        foreach ($roads as $road) {
            $badges[] = [
                'roadTypeConfig' => $road['roadTypeConfig'],
                'roadNumber' => $road['road'],
            ];
        }

        return view('livewire.road-badge', [
            'badges' => $badges,
        ]);
    }

    private function getRoadConfig(): array
    {
        $country = $this->getCountryConfig();

        $roadTypeConfig = $country['road_types'][$this->roadType] ?? null;

        if (empty($roadTypeConfig)) {
            $roadTypeConfig = [
                'color' => 'text-black',
                'background_color' => 'bg-white',
            ];

            $road = $this->road;

            return [
                [
                    'roadTypeConfig' => $roadTypeConfig,
                    'road' => $road,
                ],
            ];
        }

        $roads = [];

        if (is_string($this->road)) {
            $roadComponents = $this->getRoadComponents($this->road);

            $this->road = ($roadTypeConfig['prefix'] ?? '') . $roadComponents['road'];

            return [
                [
                    'roadTypeConfig' => $roadTypeConfig,
                    'road' => $this->road,
                ]
            ];
        }

        foreach ($this->road as $road) {
            $roadComponents = $this->getRoadComponents($road);

            $road = $roadTypeConfig['prefix'] . $roadComponents['road'];

            $roads[] = [
                'roadTypeConfig' => $roadTypeConfig,
                'road' => $road,
            ];
        }

        return $roads;
    }

    private function getCountryConfig(): ?array
    {
        $countries = config('countries');

        return ! empty($this->country) ? $countries[$this->country] : null;
    }

    private function getRoadComponents(string $string): array
    {
        $position = strcspn($string, '0123456789');

        $prefix = substr($string, 0, $position);
        $road = substr($string, $position);

        return [
            'prefix' => $prefix,
            'road' => $road,
        ];
    }
}
