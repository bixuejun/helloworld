<?php

/** 
 * @author ivali-zlj
 * 
 */
class testcodestyle
{
    // TODO - Insert your code here
    
    /**
     */
    public function __construct()
    {
        
        // TODO - Insert your code here
    }

    /**
     */
    function __destruct()
    {
        
        // TODO - Insert your code here
    }
    
    /**
     * hello
     * 鎵撴嫑鍛兼柟娉�
     * @param string $name
     */
    public function hello($name)
    {
        echo 'hello world!' . $name;
        $foobar = 'slddlskdjfweijlsdjfsldkjfldkglkdjfdlskdjlkjlkjldksldkjflsd'; // here comes the comment,using this
        if (empty($name)) {
            echo 'false';
        } else {
            echo 'true';
        }
        
        switch ($name) {
            case 'link':
                echo $name;
                break;
            case 'ok':
                echo 'ok';
                break;
            case 'error':
                echo 'err';
            // no break
            
            default:
                echo 'nothing';
                break;
        }
        
        $array = array();
        foreach ($array as $key => $val) {
            echo $array[$key];
            if ($val == '') {
                continue;
            }
        }
        while ($name !== '') {
            print $name;
        }
        
        self::__destruct();
            
     
    }
    
}

