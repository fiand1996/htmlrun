<?php 
/**
 * Snippets Model
 *
 * @date 07 June 2019
 * @version 1.0
 * @copyright Fiand T <fiand96@yahoo.com>
 * @author Fiand T (https://github.com/fiand1996)
 */
class SnippetsModel extends DataList
{    
     /**
      * Initialize
      */
     public function __construct()
     {
          $this->setQuery(DB::table(TABLE_SNIPPET));
     }
}
