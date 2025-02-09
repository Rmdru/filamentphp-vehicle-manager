<?php

declare(strict_types=1);

namespace App\Traits;

trait IsMobile
{
    public function isMobile(): bool
    {
        return request()->header('User-Agent') && preg_match('/Mobile|Android|iPhone|iPad/i', request()->header('User-Agent'));
    }
}
