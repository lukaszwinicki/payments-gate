<?php 

namespace App\Dtos\Dashboard;

readonly class DashboardBalancesDto
{
    public function __construct(
        public float $pln,
        public float $eur,
        public float $usd
    ) {
    }
}