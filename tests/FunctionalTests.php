<?php
require_once("vendor/autoload.php");

/**
 * Class for testing the functionality of an Oblig 1 implementation
 * in IMT2571.
 * Please set the value of the $baseUrl to the URL of the assignment page
 * @author Rune Hjelsvold
 */
class FunctionalTests extends \PHPUnit\Framework\TestCase
{
	/**
	* Index of the collection page title in the PAGE_TITLES array
	* @see PAGE_TITLE */
	const COLLECTION_PAGE_TITLE_IDX = 0;

	/**
	* Index of the details page title in the PAGE_TITLES array
	* @see PAGE_TITLE */
	const DETAILS_PAGE_TITLE_IDX = 1;

	/**
	* Index of the error page title in the PAGE_TITLES array
	* @see PAGE_TITLE */
	const ERROR_PAGE_TITLE_IDX = 2;

	/**
	* Constant array for holding 
	* @see PAGE_TITLE */
	protected static $PAGE_TITLES = array(
                        'Book Collection',
						'Book Details',
						'Error Page'
	);
	
	/**
	* Constant for the test case array key refering to the expected outcome of the test case 
	* @see generateTestCases */
	const OUTCOME_IDX = 'outcome';
	
	/**
	* Constant used by test cases to specify that the corresponding book information is valid 
	* @see generateTestCases */
	const OUTCOME_SUCCESS = 0;
	
	/**
	* Constant used by test cases to specify that the corresponding book information is valid
	* @see generateTestCases */
	const OUTCOME_FAILURE = 1;

	/**
	 * Holds the root URL for the Oblig 1 site.
	 */
	protected $baseUrl = 'http://localhost/IMT2571/src';
	
	/**
	 * The Mink Session object.
	 */
	protected $session;
	/**
	 * Holds the ids of books created during testing for cleanup during teardown.
	 */
	protected $testBookIds;

	/**
	 * Initiates the testing session and the cleanup array.
	 * @see teardown
	 */
	protected function setup()
	{
		// Create cleanup array
		$this->testBookIds = array();
        $driver = new \Behat\Mink\Driver\GoutteDriver();
        $this->session = new \Behat\Mink\Session($driver);
        $this->session->start();
	}
		
	/**
	 * Removes entries for all books added to the system during testing.
	 */
 	protected function teardown()
	{
		// Remove all book entries added when testing server
		foreach ($this->testBookIds as $bookId) {
			$this->removeBookEntry($bookId);
		}
	}
	
	/**
	 * Generates a table of book data used when testing add and modify
	 * operations.
	 * @todo Add tests for invalid user data - i.e., title or author are empty
	 *       strings
	 * @return string[] Associative table of test values. Keys title, author, and
	 *                  description refers to test values. Key self::OUTCOME_IDX points
	 *					to whether the book information is valid and should be accepted
	 *                  as a book record or if it should be rejected.
	 * @see OUTCOME_IDX, self::OUTCOME_SUCCESS, self::OUTCOME_FAILURE
	 */
	protected function generateTestCases() 
	{
		$cases = array();
		// The simple case
		$cases[0] = array
		            (
					    'title' => 'Test title',
					    'author' => 'Test author',
					    'description' => 'Test author',
						self::OUTCOME_IDX => self::OUTCOME_SUCCESS
					);
		// Case where value contains single quote character - may break SQL statements
		$cases[1] = array
		            (
					    'title' => "Test title with ' inside",
					    'author' => "Test title with ' inside",
					    'description' => "Test title with ' inside",
						self::OUTCOME_IDX => self::OUTCOME_SUCCESS
					);
		// Case where value contains less than character - may break HTML code
		$cases[2] = array
		            (
					    'title' => '<script document.body.style.visibility="hidden" />',
					    'author' => '<script document.body.style.visibility="hidden" />',
					    'description' => '<script document.body.style.visibility="hidden" />',
						self::OUTCOME_IDX => self::OUTCOME_SUCCESS
					);
		// case where title is blank.
		$cases[3] = array
		            (
					    'title' => '',
					    'author' => 'Test author',
					    'description' => 'Test author',
						self::OUTCOME_IDX => self::OUTCOME_FAILURE
					);
		// case where author is blank.	
		$cases[4] = array
					(	
						'title' => 'Test title',
						'author' => '',
						'description' => 'Test author',
						self::OUTCOME_IDX => self::OUTCOME_FAILURE
					);
		// case where title and author is blank.
		$cases[5] = array
					(	
						'title' => '',
						'author' => '',
						'description' => 'Test author',
						self::OUTCOME_IDX => self::OUTCOME_FAILURE
					);		
		return $cases;
	}
	
