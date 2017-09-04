<?php
require_once("vendor/autoload.php");
require_once("../src/model/DBModelTmpl.php");
require_once("TestDBProps.php");

use PHPUnit\Framework\TestCase;
use PHPUnit\DbUnit\TestCaseTrait;

class UnitTests extends TestCase
{
    use TestCaseTrait;

    // only instantiate pdo once for test clean-up/fixture load
    static private $pdo = null;

    // only instantiate PHPUnit_Extensions_Database_DB_IDatabaseConnection once per test
    private $conn = null;
	
	/**
	* Constant for an invalid id - can be used to test operations called on non-existing books
    */	
	const INVALID_BOOK_ID = -99;
	
	/**
	* Constant for the test case array key refering to the last entry representing fixture data 
	* @see $TEST_CASES */
	const FIXTURE_SIZE = 3;
	
	/**
	* Constant for the test case array key refering to the expected outcome of the test case 
	* @see $TEST_CASES */
	const OUTCOME_IDX = 'outcome';
	
	/**
	* Constant used by test cases to specify that the corresponding book information is valid 
	* @see $TEST_CASES */
	const OUTCOME_SUCCESS = 0;
	
	/**
	* Constant used by test cases to specify that the corresponding book information is valid
	* @see $TEST_CASES */
	const OUTCOME_FAILURE = 1;

	/**
	* Array containing test cases -with key for book . The first part - up to FIXTURE_SIZE - represent the fixtures.
	* The remaining part contains cases for add and modify operations. The OUTCOME_IDX key is used
	
	* @see FIXTURE_SIZE
	* @see OUTCOME_IDX
	* @see OUTCOME_SUCCESS
	* @see OUTCOME_FAILURE
	*/	
	protected static $TEST_CASES = array (
				       array ('id' => 1, 
					          'title' => 'Jungle Book',
						      'author' => 'R. Kipling',
                              'description' => 'A classic book.'
                       ),						
				       array ('id' => 2, 
					          'title' => 'Moonwalker',
						      'author' => 'J. Walker',
                              'description' => null
                       ),						
				       array ('id' => 3, 
					          'title' => 'PHP & MySQL for Dummies',
						      'author' => 'J. Valade',
                              'description' => 'Written by some smart gal.'
					   ),
					   // End of fixtures
				       array ('id' => null, 
					          'title' => 'Test title',
						      'author' => 'Test author',
                              'description' => 'Test description',
							  self::OUTCOME_IDX => self::OUTCOME_SUCCESS
                       ),						
				       array ('id' => null, 
					          'title' => "Test title with ' inside",
						      'author' => "Test author with ' inside",
                              'description' => "Test description with ' inside",
							  self::OUTCOME_IDX => self::OUTCOME_SUCCESS
                       ),						
				       array ('id' => null, 
					          'title' => '<script document.body.style.visibility="hidden" />',
						      'author' => '<script document.body.style.visibility="hidden" />',
                              'description' => '<script document.body.style.visibility="hidden" />',
							  self::OUTCOME_IDX => self::OUTCOME_SUCCESS
                       )
                    );					   

