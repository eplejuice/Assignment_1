<?php
include_once('View.php');

/** The BookView is the class that creates the page showing details about one book. 
 * @author Rune Hjelsvold
 * @see http://php-html.net/tutorials/model-view-controller-in-php/ The tutorial code used as basis.
 */
Class BookView extends View {
	protected $book;
	protected $opParamName;
	protected $delOpName;
	
    /** Constructor 
     * @author Rune Hjelsvold
	 * @param Book $book The book to be shown.
	 * @param string $opParamName The name of the parameter to used in the query string for passing the operation to be performed.
	 * @param string $delOpName The name to be used for the delete operation.
	 * @param string $modOpName The name to be used the modify operation.
     * @see http://php-html.net/tutorials/model-view-controller-in-php/ The tutorial code used as basis.
     */
	public function __construct($book, $opParamName, $delOpName, $modOpName)  
    {  
        $this->book = $book;
        $this->opParamName = $opParamName;
        $this->delOpName = $delOpName;
        $this->modOpName = $modOpName;
    } 
	
	/** Used by the superclass to generate page title
	  * @return string Page title.
	  */
	protected function getPageTitle() {
		return 'Book Details';
	}
	
	/** Helper function generating HTML code for the form for removing books from the collection
	 */
	protected function createDeleteButton() {
		return 
		'<form id="delForm" action="index.php" method="post">'
		. '<input name="'.$this->opParamName.'" value="'.$this->delOpName.'" type="hidden" />'
		. '<input name="id" value="'.$this->book->id.'" type="hidden" />'
        . '<input type="submit" value="Delete book record" />'
        . '</form>';
	}
	
	/** Helper function generating HTML code for the form for modifying book data
	 */
	protected function createModifyForm() {
		return 
		'<form id="modForm" action="index.php" method="post">'
		. '<input name="'.$this->opParamName.'" value="'.$this->modOpName.'" type="hidden" />'
		. '<input name="id" value="'.$this->book->id.'" type="hidden"/>'
		. 'Title:<br/>'
		. '<input name="title" type="text" value="'.htmlspecialchars($this->book->title).'" /><br/>'
		. 'Author:<br/>'
		. '<input name="author" type="text" value="'.htmlspecialchars($this->book->author).'" /><br/>'
		. 'Description:<br/>'
		. '<input name="description" type="text" value="'.htmlspecialchars($this->book->description).'" /><br/>'
        . '<input type="submit" value="Update book record" />'
        . '</form>';
	}
	
	/** Used by the superclass to generate page content
	 */
	protected function getPageContent() {
        return 'ID:' . $this->book->id
			   . $this->createModifyForm()
			   . $this->createDeleteButton()
			   . '<p><a href=index.php>Back to book list</a></p>';
	}	
}
?>
