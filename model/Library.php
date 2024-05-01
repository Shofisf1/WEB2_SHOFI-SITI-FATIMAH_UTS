<?php
class Library
{
    private $books = [];

    public function addBook(ReferenceBook $book)
    {
        $this->books[] = $book;
    }
    public function removeBook($isbn)
    {
        foreach ($this->books as $key => $book) {
            if ($book instanceof ReferenceBook && $book->getISBN() === $isbn) {
                unset($this->books[$key]);
                return true;
            }
        }
        return false;
    }
    public function getBooks()
    {
        return $this->books;
    }
    public function findBookByISBN($isbn)
    {
        foreach ($this->books as $key => $book) {
            if ($book instanceof ReferenceBook && $book->getISBN() === $isbn) {
                return $this->books[$key];
            }
        }
        return false;
    }

    public function sortBooks($criteria)
    {
        $sortedBooks = $this->books;

        usort($sortedBooks, function ($a, $b) use ($criteria) {
            if ($criteria === 'penulis') {
                return strcmp($a->getAuthor(), $b->getAuthor());
            } elseif ($criteria === 'tahun') {
                return $a->getYear() - $b->getYear();
            }
            return 0;
        });

        return $sortedBooks;
    }
    public function checkBorrowerLimit($borrower)
    {
        $borrowerBookCount = 0;

        foreach ($this->books as $book) {
            if ($book->isBorrowed()) {
                $borrowerBook = $book->getBorrower();
                if ($borrowerBook === $borrower) {
                    $borrowerBookCount++;
                }
            }
        }

        if ($borrowerBookCount >= 3) {
            echo "<script>alert('Maaf, Anda telah mencapai batas peminjaman buku');</script>";
            return false;
        }
        return true;
    }
    public function searchBooks($keyword)
    {
        $results = [];

        foreach ($this->books as $book) {
            if (stripos($book->getTitle(), $keyword) !== false || stripos($book->getAuthor(), $keyword) !== false) {
                $results[] = $book;
            }
        }

        return $results;
    }
    public function saveToSession()
    {
        $_SESSION['library'] = $this;
    }
}
