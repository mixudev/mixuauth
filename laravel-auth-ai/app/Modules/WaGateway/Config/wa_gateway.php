<?php

return array (
  'provider' => 'fonnte',
  'providers' => 
  array (
    'fonnte' => 
    array (
      'base_url' => 'https://api.fonnte.com/send',
      'token' => env('WA_FONNTE_TOKEN', ''),
      'token_header' => 'Authorization',
      'token_prefix' => '',
      'as_form' => true,
      'timeout' => 15,
      'default_country_code' => '62',
    ),
    'official' => 
    array (
      'base_url' => '',
      'token' => '',
      'token_header' => 'Authorization',
      'token_prefix' => 'Bearer',
      'as_form' => false,
      'timeout' => 15,
      'default_country_code' => '62',
    ),
  ),
  'guardrail' => 
  array (
    'enabled' => true,
    'daily_limit_per_config' => 500,
    'prevent_duplicate_within_seconds' => 120,
    'quiet_hours_start' => '22:00',
    'quiet_hours_end' => '07:00',
    'allow_critical_in_quiet_hours' => true,
    'default_random_delay' => '3-8',
  ),
);
