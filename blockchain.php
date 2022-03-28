<?php

class Header
{
    
    public function __construct($version, $previousHash = '', $merkle_root, $timestamp = '', $difficulty)
    {

        $this->version = $version;
        $this->previousHash = $previousHash;
        $this->merkle_root = $merkle_root;
        $this->timestamp = $timestamp;
        $this->difficulty = $difficulty;
        $this->nonce = 0;
    }
}
class block extends Header
{
    public $index;
    public function __construct($index, $header, $transactions_count, $data)
    {
        $this->index = $index;
        $this->header = $header;
        $this->hash = $this->calculateHash();
        $this->transactions_count = $transactions_count;
        $this->data = $data;
    }

    public function calculateHash()
    {
        
        return hash("sha256", serialize($this->header));
    }
}


class blockChain extends block
{

    public $chain = array();
    public $unconfirmed_transactions = array();

    public function __construct()
    {
        $this->createGenesisBlock();
    }

    public function createGenesisBlock()
    {
        $this->add_new_transaction("mark->Ola->0");
        $this->mine();
    }

    private function getLatestBlock()
    {
        return $this->chain[(count($this->chain) - 1)];
    }




    public function add_block($block, $proof)
    {

        if (count($this->chain) == 0) {
           // echo "done8";
            $previousHash = "0";
        } else {
            $previousHash = $this->getLatestBlock()->hash;
        }

        if ($previousHash != $block->header->previousHash) {
           // echo "done9";
            return False;
        }

        if (!$this->is_valid_proof($block, $proof)) {
            //echo "done10";
            return False;
        }



        $block->hash = $proof;
        $this->chain[] = $block;
        file_put_contents('testFile.txt', serialize($this->chain));

//         $fp = fopen('file.txt', 'a+');
// fwrite($fp, print_r($this->chain, true));

        return True;
    }


    public function  is_valid_proof($block, $block_hash)
    {
        //echo "done11";


        //echo str_starts_with($block_hash, str_repeat('0', 1));
        if (($block_hash == $block->calculateHash()) == "1") {
            if (str_starts_with($block_hash, str_repeat('0', 1))) {
                return true;
            }
        }

        return false;
    }

    public function proof_of_work($block)
    {
       // echo "done5";
       
       // echo $block->header->nonce;
        $block->header->nonce =0;
        while (!str_starts_with($block->calculateHash(), str_repeat('0', 1))) {
            $block->header->nonce ++;
            
            $block->hash = $block->calculateHash();
        }
      //  echo "done6";
      
        return $block->hash;
    }



    public function add_new_transaction($transaction)
    {
        $this->unconfirmed_transactions [] = $transaction;
    }

 
    public function mine()
    {
        //echo "count";
       // echo count($this->chain);
             if (!$this->unconfirmed_transactions) {
          //  echo "done1";
            return False;
        }
        if (count($this->chain) == 0) {
           // echo "done2";
            $new_header = new Header(
                1,
                "0",
                "0",
                microtime(true),
                1
            );

            $new_block = new block(
                0,
                $new_header,
                3,
                $this->unconfirmed_transactions
            );
        } else {
           // echo "done3";
           // $last_block = $this->getLatestBlock();
            $new_header = new Header(
                1,
                $this->getLatestBlock()->hash,
                "0",
                microtime(true),
                1
            );
            $new_block = new block(
                $this->getLatestBlock()->index + 1,
                $new_header,
                count($this->unconfirmed_transactions),
                $this->unconfirmed_transactions
            );
        }
      //  echo "done4";

        $proof = $this->proof_of_work($new_block);
        $this->add_block($new_block, $proof);

        $this->unconfirmed_transactions = [];
        return $new_block->index;
    }
}






$blockchain = new blockChain();


$blockchain->add_new_transaction('Omar->Ola->10');
$blockchain->mine();

$blockchain->add_new_transaction('Ola->Aya->26');
$blockchain->add_new_transaction('Aya->Omar->3');
$blockchain->mine();
$blockchain->add_new_transaction('mark->Salman->50');
$blockchain->add_new_transaction('Ola->Omar->10');

$blockchain->mine();
$blockchain->add_new_transaction('mark->Salman->50');
$blockchain->add_new_transaction('Ola->Omar->500');

$blockchain->mine();
$blockchain->add_new_transaction('mark->Salman->50');
$blockchain->add_new_transaction('Ola->Omar->500');
$blockchain->add_new_transaction('mark->Salman->50');
$blockchain->add_new_transaction('Ola->Omar->500');
$blockchain->mine();

// $array = file("file.txt", FILE_SKIP_EMPTY_LINES);
// echo count($array);
//print_r($array);


$array = unserialize(file_get_contents('testFile.txt'));
//echo count($array);


$jsonData = json_encode($array);

echo $jsonData . "\n";
