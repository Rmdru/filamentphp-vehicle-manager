<?php

declare(strict_types=1);

namespace App\Traits;

trait CountryOptions
{
    public function getCountryOptions(): array
    {
        $countries = config('countries');
        $countriesOptions = [];

        foreach ($countries as $key => $value) {
            $countriesOptions[$key] = $value['name'];
        }

        return $countriesOptions;
    }
}
