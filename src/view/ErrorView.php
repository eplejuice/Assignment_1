<?php
include_once('View.php');

/** The BookView is the class that creates the page showing details about one book. 
 * @author Rune Hjelsvold
 * @see http://php-html.net/tutorials/model-view-controller-in-php/ The tutorial code used as basis.
 */
Class ErrorView extends View {
	protected $message = null;
	/** Set a message to be given to the end user if 
	 * @$msg string The message to pass to the user.
	 */	 
	public function __construct($msg = null)
	{
		if ($msg) 
		{
		    $this->message = $msg;
		}
		else
		{
			$this->message = 'Something bad happened.';
		}
	}

	/** Used by the superclass to generate page title
	 */
	protected function getPageTitle() {
		return 'Error Page';
	}
	
	/** Used by the superclass to generate page content
	 */
	protected function getPageContent() {
        return "<p>{$this->message}</p><p><a href=index.php>Back to book list</a></p>";
	}	
}
?>