	/**
	 * Computes the number of books in the listed collection.
	 * @param $page the web page containing the book list, or the site root page
	 *        if no page reference is passed.
	 * @return integer the number of books listed on the page.
	 */
	protected function getBookListLength($page = null)
	{
		if (!$page)
		{
			$this->session->visit($this->baseUrl);
			$page = $this->session->getPage();
		}
		return sizeof($page->findAll('xpath', 'body/table[@id="bookList"]/tbody/tr'));
	}
		
	/**
	 * Adds a book to the collection. The id of the new created book is added to
	 * in $testBookIds for cleanup purposes.
	 * @param integer $id book id will be set when the book is added to the collection.
	 * @param string $title title of the book to be added.
	 * @param string $author author of the book to be added.
	 * @param string $description description of the book to be added.
	 * @see teardown()
	 */
 	protected function addBook(&$id, $title, $author, $description, $expectedOutcome = self::OUTCOME_SUCCESS)
	{
		// Load book list to get to the addForm
        $this->session->visit($this->baseUrl);
        $page = $this->session->getPage();
		$listLength = $this->getBookListLength($page);
        $addForm = $page->find('xpath', 'body/form[@id="addForm"]');
		
		// Complete and submit addForm
        $addForm->find('xpath', 'input[@name="title"]')->setValue($title);
        $addForm->find('xpath', 'input[@name="author"]')->setValue($author);
        $addForm->find('xpath', 'input[@name="description"]')->setValue($description);
		$addForm->submit();
		
        $page = $this->session->getPage();	

		if ($expectedOutcome == self::OUTCOME_SUCCESS)
		{
		    // Verify that the collection page was returned
            $this->assertTrue($this->isExpectedPage($page, self::COLLECTION_PAGE_TITLE_IDX), 'addBook: expecting collection page');

		    // Record the id of the book if it was added to the list
		    if ($this->getBookListLength($page) > $listLength)
		    {
		    	// Record the id that was assigned to the book - assuming that the newest book is the last and that id has the format bookXXX
			    $id = substr($page->find('xpath', 'body/table/tbody/tr[last()]/@id')->getText(),4);
		
			    $this->testBookIds[] = $id;
		    }
			else
			{
				$this->assertTrue($this->isExpectedPage($page, self::ERROR_PAGE_TITLE_IDX), 'addBook: expecting error page');
			}
		}
	}
		
	/**
	 * Deletes a book from the collection. The id of the new created book is removed 
	 * from $testBookIds because it no longer needs to be cleaned up.
	 * @param integer $id id of the book to be deleted from the collection.
	 * @see teardown()
	 * @uses removeBookEntry()
	 */
 	protected function deleteBook($id)
	{
		// Remove book entry in collection
        $this->removeBookEntry($id);
		
		// Remove book id from cleanup array
		$idx = array_search($id, $this->testBookIds);
		array_splice($this->testBookIds, $idx, 1);
	}
	
	/**
	 * Removes a book entry from the web site collection
	 * @param integer $id id of the book to be removed from the collection.
	 * @see teardown()
	 */
 	protected function removeBookEntry($bookId)
	{
		// Load page containing form to delete the book entry
        $this->session->visit($this->baseUrl . '?id=' . $bookId);
        $page = $this->session->getPage();
		
		// Submit the delete form
        $delForm = $page->find('xpath', 'body/form[@id="delForm"]');
		$delForm->submit();
	}
	
	/**
	 * Modifies data about a book in the collection
	 * one requested.
	 * @param integer $id id of the book to be updated.
	 * @param string $title book title 
	 * @param string $author book author
	 * @param string $description book description
	 */
 	protected function modifyBook($id, $title, $author, $description)
	{
		// Load page containing form to modify the book entry
        $this->session->visit($this->baseUrl . '?id=' . $id);
        $page = $this->session->getPage();

		// Complete and submit the form to modify the book entry
        $modForm = $page->find('xpath', 'body/form[@id="modForm"]');
        $modForm->find('xpath', 'input[@name="title"]')->setValue($title);
        $modForm->find('xpath', 'input[@name="author"]')->setValue($author);
        $modForm->find('xpath', 'input[@name="description"]')->setValue($description);
		$modForm->submit();
		
	}
		
