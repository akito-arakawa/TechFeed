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
            ['name' => 'React', 'slug' => 'react'],
            ['name' => 'Docker',  'slug' => 'docker'],
            ['name' => 'Java',  'slug' => 'java'],
            ['name' => 'JavaScript',  'slug' => 'javascript'],
            ['name' => 'NextJs',  'slug' => 'nextjs'],
            ['name' => 'Typescript',  'slug' => 'typescript'],
            ['name' => 'Python',  'slug' => 'python'],
        ]);
    }
}
