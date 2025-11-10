<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SourceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('sources')->insert([
            ['name' => 'Qiita', 'url' => 'https://qiita.com/api/v2/items'],
            ['name' => 'Zenn', 'url' => 'https://zenn.dev/feed']
        ]);
    }
    
}