	/**
	 * Asserts that data about a book in the collection matches the passed values.
	 * Also asserts that the data matches the data on the book details page.
	 * @param integer $bookId id of the book to be asserted.
	 * @param string $bookTitle book title 
	 * @param string $bookAuthor book author
	 * @param string $bookDescription book description
	 * @uses string $assertBookDetails() 
	 */
	protected function assertBookListEntry($bookId, $bookTitle, $bookAuthor, $bookDescription)
	{
		// Load book list to get to the book entries
		$this->session->visit($this->baseUrl);
        $page = $this->session->getPage();		

		// Find the book entry and verify the data matches the expected value
		$book = $page->find('xpath', 'body/table/tbody/tr[@id="book' . $bookId . '"]');
		if ($book)
		{
			$this->assertEquals($bookId, $book->find('xpath', 'td[1]/a')->getText(), 'assertBookListEntry: id');
			$this->assertEquals($bookTitle, $book->find('xpath', 'td[position() = 2]')->getText(), 'assertBookListEntry: title');
			$this->assertEquals($bookAuthor, $book->find('xpath', 'td[position() = 3]')->getText(), 'assertBookListEntry: author');
			$this->assertEquals($bookDescription, $book->find('xpath', 'td[position() = 4]')->getText(), 'assertBookListEntry: description');
		
			// Further verify that the content is the same on the details page
			$this->assertBookDetails($bookId, $bookTitle, $bookAuthor, $bookDescription);
		}
		else
		{
			// Book not found
			$this->assertTrue(false, "assertBookListEntry: book expected for id=$bookId");
		}
	}

	/**
	 * Asserts that data about a book matches the data displayed on the book details page.
	 * @param integer $bookId id of the book to be asserted.
	 * @param string $bookTitle book title 
	 * @param string $bookAuthor book author
	 * @param string $bookDescription book description
	 */
    protected function assertBookDetails($bookId, $bookTitle, $bookAuthor, $bookDescription)
    {
		// Load book details page
		$this->session->visit($this->baseUrl . '?id=' . $bookId);
        $page = $this->session->getPage();		

		// Verify values shown on form
        $modForm = $page->find('xpath', 'body/form[@id="modForm"]');
        $this->assertEquals($bookId, $modForm->find('xpath', 'input[@name="id"]')->getValue(), 'assertBookListEntry: book id');
        $this->assertEquals($bookTitle, $modForm->find('xpath', 'input[@name="title"]')->getValue(), 'assertBookListEntry: book title');
        $this->assertEquals($bookAuthor, $modForm->find('xpath', 'input[@name="author"]')->getValue(), 'assertBookListEntry: book author');
        $this->assertEquals($bookDescription, $modForm->find('xpath', 'input[@name="description"]')->getValue(), 'assertBookListEntry: book description');
    }
	
	/**
	 * Checks if the page is the expected one - based on page title.
	 * @param DOMElement $page DocumentElement The root element of page to be checked.
	 * @param integer $expectedIdx The index of the expected page - within the
     *                $PAGE_TITLE array
	 * @param string $bookAuthor book author
	 * @see $PAGE_TITLES book description
	 */
	protected function isExpectedPage($page, $expectedIdx)
	{
		$title = null;
		$titleEl = $page->find('xpath', 'head/title');
		
		// Page has a title element
		if ($titleEl)
		{
			$title = $titleEl->getText();
		}
		
		// Compare title to the expected value
		return $title === self::$PAGE_TITLES[$expectedIdx];
	}
	
	/**
	 * General test of the structure of book collection page.
	 * @test
	 */
    public function testBookCollectionPage()
    {
        $this->session->visit($this->baseUrl);
        $page = $this->session->getPage();

		// Verifying that a collection page was returned 
        $this->assertTrue($this->isExpectedPage($page, self::COLLECTION_PAGE_TITLE_IDX), 'testBookCollectionPage: expecting collection page');

		// Verifying the presence of the form for adding new books
		$addForm = $page->find('xpath', 'body/form[@id="addForm"]');
		$this->assertNotNull($addForm, 'testBookCollectionPage: addForm present');

		// Verifying the presence of the operator for adding new books
		$addOp = $addForm->find('xpath', 'input[@name="op"]');
        $this->assertEquals('add', $addOp->getValue(), 'testBookCollectionPage: expecting add operation request');
	}
	
	/**
	 * General test of the structure of the book details page
	 * @depends testBookCollectionPage
	 */
    public function testBookDetailsPage()
    {
		$testBookId = -1;
		$this->addBook($testBookId, 'Test title', 'Test author', 'Test author');

		// Load book details page
		$this->session->visit($this->baseUrl . '?id=' . $testBookId);
        $page = $this->session->getPage();

		// Verifying page title
        $this->assertTrue($this->isExpectedPage($page, self::DETAILS_PAGE_TITLE_IDX), 'testBookDetailsPage: expecting details page');

		// Verifying the form for modifying book data
        $form = $page->find('xpath', 'body/form[@id="modForm"]');
		$this->assertNotNull($form, 'testBookDetailsPage: modForm', 'testBookDetailsPage: expecting modForm present');
		$op = $form->find('xpath', 'input[@name="op"]');
        $this->assertEquals('mod', $op->getValue(), 'testBookDetailsPage: expecting mod operation request');

		// Verifying the form for deleting book entries
        $form = $page->find('xpath', 'body/form[@id="delForm"]');
		$this->assertNotNull($form, 'testBookDetailsPage: delForm', 'testBookDetailsPage: expecting delForm present');
		$op = $form->find('xpath', 'input[@name="op"]');
        $this->assertEquals('del', $op->getValue(), 'testBookDetailsPage: expecting del operation request');
	}
	
