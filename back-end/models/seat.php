<?php namespace Models;
// error_reporting(0);
class Seat
{
    private $connection;
    private $table = 'engima.seat';
    // Attributes for MYSQL table:
    public $id_seat;
    public $status;
    public $id_schedule;
    public $harga;

    public function __construct($database)
    {
        $this->connection = $database;
    }
    // Submit id_seat, id_schedule, harga
    public function submitSeat($database)
    {
        $query = "UPDATE " . $this->table
                 . " SET STATUS = '1' WHERE id_seat = '" . $this->id_seat. "' AND id_schedule = '" . $this->id_schedule . "';";
        // echo $query;
        $execute = mysqli_query($database, $query);
        if ($execute) {
            return '200';
        } else {
            return '500';
        }
    }

    public function getStatus ($database, $params)
    {
        $query = "SELECT id_seat, harga, date, time, status FROM " . $this->table . " NATURAL JOIN " . 'engima.schedule' . " WHERE id_schedule ='". $params['schedule'] . "';";    
        $execute = mysqli_query($database, $query);
        $result = mysqli_fetch_all($execute, MYSQLI_ASSOC);
        // if ($result) {
        //     return $result;
        // } else {
        //     return '500';
        // }
        return $result;
    }
}
