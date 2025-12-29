<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('categories')->insert([
            ['name' => 'React', 'slug' => 'react', 'icon_url' => '/icon/react.svg'],
            ['name' => 'Docker', 'slug' => 'docker', 'icon_url' => '/icon/docker.svg'],
            ['name' => 'Java', 'slug' => 'java', 'icon_url' => '/icon/java.svg'],
            ['name' => 'JavaScript', 'slug' => 'javascript', 'icon_url' => '/icon/javascript.svg'],
            ['name' => 'NextJs', 'slug' => 'nextjs', 'icon_url' => '/icon/nextjs.svg'],
            ['name' => 'Typescript', 'slug' => 'typescript', 'icon_url' => '/icon/typescript.svg'],
            ['name' => 'Python', 'slug' => 'python', 'icon_url' => '/icon/python.svg'],
            ['name' => 'Laravel', 'slug' => 'laravel', 'icon_url' => '/icon/Laravel.svg'],
        ]);
    }
}
