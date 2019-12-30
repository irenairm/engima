<?php namespace Controllers;

include_once 'utils/response.php';
require_once 'models/seat.php';
// require_once 'models/review.php';
require_once 'models/user.php';
require_once 'models/seat.php';
require_once 'models/transaction.php';
require_once 'models/schedule.php';
require_once 'models/movie.php';

use Models\Movie;
use Models\User;
use Models\Seat;
use Models\Transaction;
use Models\Schedule;

class BioskopController
{
    private $transaction = [];
    private $seat = [];
    private $movie = [];
    private $username;
    private $id_user;
    private $id_movie;
    private function getHeaderAuth()
    {
        foreach (getallheaders() as $name => $value) {
            if ($name === 'Authorization') {
                return $value;
            }
        }
    }

    
    private function validateAccessToken(User $user, $connection, $access_token)
    {
        $token_expdate_arr = $user->validateAccessToken($connection, $access_token);
        $token_expdate = $token_expdate_arr[0];
        if ($token_expdate_arr !== '500') {
            if ($this->validateExpDate($token_expdate)) {
                // returnResponse('200', 'Access Token Valid.');
                return true;
            }
        } else {
            returnResponse('500', 'Invalid Access Token.');
            return false;
        }
    }

    private function validateExpDate($token_expdate)
    {
        $validate = true;
        if ($token_expdate < $this->generateCurrentDate()) {
            $validate = false;
            returnResponse('500', 'Expired Access Token.');
        }
        return $validate;
    }

    private function generateCurrentDate()
    {
        return date('Y-m-d H:i:s');
    }

