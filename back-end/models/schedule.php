<?php namespace Models;

class Schedule
{
    private $connection;
    private $table = 'engima.schedule';
    private $id_schedule;
 

    public function __construct($database)
    {
        $this->connection = $database;
    }

    public function getMovieSchedules($database, $params)
    {   
        $curr_date = "'". $this->generateCurrentDate() . "'" ;
    $query = "SELECT DISTINCT id_schedule,date,time,COUNT(status) AS seat FROM " .  $this->table ." NATURAL JOIN engima.seat WHERE date BETWEEN" .$curr_date. "+ INTERVAL 0 DAY AND " .$curr_date. " + INTERVAL 7 DAY GROUP BY id_schedule,date,time;";
    // 
        $execute = mysqli_query($database, $query);
        $result = mysqli_fetch_all($execute, MYSQLI_ASSOC);
        if ($result) {
            return $result;
        } else {
            return '500';
        }
    }
    
    private function generateCurrentDate()
    {
        return date('Y-m-d');
    }

    public function getIDSchedule(){
        return ($id_schedule = $this->id_schedule);
    }

    public function setIDSchedule($id_schedule){
        $this->id_schedule = $id_schedule;
    }
}
