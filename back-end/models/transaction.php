<?php namespace Models;

class Transaction
{
    private $connection;
    private $table = 'engima.schedule';
    // Attributes 
    public $id_pengguna; //
    public $no_akun_virtual; //
    public $id_movie; //
    public $jadwal_film;
    public $id_schedule;
    public $id_seat; //
    public $status; //
    public $id_transaction;
    public $date;
    public $time;
    public $title;
    
    // public function __construct($id_pengguna)   //, $id_movie, $id_seat
    // {
    //     $this->id_pengguna = $id_pengguna;
    // }

    public function __construct($database)
    {
        $this->connection = $database;
    }

    public function getDate($id_movie){
        $query = "SELECT DISTINCT date FROM engima.schedule WHERE id_movie = '". $id_movie . "';" ;
        $execute = mysqli_query($database, $query);
        $result = mysqli_fetch_all($execute, MYSQLI_ASSOC);
        $this->date = $result;
        return $this->date;
    }
    public function getTime($id_movie){
        $query = "SELECT time FROM engima.schedule WHERE id_movie = '". $id_movie . "';" ;
        $execute = mysqli_query($database, $query);
        $result = mysqli_fetch_all($execute, MYSQLI_ASSOC);
        $this->time = $result;
        return $this->time;
    }
    public function setSchedule($id_movie){
        $query = "STR_TO_DATE(CONCAT(" .$this->getDate($id_movie). ", \' \'," .$this->getTime($id_movie). "), '%Y-%m-%d %H:%i:%s')" ;
        $execute = mysqli_query($database, $query);
        $result = mysqli_fetch_all($execute, MYSQLI_ASSOC);
        $this->jadwal_film = $result;
    }
    
    public function http_req($url,$data_json,$method){
        // curl initiate
        $ch = curl_init();
        // set url
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));  
        
        if ($method==='POST'){
            // SET Method as a POST
            curl_setopt($ch, CURLOPT_POST, 1);   
            // curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));   
        }
        elseif ($method === 'PUT'){
            // SET Method as a PUT
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
        }
        else{
            echo ("ERROR!");
        }
        // Pass user data in POST command
        curl_setopt($ch, CURLOPT_POSTFIELDS,$data_json);   
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        
        // Execute curl and assign returned data
        $response  = curl_exec($ch);
        // $result = json_encode($url,true);

        // Close curl
        curl_close($ch);
        // See response if data is posted successfully or any error
        echo ($response);
        return $response;
    }

    public function getVirtualAccount(){
        $wsdl   = 'http://localhost:8888/WebServiceBank?wsdl';
        $client = new SoapClient($wsdl, array('trace'=>1));  // The trace param will show you errors stack
        $accNumEngima = 4017954241577;
        $request_param = array(
            "accountNumber" => $accNumEngima
        );

        try
        {
            $responce_param = $client->createVirtualAccount($request_param);
            return $responce_param;
        //$responce_param =  $client->call("webservice_methode_name", $request_param); // Alternative way to call soap method
        } 
        catch (Exception $e) 
        { 
            echo $e->getMessage(); 
        }
    }
    // Submit username, id_seat, id_schedule
    public function submitTransaction($id_pengguna,$id_movie,$id_seat)
    {
        // Submit transaksi via WS
        $url = "http://3.90.144.191:9000/users/".$id_pengguna.""; 
        $data = array(
            'id_pengguna'=>$id_pengguna,
            'no_akun_virtual'=> $this->getVirtualAccount(),// virtual akun,
            'id_film'=> $id_movie,
            'jadwal_film'=>$this->setSchedule($id_movie),
            'kursi'=>$id_seat);
        $data_json = json_encode($data);
        $result = http_req($url,$data_json,'POST');
        // Ubah string JSON menjadi array
        $result = json_decode($result,TRUE);

        if ($result['status'] === '200'){
            // Return status latest id transaction
            $this->id_transaction = $result['values'];
            return '200';
        }
        else{
            return 'ERROR!';
        }
    }

    public function getNewTransactionID()
    {
        // $query = "SELECT COUNT(id_transaction) FROM " . $this->table . ";";
        // $execute = mysqli_query($database, $query);
        // $result = mysqli_fetch_array($execute);
        // $this->id_transaction = $result[0] + 1;
        return $this->id_transaction;
    }

    public function getTransactionByUser($database,$id_pengguna) //, $id_pengguna
    {
        $api = file_get_contents("http://3.90.144.191:9000/users/$id_pengguna"); //". $id_pengguna."
        $results = json_decode($api, true); 
       
        if ($results) {
            return $results;
        } else {
            return '500';
        }
    }
    public function getFilmTitle ($id_film){
        // $id_film = 640882;
        $movie_api = file_get_contents("https://api.themoviedb.org/3/movie/$id_film?api_key=1c55fae85a93267bd4a366fde9a90a4b"); //' .$results[$i]['id_film']. '
        $movie = json_decode($movie_api,true);
        return $movie['title'];
        
    }
    public function getFilmPoster ($id_film){
        // $id_film = 640882;
        $movie_api = file_get_contents("https://api.themoviedb.org/3/movie/$id_film?api_key=1c55fae85a93267bd4a366fde9a90a4b"); //' .$results[$i]['id_film']. '
        $movie = json_decode($movie_api,true);
        return $movie['poster_path'];
    }
    public function changeTransactionStatus($id_transaksi, $status){
        $url = 'http://3.90.144.191:9000/users/'.$this->id_transaksi.';';
        
        $data = array(
            'statusTransaksi'=> $status);
        $data_json = json_encode($data);
        $result = http_req($url,$data_json,'PUT');
        $this->status = $status;
        return $this->status;
    }

    public function isTwoMinutes(){
        // // microtime(true) returns the unix timestamp plus milliseconds as a float
        // $starttime = microtime(true);
        // /* do stuff here */
        // $endtime = microtime(true);
        // $timediff = $endtime - $starttime;

        if ($timedif === 120){
            return True;
        }
        else{
            return False;
        }
    }

}
