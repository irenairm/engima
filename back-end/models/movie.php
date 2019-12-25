<?php namespace Models;

class Movie
{
    private $connection;
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
        $result = file_get_contents("https://api.themoviedb.org/3/movie/now_playing?api_key=1c55fae85a93267bd4a366fde9a90a4b&language=en-US");
        $result = json_decode($result, true);
        $table = $result["results"];

        return $table;
    }

    public function getAllMoviesWithKeyword($database, $params)
    {
        $movies = $this->moviesWithKeyword($params);
        $offset = ($params['page'] - 1) * 5;
        $result = array_slice($movies,$offset,5);
        return $result;
        
    }
    
    public function moviesWithKeyword($params){
        $movies = $this->getAllMovies($database);
        $property = 'title';        
        $filterBy = $params['keyword']; 
        $new = array_filter($movies, function ($movies) use ($filterBy) {
            return (((strtotime(date("Y-m-d")) - strtotime(date($movies['release_date']))) <= 604800) && ((strtotime(date("Y-m-d")) - strtotime(date($movies['release_date']))) >= 0) && preg_match_all("/$filterBy/i", $movies['title']));
        });
        return $new;
    }

    public function countAllMoviesWithKeyWord($database, $params)
    {
        
        return count($this->moviesWithKeyword($params));
        
    }

    public function getAllAttributes($database,$params)
    {
        $api = "https://api.themoviedb.org/3/movie/". $params['id'] ."?api_key=1c55fae85a93267bd4a366fde9a90a4b";
        $result = file_get_contents($api);
        $result = json_decode($result, true);
        
        return $result;
    }
}
