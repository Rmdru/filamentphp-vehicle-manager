<?php

declare(strict_types=1);

namespace App\Traits;

trait PowerTrainOptions
{
    public function getPowerTrainOptions(): array
    {
        $powertrains = trans('powertrains');
        $powertrainsOptions = [];

        foreach ($powertrains as $key => $value) {
            $powertrainsOptions[$key] = $value['name'];
        }

        return $powertrainsOptions;
    }
}
