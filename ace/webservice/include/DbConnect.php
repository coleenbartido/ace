<?php

//Class DbConnect
class DbConnect
{
    //Variable to store database link
    private $con;

    //This method will connect to the database
    function connect()
    {
        //connecting to mysql database
        $this->con = new mysqli($_ENV['DB']->DB_HOST, $_ENV['DB']->DB_USERNAME, $_ENV['DB']->DB_PASSWORD, $_ENV['DB']->DB_NAME);

        //Checking if any error occured while connecting
        if (mysqli_connect_error()) {
            echo "Failed to connect to MySQL: " . mysqli_connect_error();
        }

        //finally returning the connection link 
        return $this->con;
    }

}