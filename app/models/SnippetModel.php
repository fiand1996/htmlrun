<?php 
/**
 * Snippet Model
 *
 * @date 07 June 2019
 * @version 1.0
 * @copyright Fiand T <fiand96@yahoo.com>
 * @author Fiand T (https://github.com/fiand1996)
 */
class SnippetModel extends DataEntry
{    
     /**
      * Extend parents constructor and select entry
      * @param mixed $uniqid Value of the unique identifier
      */
    public function __construct($uniqid=0)
    {
        parent::__construct();
        $this->select($uniqid);
    }



    /**
     * Select entry with uniqid
     * @param  int|string $uniqid Value of the any unique field
     * @return self       
     */
    public function select($uniqid)
    {
     if (is_int($uniqid) || ctype_digit($uniqid)) {
          $col = $uniqid > 0 ? "id" : null;
     } else {
          $col = "filename";
     }

     if ($col) {
          $query = DB::table(TABLE_SNIPPET)
                     ->where($col, "=", $uniqid)
                     ->limit(1)
                     ->select("*");
          if ($query->count() == 1) {
               $resp = $query->get();
               $r = $resp[0];

               foreach ($r as $field => $value)
                    $this->set($field, $value);

               $this->is_available = true;
          } else {
               $this->data = array();
               $this->is_available = false;
          }
     }

     return $this;
    }


    /**
     * Extend default values
     * @return self
     */
    public function extendDefaults()
    {
     $defaults = array(
          "user_id" => 0,
          "filepath" => "",
          "filename" => "",
          "title" => "",
          "description" => "",
          "resources" => "{}",
          "is_public" => 1,
          "created" => date("Y-m-d H:i:s"),
          "modified" => date("Y-m-d H:i:s")
     );


     foreach ($defaults as $field => $value) {
          if (is_null($this->get($field)))
               $this->set($field, $value);
     }
    }


    /**
     * Insert Data as new entry
     */
    public function insert()
    {
     if ($this->isAvailable())
          return false;

     $this->extendDefaults();

     $id = DB::table(TABLE_SNIPPET)
          ->insert(array(
               "id" => null,
               "user_id" => $this->get("user_id"),
               "filepath" => $this->get("filepath"),
               "filename" => $this->get("filename"),
               "title" => $this->get("title"),
               "description" => $this->get("description"),
               "resources" => $this->get("resources"),
               "is_public" => $this->get("is_public")
          ));

     $this->set("id", $id);
     $this->markAsAvailable();
     return $this->get("id");
    }


    /**
     * Update selected entry with Data
     */
    public function update()
    {
     if (!$this->isAvailable())
          return false;

     $this->extendDefaults();

     $id = DB::table(TABLE_SNIPPET)
          ->where("id", "=", $this->get("id"))
          ->update(array(
               "user_id" => $this->get("user_id"),
               "filepath" => $this->get("filepath"),
               "filename" => $this->get("filename"),
               "title" => $this->get("title"),
               "description" =>$this->get("description"),
               "resources" => $this->get("resources"),
               "is_public" => $this->get("is_public"),
               "modified" => $this->get("modified")
          ));

     return $this;
    }


    /**
      * Remove selected entry from database
      */
    public function delete()
    {
     if(!$this->isAvailable())
          return false;

     DB::table(TABLE_SNIPPET)->where("id", "=", $this->get("id"))->delete();
     $this->is_available = false;
     return true;
    }
}