<?php namespace Models;

class Movie
{
    private $connection;
    // private $table = 'engima.movie';
    // Attributes for MYSQL table:
    // public $id_movie;
    // public $nama;
    // public $runtime;
    // public $tanggal_rilis;
    // public $sinopsis;
    // public $poster;
    public $table;
    public $id;
    public $title;
    public $runtime;
    public $release_date;
    public $overview;
    public $poster_path;
    public $vote_average;

    public function __construct($database)
    {
        $this->connection = $database;
    }
    // Get All Movies
    public function getAllMovies($database)
    {
        // $query = "SELECT id_movie, nama, poster FROM " . $this->table .";";
        // $execute = mysqli_query($database, $query);
        // $result = mysqli_fetch_all($execute, MYSQLI_ASSOC);
        // if ($result) {
        //     return $result;
        // } else {
        //     return '500';
        // }
        $result = file_get_contents("https://api.themoviedb.org/3/movie/now_playing?api_key=1c55fae85a93267bd4a366fde9a90a4b&language=en-US");
        $result = json_decode($result, true);
        $table = $result["results"];

        return $table;
    }

    public function getAllMoviesWithKeyword($database, $params)
    {
        $rows_skipped = ($params['page'] - 1) * 5;
        $query = "SELECT id_movie, nama, sinopsis, poster FROM " . $this->table ." WHERE nama LIKE '%". $params['keyword'] . "%' LIMIT 5 OFFSET ". $rows_skipped . ";";
        // echo $query;
        $execute = mysqli_query($database, $query);
        $result = mysqli_fetch_all($execute, MYSQLI_ASSOC);
        if ($result) {
            return $result;
        }
    }

    public function countAllMoviesWithKeyWord($database, $params)
    {
        $query = "SELECT COUNT(id_movie) FROM " . $this->table ." WHERE nama LIKE '%". $params['keyword'] . "%';";
        $execute = mysqli_query($database, $query);
        $result = mysqli_fetch_array($execute);
        if ($result) {
            return $result;
        } else {
            return '500';
        }
    }

    public function getAllAttributes($database,$params)
    {
        // $query = "SELECT * FROM " . $this->table ." WHERE id_movie = '". $params['id'] . "';";
        // // echo $query;
        // $execute = mysqli_query($database, $query);
        // $result = mysqli_fetch_all($execute, MYSQLI_ASSOC);
        // if ($result) {
        //     return $result;
        // } else {
        //     return '500';
        // }
        $api = "https://api.themoviedb.org/3/movie/". $params['id'] ."?api_key=1c55fae85a93267bd4a366fde9a90a4b";
        $result = file_get_contents($api);
        $result = json_decode($result, true);
        
        return $result;
    }

    // public function getGenre($database)
    // {
    //     $result = file_get_contents("https://api.themoviedb.org/3/genre/movie/list?api_key=1c55fae85a93267bd4a366fde9a90a4b");
    //     $result = json_decode($result, true);
    //     $genre = $result["genres"];

    //     return $genre;
    // }
}
