<?php

//https://www.sitepoint.com/php-dom-working-with-xml/

namespace App\Element;

use Exception;

class Fcpxml
{
    private $xmlPath;
    private $domDocument;

    public function __construct($xmlPath) {
        //loads the document

        $doc = new \DOMDocument();
        $doc->load($xmlPath);

        //is this a library xml file?
        If ($doc->doctype->name != "fcpxml") {
            // || $doc->doctype->systemId != "fcpxml.dtd")
            throw new Exception("Incorrect document type: " . $doc->doctype->systemId );
        }

        //is the document valid and well-formed?
        if(true || $doc->schemaValidate('http://transcribe/xml/fcpxml18.dtd')) {
            $this->domDocument = $doc;
            $this->xmlPath = $xmlPath;
        }
        else {
            throw new Exception("Document did not validate");
        }
    }

    public function __destruct() {
        unset($this->domDocument);
    }

    public function getFormats()
    {
        return $this->domDocument->getElementsByTagName('format');
    }

    public function getBookByISBN($isbn) {
        // TODO: return an array with properties of a book
    }

    public function addBook($isbn, $title, $author, $genre, $chapters) {
        // TODO: add a book to the library
    }

    public function deleteBook($isbn) {
        // TODO: Delete a book from the library
    }

    public function findBooksByGenre($genre) {
        // TODO: Return an array of books
    }
}