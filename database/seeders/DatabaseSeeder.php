<?php

namespace Database\Seeders;

use App\Models\CarrierType;
use App\Models\Client;
use App\Models\Driver;
use App\Models\FreightRate;
use App\Models\Location;
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
        $taipei = Location::create(['name' => '台北']);
        $taoyuan = Location::create(['name' => '桃園']);
        $hsinchu = Location::create(['name' => '新竹']);
        $taichung = Location::create(['name' => '台中']);
        $kaohsiung = Location::create(['name' => '高雄']);

        // 司機
        Driver::create(['name' => '陳師傅', 'phone' => '0912345678']);
        Driver::create(['name' => '王師傅', 'phone' => '0923456789']);
        Driver::create(['name' => '林師傅', 'phone' => '0934567890']);

        // 托運方式
        $fullTruck = CarrierType::create(['name' => '整車']);
        $ltl = CarrierType::create(['name' => '零擔']);

        // 貨主
        Client::create(['name' => '甲公司', 'contact' => '張經理', 'phone' => '02-12345678']);
        Client::create(['name' => '乙公司', 'contact' => '李經理', 'phone' => '02-87654321']);

        // 費率表
        FreightRate::create(['origin_id' => $taipei->id, 'destination_id' => $taoyuan->id, 'carrier_type_id' => $fullTruck->id, 'base_price' => 2500]);
        FreightRate::create(['origin_id' => $taipei->id, 'destination_id' => $hsinchu->id, 'carrier_type_id' => $fullTruck->id, 'base_price' => 3500]);
        FreightRate::create(['origin_id' => $taipei->id, 'destination_id' => $taichung->id, 'carrier_type_id' => $fullTruck->id, 'base_price' => 5000]);
        FreightRate::create(['origin_id' => $taipei->id, 'destination_id' => $kaohsiung->id, 'carrier_type_id' => $fullTruck->id, 'base_price' => 8000]);
        FreightRate::create(['origin_id' => $taipei->id, 'destination_id' => $taoyuan->id, 'carrier_type_id' => $ltl->id, 'base_price' => 1500]);
        FreightRate::create(['origin_id' => $taipei->id, 'destination_id' => $hsinchu->id, 'carrier_type_id' => $ltl->id, 'base_price' => 2000]);

        // 系統設定
        Setting::set('additional_stop_fee', '100');
        Setting::set('issuer_name', 'XX運輸有限公司');
        Setting::set('issuer_address', '台北市中正區XX路1號');
        Setting::set('issuer_phone', '02-12345678');
    }
}
