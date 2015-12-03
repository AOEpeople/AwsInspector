<?php

namespace AwsInspector\Model\Ec2;

class Collection extends \SplObjectStorage
{

    public function getFirst() {
        $this->rewind();
        return $this->current();
    }

}