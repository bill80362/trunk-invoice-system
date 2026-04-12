<?php

namespace Database\Seeders;

use App\Models\CarrierType;
use App\Models\Client;
use App\Models\Driver;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        User::factory()->create([
            'name' => 'Admin',
            'email' => 'admin@admin.com',
            'password' => bcrypt('password'),
        ]);
        // 地點
        $this->call(LocationSeeder::class);

        // 司機
        Driver::create(['name' => '陳師傅', 'phone' => '0912345678']);
        Driver::create(['name' => '王師傅', 'phone' => '0923456789']);
        Driver::create(['name' => '林師傅', 'phone' => '0934567890']);

        // 托運方式
        foreach (['大車', '大車上櫃', '上櫃', '1大1小', '加重上櫃', '2大車', '3大車'] as $type) {
            CarrierType::create(['name' => $type]);
        }

        // 貨主
        Client::create(['name' => '甲公司', 'contact' => '張經理', 'phone' => '02-12345678']);
        Client::create(['name' => '乙公司', 'contact' => '李經理', 'phone' => '02-87654321']);

        // 費率表（需自行設定）

        // 系統設定
        Setting::set('additional_stop_fee', '100');
        Setting::set('issuer_name', 'XX運輸有限公司');
        Setting::set('issuer_address', '台北市中正區XX路1號');
        Setting::set('issuer_phone', '02-12345678');
    }
}
