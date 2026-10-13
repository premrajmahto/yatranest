<?php

namespace App\Models;

use App\Core\Model;

class SiteSetting extends Model {
    protected string $table = 'site_settings';
    private static ?array $cachedSettings = null;

    public function get(string $key, mixed $default = null): mixed {
        if (self::$cachedSettings === null) {
            $this->loadAll();
        }
        return self::$cachedSettings[$key] ?? $default;
    }

    public function set(string $key, mixed $value, string $group = 'general', ?string $desc = null): bool {
        $existing = $this->findBy('setting_key', $key);
        if ($existing) {
            $updated = $this->update($existing['id'], [
                'setting_value' => (string)$value,
                'setting_group' => $group,
                'description' => $desc ?? $existing['description']
            ]);
        } else {
            $updated = (bool)$this->create([
                'setting_key' => $key,
                'setting_value' => (string)$value,
                'setting_group' => $group,
                'description' => $desc
            ]);
        }
        self::$cachedSettings[$key] = $value;
        return $updated;
    }

    public function loadAll(): array {
        $rows = $this->all();
        self::$cachedSettings = [];
        foreach ($rows as $row) {
            self::$cachedSettings[$row['setting_key']] = $row['setting_value'];
        }
        return self::$cachedSettings;
    }
}
