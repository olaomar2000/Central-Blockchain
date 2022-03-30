<?php

class MerkleTree
{

    private $element_list;
    private $root;


    public function __construct()
    {
        $this->element_list = array();
        $this->root         = "";
    }

    public function addElement($element)
    {
        $this->element_list[] = $this->hash($element);
    }

    public function create()
    {
        $new_list = $this->element_list;

        // This is simply "going up one level".
        while (count($new_list) != 1) {
            $new_list = $this->getNewList($new_list);
        }

        $this->root = $new_list[0];

        // We return the root immediately, but there is also a getRoot() method.
        return $this->root;
    }


    private function getNewList($temp_list)
    {
        $i        = 0;
        $new_list = array();

        while ($i < count($temp_list)) {
            // Left child
            $left = $temp_list[$i];
            $i++;

            // Right child
            if ($i != count($temp_list)) {
                $right = $temp_list[$i];
            } else {
                $right = $left;
            }

            // Hash and add as parent.
            $hash_value = $this->hash($left . $right);

            $new_list[] = $hash_value;
            $i++;
        }

        return $new_list;
    }


   
    private function hash($string)
    {
        
        return hash('sha256', $string, false);
    }

    public function getRoot()
    {
        return $this->root;
    }


}






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
public $element_list = array();
    public $chain = array();
    public $unconfirmed_transactions = array();
    public $root;

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
           
            $previousHash = "0";
        } else {
            $previousHash = $this->getLatestBlock()->hash;
        }

        if ($previousHash != $block->header->previousHash) {
           
            return False;
        }

        if (!$this->is_valid_proof($block, $proof)) {
            
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
       echo str_starts_with($block_hash, str_repeat('0', 1));
        if (($block_hash == $block->calculateHash()) == "1") {
            if (str_starts_with($block_hash, str_repeat('0', 1))) {
                return true;
            }
        }

        return false;
    }

    public function proof_of_work($block)
    {
       
       
        echo $block->header->nonce;
        $block->header->nonce =0;
        while (!str_starts_with($block->calculateHash(), str_repeat('0', 1))) {
            $block->header->nonce ++;
            
            $block->hash = $block->calculateHash();
        }
       
        return $block->hash;
    }

    public function create()
    {
        $new_list = $this->element_list;
 while (count($new_list) != 1) {
            $new_list = $this->getNewList($new_list);
        }
   $this->root = $new_list[0];
   return $this->root;
    }


    private function getNewList($temp_list)
    {
        $i        = 0;
        $new_list = array();

        while ($i < count($temp_list)) {
            // Left child
            $left = $temp_list[$i];
            $i++;

            // Right child
            if ($i != count($temp_list)) {
                $right = $temp_list[$i];
            } else {
                $right = $left;
            }

            // Hash and add as parent.
            $hash_value = $this->hash($left . $right);

            $new_list[] = $hash_value;
            $i++;
        }

        return $new_list;
    }


   
    private function hash($string)
    {
        
        return hash('sha256', $string, false);
    }

    
    public function add_new_transaction($transaction)
    {
        $this->unconfirmed_transactions [] = $transaction;
        $this->element_list[] = $this->hash($transaction);

    }


 
    public function mine()
    {
        echo "count";
        echo count($this->chain);
             if (!$this->unconfirmed_transactions) {
          
            return False;
        }
        if (count($this->chain) == 0) {
           
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
           
           // $last_block = $this->getLatestBlock();
            $new_header = new Header(
                1,
                $this->getLatestBlock()->hash,
                $this->create(),
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


$blockchain->add_new_transaction('rawan->mah->3');
$blockchain->mine();


// $array = file("file.txt", FILE_SKIP_EMPTY_LINES);
// echo count($array);
//print_r($array);


$array = unserialize(file_get_contents('testFile.txt'));
//echo count($array);


$jsonData = json_encode($array);

echo $jsonData . "\n";