	/**
	 * Tests adding new books using various book test cases
	 * @see generateTestCases()
	 * @todo add functionality for testing unsuccessful cases
	 * @depends testBookDetailsPage
	 */
    public function testAdd()
    {
		$bookListLength = $this->getBookListLength();		
		foreach ($this->generateTestCases() as $testCase)
		{
			if ($testCase[self::OUTCOME_IDX] === self::OUTCOME_SUCCESS)
			{
				$testBookId = -1;
				$this->addBook($testBookId, $testCase['title'], $testCase['author'], $testCase['description']);
				$bookListLength += 1;
			
				// Verifying book content in book list and on book details page
				$this->assertEquals($bookListLength, $this->getBookListLength(), 'testAdd: bookListLength');		
				$this->assertBookListEntry($testBookId, $testCase['title'], $testCase['author'], $testCase['description']);
			}
			else
			{
				// Verifying that error page is returned, by adding a book with illegal data.
				$testBookId = -1;
				$this->addBook($testBookId, $testCase['title'], $testCase['author'], $testCase['description'], self::OUTCOME_FAILURE);
				$page = $this->session->getPage();	
				$this->assertTrue($this->isExpectedPage($page, self::ERROR_PAGE_TITLE_IDX), 'testAdd: expecting error page');
			}
		}
	}
	
	/**
	 * Tests deleting a book entry
	 * @depends testBookDetailsPage
	 */
    public function testDelete()
    {
		$testBookId = -1;
        $this->addBook($testBookId, 'Test book', 'Test author', 'Test description');
		$bookListLength = $this->getBookListLength();
		
		$this->deleteBook($testBookId);
		$this->session->visit($this->baseUrl);
        $page = $this->session->getPage();		

		// Verifying that book is removed from book list
		$this->assertEquals($bookListLength-1, $this->getBookListLength($page), 'testDelete: bookListLength');		
		$book = $page->find('xpath', 'body/table/tbody/tr[@id="book' . $testBookId . '"]');
		$this->assertNull($book, 'testDelete: book not in book table');
    }
	
	/**
	 * Tests modifying book date using various test cases
	 * @see generateTestCases()
	 * @todo add functionality for testing unsuccessful cases
	 * @depends testBookDetailsPage
	 */
    public function testModify()
    {
		$testBookId = -1;
		$bookListLength = $this->getBookListLength();
				
        $this->addBook($testBookId, 'Test book for modify', 'Test author for modify', 'Test description for modify');
		
		$bookListLength = $this->getBookListLength();
				
		foreach ($this->generateTestCases() as $testCase)
		{
			if ($testCase[self::OUTCOME_IDX] === self::OUTCOME_SUCCESS)
			{
				$this->modifyBook($testBookId, $testCase['title'], $testCase['author'], $testCase['description']);

				// Verifying book content in book list and on book details page
				$this->assertEquals($bookListLength, $this->getBookListLength(), 'testModify: bookListLength');		
				$this->assertBookListEntry($testBookId, $testCase['title'], $testCase['author'], $testCase['description']);
			}
			else
			{
				// Verifying that error page is returned, by trying to modify a book with illegal data.
				$this->modifyBook($testBookId, $testCase['title'], $testCase['author'], $testCase['description']);
				$page = $this->session->getPage();	
				$this->assertTrue($this->isExpectedPage($page, self::ERROR_PAGE_TITLE_IDX), 'testModify: expecting error page');
			}
		}
    }
	
	/**
	 * Tests if book details page is open for SQL injection
	 * @depends testBookDetailsPage
	 */
	public function testSqlInjection()
	{
		$testBookId = -1;
        $this->addBook($testBookId, 'Test book', 'Test author', 'Test description');
		
		$this->session->visit($this->baseUrl . '?id=' . $testBookId . "'; drop table books;--");
        $page = $this->session->getPage();

		// Verifying that id containing injection code was rejected	
        $this->assertTrue($this->isExpectedPage($page, self::ERROR_PAGE_TITLE_IDX), 'testSqlInjection: expecting error page');
	}
}
?>