<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\User;

class EmptyFileException extends Exception {}

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        if (!DB::table('users')->where('email', "test@test.com")->first()) {
            $admin = new User([
                'name' => "Admin",
                'email' => 'test@test.com',
                'password' => bcrypt('secretaa'),
            ]);

            $guest = new User([
                'name' => "guest",
                'email' => 'guest',
                'password' => bcrypt('secretaa'),
            ]);

            $admin->save();
            $guest->save();
        }

        $path = base_path() . "\\database\\seeds\\file-types.json";
        $json = json_decode(file_get_contents($path), true);

        //if (!isset($json["types"])) throw new EmptyFileException($path . " is empty");

        foreach($json["types"] as $type) {
            DB::table('file_types')->insert([
                'name' => $type["name"],
                "allowed_types" => implode(",", $type["extensions"])
            ]);
        }

    }
}