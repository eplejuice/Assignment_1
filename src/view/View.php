<?php
/** The View is the superclass that sets up the page for each of the views. 
 * @author Rune Hjelsvold
 * @see http://php-html.net/tutorials/model-view-controller-in-php/ The tutorial code used as basis.
 */

abstract Class View {
	/** Is used to retrieve the title of the given view page
	 * @return string View page title.
	 */
    abstract protected function getPageTitle();
	
	/** Is used to retrieve the page content of the given view page
	 * @return string View page content.
	 */
	abstract protected function getPageContent();

	/** Creates HTML code for the given view page
	 */
	public function create() {
	    echo <<<HTML
<!DOCTYPE html>
<html>
<head>
<title>
HTML;
        echo $this->getPageTitle();
		echo <<<HTML
</title>
</head>

<body>
<h1>
HTML;
        echo $this->getPageTitle();
		echo <<<HTML
</h1>
HTML;
        echo $this->getPageContent();
		echo <<<HTML
</body>
</html>	
HTML;
		 }
	}
?>