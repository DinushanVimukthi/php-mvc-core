<?php

namespace stdin\core\db;

use stdin\core\Application;
use \PDO;


class Database
{
    public \PDO $pdo;
    public function __construct(array $config)
    {
        $dsn=$config['dsn'] ?? '';
        $user=$config['user'] ?? '';
        $password=$config['password'] ?? '';


        $this->pdo = new \PDO($dsn,$user,$password);
//        $this->pdo = new \PDO('mysql:host=localhost;dbname=mvc_db', 'root', '');

        $this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
    }

    public function applyMigrations()
    {
        $this->createMigrationTable();
        $appliedMigration=$this->getAppliedMigration();

        $newMigration=[];
        $files= scandir(Application::$ROOT_DIR . '/migrations');
        $toApplyMigration =array_diff($files,$appliedMigration);

        foreach ($toApplyMigration as $migration){
            if($migration=='.' || $migration=='..'){
                continue;
            }
                require_once Application::$ROOT_DIR.'/migrations/'.$migration;
                $className=pathinfo($migration,PATHINFO_FILENAME);
                $instance=new $className();
                $this->log("Applying migration: \n");
                $instance->up();
                $this->log("Applied migration: $migration \n");
                $newMigration[]=$migration;

            }
            if(!empty($newMigration)){
                $this->saveMigration($newMigration);
            }
            else{
                $this->log("All migrations are applied ");
            }
//        echo '<pre>';
//        var_dump($toApplyMigration);
//        echo '</pre>';
//        exit();
    }

    public function prepare($sql)
    {
        return $this->pdo->prepare($sql);
    }

    public function createMigrationTable()
    {
        $this->pdo->exec('CREATE TABLE IF NOT EXISTS migrations (
            id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
            migration VARCHAR(255) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )ENGINE = InnoDB');
    }

    public function getAppliedMigration()
    {
        $statement=$this->pdo->prepare("SELECT migration FROM migrations");
        $statement->execute();
        return $statement->fetchAll(\PDO::FETCH_COLUMN);
    }

    public function saveMigration(array $migration)
    {
        $str=implode(",",array_map(fn($n)=>"('$n')",$migration));
        $statement= $this->pdo->prepare("INSERT INTO migrations (migration) 
        VALUES ".$str);
        $statement->execute();

    }

    protected function log($message)
    {
        echo '['.date('Y-m-d H:i:s').'] '.$message."\n";
    }
}