<?php

class Book
{
    private $title;
    private $author;
    private $year;
    private $isBorrowed;
    private $borrower;
    private $returnDate;
    private $fine;

    public function __construct($title, $author, $year)
    {
        $this->title = $title;
        $this->author = $author;
        $this->year = $year;
        $this->isBorrowed = false;
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function getAuthor()
    {
        return $this->author;
    }

    public function getYear()
    {
        return $this->year;
    }

    public function getBorrower()
    {
        return $this->borrower;
    }
    public function getReturnDate()
    {
        return $this->returnDate;
    }
    public function isBorrowed()
    {
        return $this->isBorrowed;
    }
    public function borrowBook($borrower, $returnDate)
    {
        if (!$this->isBorrowed) {
            $this->isBorrowed = true;
            $this->borrower = $borrower;
            $this->returnDate = $returnDate;
        }
    }
    public function returnBook()
{
    if ($this->isBorrowed) {
        $today = new DateTime();
        $returnDate = new DateTime($this->returnDate);
        if ($returnDate < $today) {
            $difference = $today->diff($returnDate);
            $daysOverdue = $difference->days;

            $fine = $daysOverdue * 1000;

            $this->fine = $fine;

            echo "<script>alert('Buku berhasil dikembalikan. Anda dikenakan denda sebesar $fine');</script>";
        } else {
            echo "<script>alert('Buku berhasil dikembalikan. Tidak ada denda yang dikenakan');</script>";
        }

        $this->isBorrowed = false;
        $this->borrower = "";
        $this->returnDate = "";
    }
}

}

class ReferenceBook extends Book
{
    private $isbn;
    private $publisher;

    public function __construct($title, $author, $year, $isbn, $publisher)
    {
        parent::__construct($title, $author, $year);
        $this->isbn = $isbn;
        $this->publisher = $publisher;
    }

    public function getISBN()
    {
        return $this->isbn;
    }

    public function getPublisher()
    {
        return $this->publisher;
    }
}
