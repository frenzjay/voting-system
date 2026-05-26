<?php
class CandidateNode {
    public $data;
    public $left = null;
    public $right = null;

    public function __construct($data) {
        $this->data = $data;
    }
}

class ResultBST {
    private $root = null;

    public function insert($data) {
        $newNode = new CandidateNode($data);
        if ($this->root === null) {
            $this->root = $newNode;
        } else {
            $this->insertNode($this->root, $newNode);
        }
    }

    private function insertNode(&$node, &$newNode) {
        if ($newNode->data['votes'] < $node->data['votes']) {
            if ($node->left === null) {
                $node->left = $newNode;
            } else {
                $this->insertNode($node->left, $newNode);
            }
        } else {
            if ($node->right === null) {
                $node->right = $newNode;
            } else {
                $this->insertNode($node->right, $newNode);
            }
        }
    }

    public function getRankedResults(&$resultArray, $node = 'INIT') {
        if ($node === 'INIT') $node = $this->root;
        
        if ($node !== null) {
            $this->getRankedResults($resultArray, $node->right);
            $resultArray[] = $node->data; 
            $this->getRankedResults($resultArray, $node->left);
        }
    }
}
?>