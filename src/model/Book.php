<?php

/** The Model is the class holding data related to one book. 
 * @author Rune Hjelsvold
 * @see http://php-html.net/tutorials/model-view-controller-in-php/ The tutorial code used as basis.
 */
class Book {
	public $id;
	public $title;
	public $author;
	public $description;

/** Constructor
 * @param string $title Book title
 * @param string $author Book author 
 * @param string $description Book description 
 * @param integer $id Book id (optional) 
 */
	public function __construct($title, $author, $description, $id = -1)  
    {  
        $this->id = $id;
        $this->title = $title;
	    $this->author = $author;
	    $this->description = $description;
    } 
}

?>