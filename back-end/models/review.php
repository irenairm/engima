<?php namespace Models;

class Review
{
    private $connection;
    private $table = 'engima.review';

    public function __construct($database)
    {
        $this->connection = $database;
    }
    // Get All Movies
    public function getMovieReviews($database, $params)
    {
        $query = "SELECT *
                  FROM " .  $this->table . " NATURAL JOIN engima.user;"; 
                  // WHERE
                  // id_movie = '" . $params['id'] . "';";
        // echo $query;
        $execute = mysqli_query($database, $query);
        $result = mysqli_fetch_all($execute, MYSQLI_ASSOC);
        if ($result) {
            return $result;
        } else {
            return '500';
        }
    }
}
