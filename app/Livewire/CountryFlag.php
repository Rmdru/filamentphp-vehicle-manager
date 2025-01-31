<?php

namespace App\Livewire;

use Livewire\Component;

class CountryFlag extends Component
{
    public $country;
    public $showName;

    public function mount($country, $showName = false): void
    {
        $this->country = $country;
        $this->showName = $showName;
    }

    public function render()
    {
        $country = $this->getCountryConfig();

        $countries['showName'] = $this->showName;

        return view('livewire.country-flag', $country);
    }

    private function getCountryConfig(): ?array
    {
        $countries = config('countries');

        return ! empty($this->country) ? $countries[$this->country] : null;
    }
}
