<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    private static array $dropConfigs = ['paypal_username', 'paypal_password', 'paypal_secret', 'paypal_certificate'];

    private static array $newConfigs = ['paypal_client_id', 'paypal_client_secret'];

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        foreach (self::$newConfigs as $config) {
            \App\Models\Config::insert(['name' => $config]);
        }
        foreach (self::$dropConfigs as $config) {
            \App\Models\Config::destroy(['name' => $config]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        foreach (self::$newConfigs as $config) {
            \App\Models\Config::destroy(['name' => $config]);
        }
        foreach (self::$dropConfigs as $config) {
            \App\Models\Config::insert(['name' => $config]);
        }
    }
};
