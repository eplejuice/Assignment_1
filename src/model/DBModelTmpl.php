<?php
include_once("IModel.php");
include_once("Book.php");


/** The Model is the class holding data about a collection of books. 
 * @author Rune Hjelsvold
 * @see http://php-html.net/tutorials/model-view-controller-in-php/ The tutorial code used as basis.
 */
class DBModel implements IModel
{        
    /**
      * The PDO object for interfacing the database
      *
      */
    protected $db = null;  
    
    /**
	 * @throws PDOException
     */
    public function __construct($db = null)  
    {  
	   if ($db) {
			$this->db = $db;
		}
		else
		{
            try {
                $this->db = new PDO('mysql:host=localhost;dbname=test;charset=utf8mb4', 'root', '', 
                array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION)); 
            }   
            catch(PDOException $ex) {
                throw $ex;
            }
		}
    }
    
    /** Function returning the complete list of books in the collection. Books are
     * returned in order of id.
     * @return Book[] An array of book objects indexed and ordered by their id.
	 * @throws PDOException
     */
    public function getBookList()
    {
        $booklist = array();
        try {
            foreach($this->db->query('SELECT * FROM book') as $row) {
               array_push($booklist, new book($row['title'], $row['author'], $row['description'], $row['id']));
            }
        }
        catch(PDOException $ex) {
            throw $ex;
        }  
        return $booklist;
    }
    
    /** Function retrieving information about a given book in the collection.
     * @param integer $id the id of the book to be retrieved
     * @return Book|null The book matching the $id exists in the collection; null otherwise.
	 * @throws PDOException
     */
    public function getBookById($id)
    {
        if(!is_numeric($id)) {
            throw new Exception('Possible SQL injection.');
        }
        $book = null;
        try {
        $row = $this->db->query("SELECT * FROM book WHERE id = $id")->fetch(PDO::FETCH_ASSOC);
        }
        catch(PDOException $ex) {
            throw $ex;
        }  
        if($row != NULL) {  
            $book = new book($row['title'], $row['author'], $row['description'], $row['id']);
            return $book;
        }
        else {
            return NULL;
        }
    }
    
    /** Adds a new book to the collection.
     * @param $book Book The book to be added - the id of the book will be set after successful insertion.
	 * @throws PDOException
     */
    public function addBook($book)
    {
        try {
        $stmt = $this->db->prepare('INSERT INTO book (title, author, description) '
        . 'VALUES(:title, :author, :description)');
        
        }
        catch(PDOException $ex) {
            throw $ex;
        }
        if($book->title == '' || $book->author == '') {
            throw new Exception("Blank title or author when adding");
        }
        $stmt->bindValue(':title', $book->title);
        $stmt->bindValue(':author', $book->author);
        $stmt->bindValue(':description', $book->description);
        $stmt->execute();
        $book->id = $this->db->lastInsertID();            
    }

    /** Modifies data related to a book in the collection.
     * @param $book Book The book data to be kept.
     * @todo Implement function using PDO and a real database.
     */
    public function modifyBook($book)
    {
        if($book->title == '' || $book->author == '') {
           throw new Exception("Blank title or author when modifying");
        }
        
        $stmt = $this->db->prepare('UPDATE  book SET title = :title, author = :author, description = :description  WHERE id =' . $book->id);

            $stmt->bindValue(':title', $book->title);
            $stmt->bindValue(':author', $book->author);
            $stmt->bindValue(':description', $book->description);
            $stmt->execute();
            
       /* 
       $affectedNo = $this->db->exec( 'UPDATE book SET title = "' . $book->title , '", author ="' . $book->author , 
        '", description ="' . $book->description . '" WHERE id =' . $book->id); 
       */
    }

    /** Deletes data related to a book from the collection.
     * @param $id integer The id of the book that should be removed from the collection.
     */
    public function deleteBook($id)
    {  
        if(!is_numeric($id)) {
        throw new Exception('Cannot delete Non-numeric value.');
        }
        $affectedNo = $this->db->exec( 'DELETE FROM book WHERE id=' . $id );            
    }
}
?>