    /**
	 * Returning the PDO object for the database to be used for tests.
     * @return PHPUnit_Extensions_Database_DB_IDatabaseConnection
     */
	final public function getConnection()
    {
        if ($this->conn === null) {
            if (self::$pdo == null) {
                self::$pdo = new PDO('mysql:dbname=test;host=' . TEST_DB_HOST,
                        				TEST_DB_USER, TEST_DB_PWD,
										array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
            }
            $this->conn = $this->createDefaultDBConnection(self::$pdo, 'test');
        }

        return $this->conn;
    }
	
    /**
	 * Returning the fixtures.
     * @return PHPUnit_Extensions_Database_DataSet_IDataSet
     */
    protected function getDataSet()
    {
        return $this->createXMLDataSet('UnitTestFixture.xml');
    }	

    /**
     * Tests for DBModel::getBookList()
     */	 
	public function testGetBookList()
	{
		$model = new DBModel(self::$pdo);
		
		$bookList = $model->getBookList();
		
		// Are all books from the fixtures loaded?
		$this->assertEquals(self::FIXTURE_SIZE, sizeof($bookList), "Unexpected booklist length");
		
		// Is each book correctly represented in database?
		for ($cnt = 0; $cnt < self::FIXTURE_SIZE; $cnt++) {
			$this->assertBookData($cnt, $bookList[$cnt]);
		}
	}
	
    /**
     * Tests for DBModel::getBookById()
     */	 
	public function testGetBook()
	{
		$model = new DBModel(self::$pdo);
		
		// Can all books from the fixtures be recalled?
		for ($i = 0; $i < self::FIXTURE_SIZE; $i++) {
			$this->assertBookData($i, $model->getBookById(self::$TEST_CASES[$i]['id']));
		}
		
		// No book should be returned for invalid ids
		$this->assertNull($model->getBookById(self::INVALID_BOOK_ID), "No book expected for invalid id.");
	}
	
    /**
     * Tests for DBModel::addBook()
     */	 
	public function testAddBook()
	{
		$model = new DBModel(self::$pdo);
		$dbSize = $this->getConnection()->getRowCount('book');
		
		// Run tests for all non-fixture cases
		for ($i = self::FIXTURE_SIZE; $i < sizeof(self::$TEST_CASES); $i++)
		{
			// Run test for cases that should succeed without problems
			if (self::$TEST_CASES[$i][self::OUTCOME_IDX] == self::OUTCOME_SUCCESS)
			{
				$book = $this->generateTestBook($i);
				$model->addBook($book);
				
				// Was the book added?
				$this->assertGreaterThan($dbSize, $book->id);
				$dbSize ++;

                // Set the newly assigned id for the test case too before comparing				
				self::$TEST_CASES[$i]['id'] = $book->id;
				$this->assertBookData($i, $book);
			}
			else
			{
				//TODO: Add tests for unsuccessful cases
			}
		}
	}
	
    /**
     * Tests for DBModel::modifyBook()
	 * Warning, this method has a potential side effect: id of elements in
	 * TEST_CASES may be changed if the test fails.
	 * @see TEST_CASES
     */	 
	public function testModifyBook()
	{
		$model = new DBModel(self::$pdo);
		
		// Run tests for all non-fixture cases
		for ($i = self::FIXTURE_SIZE; $i < sizeof(self::$TEST_CASES); $i++)
		{
			// Run test for cases that should succeed without problems
			if (self::$TEST_CASES[$i][self::OUTCOME_IDX] == self::OUTCOME_SUCCESS)
			{
				// Keeping test case id for cleanup
                $realId = self::$TEST_CASES[$i]['id'];

                // Adding a new test case as the target for modification
				self::$TEST_CASES[$i]['id'] = self::$TEST_CASES[0]['id'];
				$book = $this->generateTestBook($i);
				$model->modifyBook($book);

                // Verify that data was correctly changed
				$this->assertBookData($i, $model->getBookById(self::$TEST_CASES[$i]['id']));
				
				// Reset id for test case
                self::$TEST_CASES[$i]['id'] = $realId;
				
				// Restore database values
				$book = $this->generateTestBook(0);
				$model->modifyBook($book);
			}
			else
			{
				//TODO: Add tests for unsuccessful cases
			}
		}
	}
	
    /**
     * Tests for DBModel::deleteBook()
     */	 
	public function testDeleteBook()
	{
		$model = new DBModel(self::$pdo);
		$dbSize = $this->getConnection()->getRowCount('book');
		
        // Using the first test case as the target for deletions	
        $id = self::$TEST_CASES[0]['id'];
		$model->deleteBook($id);
		$dbSize--;
		
		// Verifying that a book has disappeared
		$this->assertEquals($dbSize, $this->getConnection()->getRowCount('book'), "Expecting book table size to decrement");		
		$this->assertNull($model->getBookById($id), "Expecting book to have been removed from collection");		

		// Verifying that no books are deleted when called on invalid ids
		$model->deleteBook(self::INVALID_BOOK_ID);
		$this->assertEquals($dbSize, $this->getConnection()->getRowCount('book'), "Invalid book ID: Expecting book table size to decrement");		
	}
	
    /**
     * Generates a Book object corresponding to the test case in $TEST_CASES.
	 * @param int $caseIdx Index of the test as
	 * @return Book A Book object corresponding to the test case
	 * @see $TEST_CASES
     */	 
	protected function generateTestBook ($caseIdx)
	{
		return new Book(
		           self::$TEST_CASES[$caseIdx]['title'],
		           self::$TEST_CASES[$caseIdx]['author'],
		           self::$TEST_CASES[$caseIdx]['description'],
		           self::$TEST_CASES[$caseIdx]['id']);
	}
	
    /**
     * Asserts that the attribute of a Book object is matching values in the corresponding test case in $TEST_CASES.
	 * @param int $caseIdx Index of test case in $TEST_CASES to be compared against
	 * @param Book $book Book object to test
	 * @see $TEST_CASES
     */	 
	protected function assertBookData($caseIdx, $book)
	{
		$this->assertEquals(self::$TEST_CASES[$caseIdx]['id'], $book->id, "Unexpected book id");
		$this->assertEquals(self::$TEST_CASES[$caseIdx]['title'], $book->title, "Unexpected book title");
		$this->assertEquals(self::$TEST_CASES[$caseIdx]['author'], $book->author, "Unexpected book author");
		$this->assertEquals(self::$TEST_CASES[$caseIdx]['description'], $book->description, "Unexpected book description");
	}
}
?>