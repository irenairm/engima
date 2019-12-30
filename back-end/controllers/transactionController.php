<?php namespace Controllers;

include_once 'utils/response.php';
require_once 'models/transaction.php';
require_once 'models/review.php';
require_once 'models/user.php';
require_once 'models/seat.php';

use Models\Transaction;
use Models\User;
use Models\Review;
use Models\Seat;

class TransactionController
{
    private $username;
    private $params;
    private $id_pengguna;
    private $id_movie;
    private $id_seat;
    private $no_akun_virtual;
    private $review_status;

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

    private function fetchUsername(User $user, $connection, $access_token)
    {
        $this->username = $user->fetchUsername($connection, $access_token);
    }

    private function fetchID(User $user, $connection, $access_token){
        $this->id_user = $user->fetchID($connection, $access_token);
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

    public function fetch($connection)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $access_token = $this->getHeaderAuth();
            $user = new User($connection);
            $transaction = new Transaction($connection);
            $review = new Review($connection);
            // $id_pengguna = 1;
            if ($this->validateAccessToken($user, $connection, $access_token)) {
                $this->fetchUsername($user, $connection, $access_token);
                $this->fetchID($user,$connection,$access_token);
                $transaction->id_pengguna = $this->id_user;

                // echo $transaction->id_pengguna;
                $this->getTransactionFromUser($connection,$transaction); //, $id_movie, $id_sea
                $this->getReviewFromUser($connection,$review);
                } else {
                    returnResponse('500', 'Invalid Params.');
            }
        }
    }
    
    public function getTransactionFromUser($connection,$transaction)
    {
        $transaction_arr = $transaction->getTransactionByUser($connection,$transaction->id_pengguna);
        // var_dump($transaction_arr);

        if ($transaction_arr != '500')
        {
            // echo 'tesuto';
            $this->render($transaction, $transaction_arr);
        }   
    }
    
    public function getReviewFromUser($connection,$review){
        $review_arr = $review->getMovieReviews($connection,$params);
        if ($review_arr != '500')
        {
            // echo 'tesuto';
            $this->review_status = True;
        }   
    }
   public function render($transaction, $transaction_arr)    //$transaction_arr, $username, $connection
    {
        $html .= '
        <div class="transaction-container">
        
                <div class="transaction-header">
                    Transaction History
                </div>
                <br>';
                foreach ($transaction_arr as $movie) {     
                    
                    $transgetFilm = $transaction->getFilmTitle($movie['id_film']);
                    $transgetPoster = $transaction->getFilmPoster($movie['id_film']);  
                $html .= '<img src="https://image.tmdb.org/t/p/w185/' . $transgetPoster .'" class="movie-poster">
                            <div class="judul">'. $transgetFilm .'</div>
                            <div class="schedule">
                                <span class="schedule">
                                    Schedule
                                </span>
                                <span class="datetime">' 
                                    .date('F d, Y - h:i A', strtotime($movie['jadwal_film'])).
                                    // September 29, 2019 - 3.00 PM
                                '</span>
                            </div>
                        <div class="transaction">
                            <div class="trans-detail">
                                <span class="schedule">ID Transaksi</span>
                                <span id="id_transaction">'
                                .$movie['id_transaksi']. '</span>
                            </div>';
            $html  .=      ' <div class="trans-detail">
                                <span class="schedule">Status</span>
                                <span id="status_trans">'
                        .$movie['status_transaksi']. ' </span>
                            </div>
                         </div>
                    
                    ';
            if (((strtotime(date("Y-m-d h:i:s")) - strtotime(date($movie['jadwal_film']))) > 0)){
                // Kalau udah lewat baru bisa review, cek udah review apa belom
                if ($review_status){
                    $html .=                 
                    
                    '
                        <span class="button-transaction">
                            <button class="delete-button" id="delete">Delete Review</button>
                            <button class="edit-button" id="edit">Edit Review</button>
                        </span>
                    
                        ';
                }
                else {
                    $html .=                 
                    
                    '
                        <span class="button-transaction">
                            <button class="edit-button" id="add">Add Review</button>
                        </span>
                        
                    ';
                }
            
            }
        $html .= '<hr> ';
        }
        $html .= '</div>';

        returnResponse('200', $html);
}}

