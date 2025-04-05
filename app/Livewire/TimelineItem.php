<?php

declare(strict_types=1);

namespace App\Livewire;

use Carbon\CarbonImmutable
use Illuminate\View\View;
use Livewire\Component;

class TimelineItem extends Component
{
    public string $icon;
    public string $logo;
    public string $countryFlag;
    public ?string $heading;
    public ?Carbon $date = null;
    public ?float $price = null;
    public ?int $mileage = null;
    public ?string $location = null;
    public array $badges = [];
    public array $roadConfig = [];
    public ?array $link = [];

    public function mount($icon, $logo, $countryFlag, $heading, $price, $mileage, $location, $badges, $roadConfig, $link): void
    {
        $this->icon = $icon;
        $this->logo = $logo;
        $this->countryFlag = $countryFlag;
        $this->heading = $heading;
        $this->price = $price;
        $this->mileage = $mileage;
        $this->location = $location;
        $this->badges = $badges;
        $this->roadConfig = $roadConfig;
        $this->link = $link;
    }

    public function render(): View
    {
        return view('livewire.timeline-item');
    }
}
