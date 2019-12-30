<?php namespace Models;

class Review
{
    private $connection;
    private $table = 'engima.review';
    private $rating;
    private $description;
    private $username;
    private $id_movie;
    
    public function __construct($database)
    {
        $this->connection = $database;
    }

    public function submitReview($description, $rating, $username, $id_movie, $connection){
        $query = "INSERT INTO " .$this->table. "(description,rating,id_movie,username) VALUES
        (" .$this->description. ", " .$this->rating. ", " .$this->id_movie. ", " .$this->username. ");";
        $execute = mysqli_query($database, $query);
        if ($execute) {
            return '200';
        } else {
            return '500';
        }
    }
    // Get All Movies
    public function getMovieReviews($database, $params)
    {
        $query = "SELECT username,description,rating
                  FROM " .  $this->table . " NATURAL JOIN engima.user
                  WHERE
                  id_movie = '" . $params['id'] . "';";
        // echo $query;
        $execute = mysqli_query($database, $query);
        $result = mysqli_fetch_all($execute, MYSQLI_ASSOC);
        if ($result) {
            return $result;
        } else {
            return '500';
        }
    }

    // Get average rating
    public function getAverageRatings($database, $params)
    {
        $query = "SELECT ROUND(avg(rating),1) AS rating FROM " .  $this->table . " ;"; 
                //   WHERE
                //   id_movie = '" . $params['id'] . "';";
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
