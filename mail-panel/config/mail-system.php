<?php

return [
    /*
    | Daily cap = maximum allowed sends per tenant per day.
    | Actual sends can be lower — never force volume to hit the cap.
    */
    'default_daily_cap' => (int) env('MAIL_SYSTEM_DAILY_CAP', 15),

    'warmup_caps' => [
        1 => 15,
        2 => 30,
        3 => 50,
        4 => 80,
    ],

    'api_key_header' => 'X-API-Key',

    'message_id_prefix' => 'msg_',
];
