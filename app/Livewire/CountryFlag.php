<?php

namespace App\Livewire;

use Livewire\Component;

class CountryFlag extends Component
{
    public $country;


    public function mount($country): void
    {
        $this->country = $country;
    }

    public function render()
    {
        $country = $this->getCountryConfig();

        return view('livewire.country-flag', $country);
    }

    private function getCountryConfig(): array
    {
        $countries = config('countries');

        return $countries[$this->country];
    }
}
