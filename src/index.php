<?php 
/** The start page of the application. 
 * Control is forwarded to the controller
 * @author Rune Hjelsvold
 * @see http://php-html.net/tutorials/model-view-controller-in-php/ The tutorial code used as basis.
 */

    include_once("controller/Controller.php");

	$controller = new Controller();
	$controller->invoke();

?>