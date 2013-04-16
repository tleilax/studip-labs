<?php
namespace API;
use \ArrayAccess, \IteratorAggregate;

class Collection implements ArrayAccess, IteratorAggregate
{
    public static function fromArray(Array $array)
    {
        $collection = new Collection();
        return $collection->setData($array);
    }

    protected $elements   = array();
    protected $pagination = array();

    public function __construct()
    {
        
    }

    public function setData(Array $array)
    {
        $this->elements = $array;
        return $this;
    }
    
    public function offsetExists($offset)
    {
        return isset($this->elements[$offset]);
    }
    
    public function offsetGet($offset)
    {
        return $this->elements[$offset];
    }
    
    public function offsetSet($offset, $value)
    {
        if ($offset === null) {
            $this->elements[] = $value;
        } else {
            $this->elements[$offset] = $value;
        }
    }
    
    public function offsetUnset($offset)
    {
        unset($this->elements[$offset]);
    }
    
    public function getIterator()
    {
        return new \ArrayIterator($this->elements);
    }
    
    // TODO: Adjust limit to Stud.IP Config->ENTRIES_PER_PAGE (or whatever it's called)
    public function paginate($uri_template, $total, $offset, $limit = 20)
    {
        if ($total > 0) {
            $offset = $offset - $offset % $limit;
            $max    = ($total % $limit)
                    ? $total - $total % $limit
                    : $total - $limit;

            $pagination = compact('total', 'offset', 'limit');
            if ($total > $limit) {
                $pagination['links'] = array();
                $pagination['links']['first']    = sprintf($uri_template, 0, $limit);
                $pagination['links']['previous'] = sprintf($uri_template, max(0, $offset - $limit), $limit);
                $pagination['links']['next']     = sprintf($uri_template, min($max, $offset + $limit), $limit);
                $pagination['links']['last']     = sprintf($uri_template, $max, $limit);
            }
            $this->pagination = compact('pagination');
        }

        return $this;
    }
    
    public function toArray()
    {
        return array_merge(
            array('collection' => $this->elements),
            $this->pagination
        );
    }
}