<?php

class HTMLCleaner {
    protected $dom = null;
    protected $removed_nodes = array();
    protected $is_snippet = true;
    
    function __construct() {
    }
    
    function load($html, $charset = 'utf-8', $is_snippet = true) {
        $this->is_snippet = $is_snippet;
        
        if ($is_snippet) {
            $html = '<html xmlns="http://www.w3.org/1999/xhtml"><head><meta http-equiv="Content-Type" content="text/html; charset='.$charset.'" /></head><body>'.$html.'</body></html>';
        }
        $this->dom = new DOMDocument('1.1', $charset);
        ErrorWrapperException::unbind();
        @$this->dom->loadHTML($html);
        ErrorWrapperException::bind();
        
        $this->xpath = new DOMXpath($this->dom);
    }
    
    function removeTags($tags) {
        $q = '//'.implode('|//', (array)$tags);
        $nodes = $this->xpath->query($q);
        foreach ($nodes as $node) {
            $node->parentNode->removeChild($node);
            $this->removed_nodes[]= $node;
        }
    }
    
    function getResult() {
        $ret = $this->dom->saveHTML();
        if ($this->is_snippet) {
            $ret = preg_replace('~^.+<body>(.+)</body>.+$~s', '$1', $ret);
        }
        return $ret;
    }
    
    function removeAttributes($attrs) {
        $q = '//@'.implode('|//@', (array)$attrs);
        $on = $this->xpath->query($q);
        foreach ($on as $n) {
            $n->parentNode->removeAttribute($n->name);
            $this->removed_nodes[]= $n;
        }
    }
    
    function removeEventAttributes() {
        $on = $this->xpath->query('//@*[starts-with(local-name(), "on")]');
        foreach ($on as $n) {
            $n->parentNode->removeAttribute($n->name);
            $this->removed_nodes[]= $n;
        }
    }
    
    function safe() {
        $this->removeTags(array('script','style','object','embed','applet','font'));
        $this->removeEventAttributes();
        $this->removeAttributes('style');
    }
    
    protected function outerHTML($node) {
        $ret = '';
        ErrorWrapperException::unbind();
        $ret = @$this->dom->saveXML($node);
        ErrorWrapperException::bind();
        return $ret;
    }
    
    protected function innerHTML($node) {
        $ret = '';
        foreach ($node->childNodes as $child) {
            $ret.= $this->dom->saveXML($child);
        }
        return $ret;
    }
    
    function dumpRemoved() {
        $ret = array();
        foreach ($this->removed_nodes as $node) {
            try {
                $ret[]= $this->outerHTML($node);
            } catch (Exception $e) {
                //
            }
        }
        return $ret;
    }
    
    function removed() {
        return $this->removed_nodes;
    }
}