    private function fetchUsername(User $user, $connection, $access_token)
    {
        $this->username = $user->fetchUsername($connection, $access_token);
    }
    private function fetchID(User $user, $connection, $access_token){
        $this->id_user = $user->fetchID($connection, $access_token);
    }
    public function submit($connection, $params)
    {
        //$id_pengguna, $id_movie, $id_seat,
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $access_token = $this->getHeaderAuth();
            $user = new User($connection);
            // $id_pengguna = $this->fetchID($user,$connection,$access_token);
            $transaction = new Transaction($connection); 
            $seat = new Seat($connection);
            if ($this->validateAccessToken($user, $connection, $access_token)) {
                $this->fetchUsername($user, $connection, $access_token);
                $this->fetchID($user,$connection,$access_token);
                $data = json_decode(file_get_contents("php://input"));
                
                $this->split($seat, $transaction, $data, $connection);
                $resTrans = $this->insertInfoTransaction($this->id_user, $transaction, $connection);
                $resSeat = $this->insertInfoSeat($seat, $connection);
                if ($resTrans && $resSeat) {
                    returnResponse('200', 'Data have been inserted.');
                } else {
                    returnResponse('500', 'Internal Server Error HERE.');
                }
            }
        } else {
            returnResponse('500', 'Invalid HTTP REQUEST.');
        }
    }

    public function split(Seat $seat, Transaction $transaction, $data, $connection)
    {
        $transaction->id_seat = $data->id_seat;
        $transaction->id_schedule = $data->id_schedule;
        $transaction->id_movie = $data->id_movie;
        $seat->id_seat = $data->id_seat;
        $seat->id_schedule = $data->id_schedule;
        $seat->harga = 45000;
    }

    public function getTransactionID(User $user, $connection)
    {
        $user->getTransactionID($connection);
    }

    public function fetch($connection, $params)  //, $id_pengguna, $id_movie //ngambil semua data kursi, schedule
    {
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $access_token = $this->getHeaderAuth();
            $user = new User($connection);
            $seat = new Seat($connection);
            $schedule = new Schedule($connection);
            $movies = new Movie($connection);
            $transaction = new Transaction($connection);
            if ($this->validateAccessToken($user, $connection, $access_token)) {
                if ($params['schedule'] && $params['id']) { //if exist param, get all seat
                    $this->id_movie = $params['id'];
                    $this->fetchID($user,$connection,$access_token);
                    $transaction->id_pengguna = $this->id_user;
                    $this->getAllAttributes($connection, $params, $seat, $movies, $transaction); //, $transaction, $id_pengguna
                } else {
                    returnResponse('500', 'Invalid Params.');
                }
            }
        } else {
            returnResponse('500', 'Invalid HTTP REQUEST.');
        }
    }

    
    public function getAllAttributes($connection, $params, Seat $seat, Movie $movies, Transaction $transaction) //, Transaction $transaction, $id_pengguna
    {
        $seats_arr = $seat->getStatus($connection, $params);
        $movie = $movies->getAllAttributes($connection, $params);
        $transactions = $transaction->getTransactionByUser($connection,$transaction->id_pengguna); //,$id_pengguna
        if ($seats_arr != '500' && $movie != '500' &&  $transactions != '500') {
            // $this->transactions = $transaction_arr;
            $this->render($seats_arr, $movie, $transactions);
        } else {
            returnResponse('500', 'Internal Server Error here.');
        }
    }

    public function render($seats_arr, $movie, $transactions) //, $movie_arr
    {
        $html .= '<div class="header">
                            <div class="click-previously">
                                    <a href="#"><i class="fa fa-angle-left fa-3x"></i></a>
                            </div>            
                            
                            <div class="movie-header">
                                <div class="title">'. $movie['title'] .' </div>
                                <div class="showtime">'. date("F d, Y", strtotime($seats_arr[0]['date'])) .' - '.date("g:i A", strtotime($seats_arr[0]['time'])) .'';
        $html .=                '</div>
                            </div>
                    </div>
                <hr>

                <div class="flex-container-book">
                <div class="seat-selection">
                <div class="seat-row">';

        foreach ($seats_arr as $seat) 
        {   
            if(($seat['id_seat']) % 10 == 0)
            {
                
                if ($seat['status'] == 0) {
                    $html .=  '<button class="seat" value ="'.$seat['id_seat'].'" id="'.$seat['id_seat'].'" type="submit">'.$seat['id_seat'].'</button></div>';
                } else {
                    $html .=  '<button class="seat" value ='.$seat['id_seat'].' id='.$seat['id_seat'].' type="submit" disabled>'.$seat['id_seat'].'</button></div>';
                } 
                $html .= '<div class="seat-row">';  
            }
            else{
            if ($seat['status'] == 0) {
                $html .=  '<button class="seat" value ="'.$seat['id_seat'].'" id="'.$seat['id_seat'].'" type="submit">'.$seat['id_seat'].'</button>';
            } else {
                    $html .=  '<button class="seat" value ='.$seat['id_seat'].' id='.$seat['id_seat'].' type="submit" disabled>'.$seat['id_seat'].'</button>';
                }
            }
        }

        $count = count($transactions);

        $html .= '<div class="seat-row">
                <button class="screen-img" type="button" disabled>Screen</button>
                </div>
            </div>
            </div>

            <div id="booking-message">
                
                <h3 class="text">Booking Summary</h3>
                
                <p id="book-msg">You haven\'t selected any seat yet. Please click on one of the seat provided.</p>
                
                <div id ="booked">
                    <div class="movie-detail">
                        <p id="movie-selected-title">'.$movie['title'].'</p>
                        <p id="movie-selected-time">'. date("F d, Y", strtotime($seats_arr[0]['date'])) .' - '. date("g:i A", strtotime($seats_arr[0]['time'])) .'</p>
                    </div>
                    <span class="price-detail">
                        <p id="movie-selected-seat">No seat selected</p>
                        <p id="movie-selected-price">Rp 45.000</p>
                    </span>
                    <button id="button-buy" type="submit">Buy Ticket</button>
                </div>

                <div id="modal">
                    <div class="modal-content">
                        <div class="modal-text">
                            <h2 class="message-pay">Please do the payment transaction</h2>
                            <span><p class = "message-directing" id="id_transaksi">ID Transaksi : '. $transactions[$count-1]['id_transaksi'] .'</p></span>
                            <span><p class = "message-directing" id="no_va">No Akun Virtual : '. $transactions[$count-1]['nomor_akun_virtual'] .'</p></span>
                            <button class="mod" id="modal-button" type="submit">Go to transaction history</button>
                        </div>
                    </div>
                </div>

            </div>
            ';
        // returnBioskopResponse('200', $html, ($movie_arr[0]));
     returnResponse('200', $html);   
    }

     
    public function insertInfoSeat(Seat $seat, $connection)
    {
        $result = $seat->submitSeat($connection);
        if ($result == '200') {
            return True;
        } else {
            return False;
            returnResponse('500','FAIL Seat');
        }
    }

    public function insertInfoTransaction($id_user, Transaction $transaction,$connection)
    {
        $transaction->id_user = $id_user;
        $id_seat = $transaction->id_seat;
        $id_user = $transaction->id_user;
        $id_movie = $transaction->id_movie;
        $result = $transaction->submitTransaction($id_user,$id_movie,$id_seat,$connection);
        if ($result =='200') {
            return True;
            returnResponse('200','Success');
        } else {
            return False;
            returnResponse('500','FAIL TRANS');
        }
    }
}
 