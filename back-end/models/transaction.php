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
    public $schedule;
    
    // public function __construct($id_pengguna)   //, $id_movie, $id_seat
    // {
    //     $this->id_pengguna = $id_pengguna;
    // }

    public function __construct($database)
    {
        $this->connection = $database;
    }

    public function getDate($database){
        $query = "SELECT date FROM engima.schedule WHERE id_schedule = '".$this->id_schedule."';" ;
        $execute = mysqli_query($database, $query);
        $result = mysqli_fetch_array($execute);
        $this->date = $result["date"];
        echo $params['schedule'];
        return $this->date;
    }
    public function getTime($database){
        $query = "SELECT time FROM engima.schedule WHERE id_schedule = '".$this->id_schedule."';" ;
        $execute = mysqli_query($database, $query);
        $result = mysqli_fetch_array($execute);
        $this->time = $result["time"];
        return $this->time;
    }
    public function setSchedule($database){
        $this->date = $this->getDate($database);
        $this->time = $this->getTime($database);
        $this->schedule = $this->date." ".$this->time;
        return $this->schedule;
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
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));   
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
        // $wsdl   = 'http://localhost:8888/WebServiceBank?wsdl';
        // $client = new SoapClient($wsdl, array('trace'=>1));  // The trace param will show you errors stack
        // $accNumEngima = 4017954241577;
        // $request_param = array(
        //     "accountNumber" => $accNumEngima
        // );

        // try
        // {
        //     $responce_param = $client->createVirtualAccount($request_param);
        //     return $responce_param;
        // //$responce_param =  $client->call("webservice_methode_name", $request_param); // Alternative way to call soap method
        // } 
        // catch (Exception $e) 
        // { 
        //     echo $e->getMessage(); 
        // }
        return 67068566614195817;
    }

    // Submit username, id_seat, id_schedule
    public function submitTransaction($id_pengguna,$id_movie,$id_seat,$database)
    {
        // Submit transaksi via WS
        // http://3.90.144.191:9000/users/
        $url = "http://localhost:9000/users/".$id_pengguna; 
        $data = array(
            'id_pengguna'=>$id_pengguna,
            'no_akun_virtual'=> $this->getVirtualAccount(),// virtual akun,
            'id_film'=> $id_movie,
            'jadwal_film'=> $this->setSchedule($database),
            'kursi'=>$id_seat);
        $data_json = json_encode($data);
       
        $result = $this->http_req($url,$data_json,'POST');
        // Ubah string JSON menjadi array
        $respons = json_decode($result,TRUE);

        if ($respons['status'] == '200'){
            // Return status latest id transaction
            $this->id_transaction = $respons['values'];
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
        $api = file_get_contents("http://localhost:9000/users/".$id_pengguna); //". $id_pengguna."
        $results = json_decode($api, true); 
        // var_dump($results);
        // echo count($results['values']);
        if ($results['status'] == '200') {
           
            return $results['values'];
        } else {
            return '500';
        }
        
        // result is id_pengguna, nomor_akun_virtual, 
        // id_film, jadwal_film, kursi_pesanan, waktu_transaksi, status_transaksi,
        // id_transaksi
    }

    public function getFilmTitle ($id_movie){
        // echo $id_movie;
        // $id_movie = $this->id_movie;
        $movie_api = file_get_contents("https://api.themoviedb.org/3/movie/".$id_movie."?api_key=1c55fae85a93267bd4a366fde9a90a4b"); //' .$results[$i]['id_film']. '
        $movie = json_decode($movie_api,true);
        return $movie['title'];
        
    }
    public function getFilmPoster ($id_movie){
        // $id_movie = $this->id_movie;
        $movie_api = file_get_contents("https://api.themoviedb.org/3/movie/".$id_movie."?api_key=1c55fae85a93267bd4a366fde9a90a4b"); //' .$results[$i]['id_film']. '
        $movie = json_decode($movie_api,true);
        return $movie['poster_path'];
    }
    public function changeTransactionStatus($id_transaksi, $status){
        $url = 'http://localhost:9000/users/'.$this->id_transaksi.';';
        
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
