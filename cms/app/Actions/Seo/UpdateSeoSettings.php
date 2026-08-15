<?php

namespace App\Actions\Seo;

use App\Actions\Access\RecordAudit;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class UpdateSeoSettings
{
    public function __construct(private readonly RecordAudit $audit) {}

    public function execute(array $data, User $actor): void
    {
        DB::transaction(function () use ($data, $actor): void {
            $changed = [];
            $target = null;
            foreach ($data as $key => $value) {
                $stored = is_bool($value) ? ($value ? '1' : '0') : $value;
                $setting = Setting::firstOrNew(['key' => $key]);
                if ((string) $setting->value !== (string) $stored) {
                    $changed[] = $key;
                }
                $setting->value = $stored;
                $setting->save();
                $target ??= $setting;
            }
            Cache::forget('content.sitemap');
            $this->audit->execute($actor, 'seo.settings_updated', $target, ['changed_keys' => array_slice($changed, 0, 20)]);
        });
    }
}
