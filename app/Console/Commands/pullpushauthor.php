<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use App\Authors;
use DB;

class pullpushauthor extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'pullpushauthor';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Pull List of Author and Push/Save to Database';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $ts = time();
        $pub_key = $_ENV['PUBLIC_KEY'];
        $priv_key = $_ENV['PRIVATE_KEY'];
        $hash_key = md5($ts.$priv_key.$pub_key);

        $confirm = $this->confirm('Do you want to Pull and Save Author to Database?');

        if ($confirm) {

            $curl = curl_init();
            
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

            $query = array(
                "orderBy" => "lastName",
                "limit" => 10,
                'apikey' => $pub_key,
                'ts' => $ts,
                'hash' => $hash_key
            );
            
            $def_url = 'http://gateway.marvel.com/v1/public/creators?' . http_build_query($query);

            curl_setopt($curl, CURLOPT_URL, $def_url);

            $result = json_decode(curl_exec($curl), true);

            curl_close($curl);
            
            foreach ($result['data']['results'] as $value) {

                DB::table('authors')->insertOrIgnore([
                    [
                        'first_name' => $value['firstName'],
                        'last_name' => $value['lastName'],
                        'thumbnail_url' => $value['thumbnail']['path']
                    ]
                ]);

                $this->pullsaveComics($value['id']);
                $this->info('....');
            }

            $this->info('Data Sunccesfully Pulled and Saved to Database');

        }
    }

    public function pullsaveComics( $authorId ){

        $ts = time();
        $pub_key = $_ENV['PUBLIC_KEY'];
        $priv_key = $_ENV['PRIVATE_KEY'];
        $hash_key = md5($ts.$priv_key.$pub_key);


        $curl = curl_init();
            
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

            $query = array(
                'apikey' => $pub_key,
                'ts' => $ts,
                'hash' => $hash_key
            );
            
            $def_url = 'http://gateway.marvel.com/v1/public/creators/'. $authorId .'/comics?'. http_build_query($query);

            curl_setopt($curl, CURLOPT_URL, $def_url);

            $result = json_decode(curl_exec($curl), true);

            curl_close($curl);
            
            foreach ($result['data']['results'] as $value) {

                DB::table('comics')->insertOrIgnore([
                    [
                        'title' => $value['title'],
                        'series_name' => $value['series']['name'],
                        'description' => $value['description'],
                        'page_count' => $value['pageCount'],
                        'thumbnail_url' => $value['thumbnail']['path'].$value['thumbnail']['extension']
                    ]
                ]);

                DB::table('author_comics')->insertOrIgnore([
                    [
                        'author_id' => $authorId,
                        'comic_id' => $value['id'],
                    ]
                ]);
            }
    }
}
