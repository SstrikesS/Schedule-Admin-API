<?php

namespace App\Libs\Setting;

use App\Libs\Serializer\Normalize;
use Illuminate\Http\Request;

class DataClient
{
    protected ?string $platform;
    protected ?array $agent;
    protected string $langCode;

    public function __construct(?array $data = [])
    {
        $this->platform = Normalize::initString($data, 'platform');
        $this->langCode = Normalize::initString($data, 'langCode') ?? 'vi';
        $this->agent = Normalize::initArray($data, 'agent');
    }

    public function getPlatform(): string
    {
        return $this->platform;
    }

    public function getLangCode(): string
    {
        return $this->langCode;
    }

    public function getAgent(): array
    {
        return $this->agent;
    }

    public function isEncrypt(array $compare = ['web', 'desktop']): bool
    {
        if (!$this->platform) {
            return false;
        }

        return in_array($this->platform, $compare);
    }

    public function initAgent(Request $request): void
    {
        if ($this->agent) {
            if (isset($this->agent['geoLocation'])) {
                if (isset($this->agent['geoLocation']['ipAddress']) && filter_var($this->agent['geoLocation']['ipAddress'], FILTER_VALIDATE_IP)) {
                    $_SERVER['REMOTE_ADDR'] = $this->agent['geoLocation']['ipAddress'];
                    $request->server->set('REMOTE_ADDR', $this->agent['geoLocation']['ipAddress']);
                }
            }
        }
    }
}
