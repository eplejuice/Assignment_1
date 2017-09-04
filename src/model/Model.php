<?php

include_once("Book.php");
include_once("IModel.php");

/** The Model is the class holding data about a collection of books. 
 * @author Rune Hjelsvold
 * @see http://php-html.net/tutorials/model-view-controller-in-php/ The tutorial code used as basis.
 */
class Model implements IModel
{								  
    /**
	 * @todo The session array for storing book collection is to be replaced by a database.
	 */
	public function __construct()  
    {  
	    // Create an initial collection of books
	    if (!isset($_SESSION['BookList']))
		{
			$_SESSION['BookList'] = array(new Book("Jungle Book", "R. Kipling", "A classic book.", 1),
										  new Book("Moonwalker", "J. Walker", "", 2),
										  new Book("PHP for Dummies", "J. Valade", "Some smart gal.", 3)
									);
		} 
	}
	
	/** Function returning the complete list of books in the collection. Books are
	 * returned in order of id.
	 * @return Book[] An array of book objects indexed and ordered by their id.
	 * @todo Replace implementation using a real database.
	 */
	public function getBookList()
	{
		// here goes some session values to simulate the database
		return $_SESSION['BookList'];
	}
	
	/** Function retrieveing information about a given book in the collection.
	 * @param integer $id The id of the book to be retrieved.
	 * @return Book|null The book matching the $id exists in the collection; null otherwise.
	 * @todo Replace implementation using a real database.
	 */
	public function getBookById($id)
	{
		// we use the session book list array to get all the books and then we return the requested one.
		// in a real life scenario this will be done through a db select command
		$idx = $this->getBookIndexById($id);
		if ($idx > -1)
		{
			return $_SESSION['BookList'][$idx];
		}
		return null;
	}
	
	/** Adds a new book to the collection.
	 * @param $book Book The book to be added - the id of the book will be set after successful insertion.
	 * @todo Replace implementation using a real database.
	 */
	public function addBook($book)
	{
	    $book->id = $this->nextId();
		$_SESSION['BookList'][] = $book;
	}

	/** Modifies data related to a book in the collection.
	 * @param Book $book The book data to kept.
	 * @todo Replace implementation using a real database.
	 */
	public function modifyBook($book)
	{
		$idx = $this->getBookIndexById($book->id);
		if ($idx > -1)
		{
			$_SESSION['BookList'][$idx]->title = $book->title;
			$_SESSION['BookList'][$idx]->author = $book->author;
			$_SESSION['BookList'][$idx]->description = $book->description;
		}
	}

	/** Deletes data related to a book from the collection.
	 * @param integer $id The id of the book that should be removed from the collection.
	 * @todo Replace implementation using a real database.
	 */
	public function deleteBook($id)
	{
		$idx = $this->getBookIndexById($id);
		if ($idx > -1)
		{
			array_splice($_SESSION['BookList'],$idx, 1);
		}
	}
	
	/** Helper function finding the location of the book in the collection array.
	 * @param integer $id The id of the book to look for.
	 * @return integer The index of the book in the collection array; -1 if the book is
	 *                 not found in the array.
	 */
	protected function getBookIndexById($id)
	{
		for ($i = 0; $i < sizeof($_SESSION['BookList']); $i++)
        {
			if ((string)$_SESSION['BookList'][$i]->id === $id)
			{
				return $i;
			}
		}
		return -1;
	}
	
	/** Helper function generating a sequence of ids.
	 * @return integer A value larger than the largest book id in the collection.
	 * @todo Replace with a call to a database auto_increment function.
	 */
	protected function nextId()
	{
		$maxId = 0;
		foreach ($_SESSION['BookList'] as $book)
		{
			if (isset($book) && $book->id > $maxId)
			{
				$maxId = $book->id;
			}
		}
		return $maxId + 1;
	}

}

?>