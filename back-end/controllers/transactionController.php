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
            $user = new User($connection);
            $access_token = $this->getHeaderAuth();
            // $id_pengguna = 1;
            if ($this->validateAccessToken($user, $connection, $access_token)) {
                $this->fetchUsername($user, $connection, $access_token);
                $this->fetchID($user,$connection,$access_token);
                $id_user = 1;
                $this->getTransactionFromUser($connection,$id_user); //, $id_movie, $id_seat
            }
        }
    }
    
    public function getTransactionFromUser($connection,$id_user)
    {
        $transaction = new Transaction($connection);
        $transaction_arr = $transaction->getTransactionByUser($connection,$id_user);
        
        if ($transaction_arr != '500')
        {
            $this->render($transaction, $transaction_arr);
        }   
    }
    
   public function render($transaction, $transaction_arr)    //$transaction_arr, $username, $connection
    {
        $html .= '<div class="transaction-header">
                    Transaction History
                </div>
                <br>';
        foreach ($transaction_arr as $movie) {      
        $transgetFilm = $transaction->getFilmTitle($transaction_arr['values']['id_film']);
        $transgetPoster = $transaction->getFilmPoster($transaction_arr['values']['id_film']);  
        $html .= '<img src="https://image.tmdb.org/t/p/w185/' . $transgetPoster .'" class="movie-poster">
                    <div class="judul">'. $transgetFilm .'</div>
                    <div class="schedule">
                        <span class="schedule">
                            Schedule
                        </span>
                        <span class="datetime">
                            September 29, 2019 - 3.00 PM
                        </span>
                    </div>
                    <div class="transaction">
                        <div class="trans-detail">
                            <span class="schedule">ID Transaksi</span>
                            <span id="id_transaction">'
                              .$movie['values']['id_transaksi']. '</span>
                        </div>';
        $html  .=      ' <div class="trans-detail">
                            <span class="schedule">Status</span>
                            <span id="status_trans">'
                       .$movie['values']['status_transaksi']. ' </span>';
        $html .=                 
                       '</div>
                    </div>
                    <span class="button-transaction">
                        <button class="delete-button">Delete</button>
                        <button class="edit-button">Edit</button>
                    </span>
                </div>
                <hr>';
        }
        returnResponse('200', $html);
    }
}
