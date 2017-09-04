<?php
Interface IModel {
    /** Function returning the complete list of books in the collection. Books are
     * returned in order of id.
     * @return Book[] An array of book objects indexed and ordered by their id.
	 * @throws PDOException
     */
    public function getBookList();
    
    /** Function retrieving information about a given book in the collection.
     * @param integer $id the id of the book to be retrieved
     * @return Book|null The book matching the $id exists in the collection; null otherwise.
	 * @throws PDOException
     */
    public function getBookById($id);
    
    /** Adds a new book to the collection.
     * @param $book Book The book to be added - the id of the book will be set after successful insertion.
	 * @throws PDOException
     */
    public function addBook($book);

    /** Modifies data related to a book in the collection.
     * @param $book Book The book data to be kept.
     * @todo Implement function using PDO and a real database.
     */
    public function modifyBook($book);

    /** Deletes data related to a book from the collection.
     * @param $id integer The id of the book that should be removed from the collection.
     */
    public function deleteBook($id);
}
?>