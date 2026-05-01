<?php

declare(strict_types=1);

namespace App\Config;

use CodeIgniter\Config\BaseConfig;

class Ai extends BaseConfig
{
    public string $apiKey  = '';
    public string $model   = '';
    public string $baseUrl = 'https://openrouter.ai/api/v1/chat/completions';

    public function __construct()
    {
        parent::__construct();
        $this->apiKey = env('OPENROUTER_API_KEY', '');
        $this->model  = env('OPENROUTER_MODEL', 'openrouter/free');
    }
}
