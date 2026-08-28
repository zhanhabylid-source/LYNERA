<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Persentase DP Minimum
    |--------------------------------------------------------------------------
    |
    | Nilai dalam persen (0-100). Contoh 30 berarti DP minimum 30% dari total.
    |
    */
    'dp_min_percent' => (float) env('PAYMENT_DP_MIN_PERCENT', 30),
];

