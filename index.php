<?php
require_once "model/Library.php";
require_once "model/Book.php";
session_start();

if (!isset($_SESSION['library'])) {
    $_SESSION['library'] = new Library();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['addBook'])) {
        $isbn = $_POST['isbn'];
        $judul = $_POST['judul'];
        $penulis = $_POST['penulis'];
        $penerbit = $_POST['penerbit'];
        $tahun = $_POST['tahun'];

        $newBook = new ReferenceBook($judul, $penulis, $tahun, $isbn, $penerbit);
        $_SESSION['library']->addBook($newBook);
    }
    if (isset($_POST['removeBook'])) {

        if (isset($_POST['bookId'])) {

            $isbn = $_POST['bookId'];
            if (isset($_SESSION['library'])) {
                $_SESSION['library']->removeBook($isbn);
            }
        }
    }

    if (isset($_POST['pinjamBook'])) {
        $isbn = $_POST['isbn'];
        $peminjam = $_POST['peminjam'];
        $tanggal_kembali = $_POST['tanggal'];

        if ($_SESSION['library']->checkBorrowerLimit($peminjam)) {
            $book = $_SESSION['library']->findBookByISBN($isbn);

            if ($book) {
                $book->borrowBook($peminjam, $tanggal_kembali);
                $_SESSION['library']->saveToSession();
            }
        }
    }

    if (isset($_POST['returnBook'])) {
        $isbn = $_POST['isbn'];

        $book = $_SESSION['library']->findBookByISBN($isbn);

        if ($book) {
            $book->returnBook();
            $_SESSION['library']->saveToSession();
        }
    }
}
?>

<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Perpustakaan | Beranda</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <style>
        .card-book {
            width: 200px;
        }
    </style>
</head>

<body>
    <nav class="navbar bg-primary">
        <div class="container d-flex justify-content-center">
            <a class="navbar-brand" href="#">
                Shofi Siti Fatimah Perpustakaan
            </a>
        </div>
    </nav>
    <div class="modal fade" id="modalPinjam" tabindex="-1" aria-labelledby="modalLabelPinjam" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="modalLabelPinjam">Pinjam Buku?</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST">
                    <div class="modal-body">
                        <input type="hidden" class="form-control" id="modalISBN" name="isbn" required>
                        <div class="mb-3">
                            <label for="modalPeminjam" class="form-label">Nama Peminjam</label>
                            <input type="text" class="form-control" name="peminjam" id="modalPeminjam" required>
                        </div>
                        <div class="input-group date mb-3" id="datepicker">
                            <input type="date" class="form-control" name="tanggal" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Tidak</button>
                        <button type="submit" name="pinjamBook" class="btn btn-primary">Ya</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="modal fade" id="modalHapus" tabindex="-1" aria-labelledby="modalLabelHapus" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="modalLabelHapus">Hapus Buku?</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST">
                    <div class="modal-body">
                        <p>Apakah anda yakin ingin menghapus buku ini?</p>
                        <input type="hidden" name="bookId" id="bookId" value="" />
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Tidak</button>
                        <button type="submit" name="removeBook" class="btn btn-primary">Ya</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="container">
        <div class="mx-auto px-5 my-3">
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST">
                <div class="input-group">
                    <input type="text" class="form-control" placeholder="Cari buku" name="keyword" aria-label="Recipient's username" aria-describedby="basic-addon2">
                    <button class="btn btn-outline-secondary" type="submit" id="button-addon2">Cari</button>
                </div>
            </form>
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST">
                <div class="mb-3">
                    <label for="sort" class="form-label">Sortir Berdasarkan</label>
                    <select class="form-select w-25" aria-label="Sortir Buku" id="sort" name="sort">
                        <option selected value="penulis">Penulis</option>
                        <option value="tahun">Tahun Terbit</option>
                    </select>
                </div>
                <button type="submit" name="apply_sort" class="btn btn-primary">Terapkan Sorting</button>
            </form>
        </div>

        <div class="book-container text-center">
            <h3>List Buku Tersedia</h3>
            <div class="list-book d-flex flex-wrap justify-content-center gap-2">
                <?php
                if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['apply_sort'])) {
                    $sortCriteria = $_POST['sort'];

                    $sortedBooks = $_SESSION['library']->sortBooks($sortCriteria);

                    foreach ($sortedBooks as $book) {
                        if (!$book->isBorrowed()) {
                            echo "<div class='card card-book'>";
                            echo "<h5 class='card-header'>" . $book->getTitle() . "</h5>";
                            echo "<div class='card-body'>";
                            echo "<h5 class='card-title'>" . $book->getAuthor() . "</h5>";
                            echo "<p class='card-text'>" . $book->getYear() . "</p>";
                            echo "<p class='card-text'>" . $book->getPublisher() . "</p>";
                            echo "<div class='d-flex flex-col gap-2 justify-content-center'>";
                            echo "<a type='button' class='btn btn-primary btn-pinjam' data-bs-toggle='modal' data-bs-target='#modalPinjam' data-isbn='" . $book->getISBN() . "'>Pinjam</a>";
                            echo "<a type='button' class='btn btn-danger btn-hapus' data-bs-toggle='modal' data-bs-target='#modalHapus' data-isbn='" . $book->getISBN() . "'>Hapus</a>";
                            echo "</div>";
                            echo "</div>";
                            echo "</div>";
                        }
                    }
                } elseif ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['keyword'])) {
                    $keyword = $_POST['keyword'];
                    $searchResults = $_SESSION['library']->searchBooks($keyword);
                    foreach ($searchResults as $book) {
                        if (!$book->isBorrowed()) {
                            echo "<div class='card card-book'>";
                            echo "<h5 class='card-header'>" . $book->getTitle() . "</h5>";
                            echo "<div class='card-body'>";
                            echo "<h5 class='card-title'>" . $book->getAuthor() . "</h5>";
                            echo "<p class='card-text'>" . $book->getYear() . "</p>";
                            echo "<p class='card-text'>" . $book->getPublisher() . "</p>";
                            echo "<div class='d-flex flex-col gap-2 justify-content-center'>";
                            echo "<a type='button' class='btn btn-primary btn-pinjam' data-bs-toggle='modal' data-bs-target='#modalPinjam' data-isbn='" . $book->getISBN() . "'>Pinjam</a>";
                            echo "<a type='button' class='btn btn-danger btn-hapus' data-bs-toggle='modal' data-bs-target='#modalHapus' data-isbn='" . $book->getISBN() . "'>Hapus</a>";
                            echo "</div>";
                            echo "</div>";
                            echo "</div>";
                        }
                    }
                } else {
                    foreach ($_SESSION['library']->getBooks() as $book) {
                        if (!$book->isBorrowed()) {
                            echo "<div class='card card-book'>";
                            echo "<h5 class='card-header'>" . $book->getTitle() . "</h5>";
                            echo "<div class='card-body'>";
                            echo "<h5 class='card-title'>" . $book->getAuthor() . "</h5>";
                            echo "<p class='card-text'>" . $book->getYear() . "</p>";
                            echo "<p class='card-text'>" . $book->getPublisher() . "</p>";
                            echo "<div class='d-flex flex-col gap-2 justify-content-center'>";
                            echo "<a type='button' class='btn btn-primary btn-pinjam' data-bs-toggle='modal' data-bs-target='#modalPinjam' data-isbn='" . $book->getISBN() . "'>Pinjam</a>";
                            echo "<a type='button' class='btn btn-danger btn-hapus' data-bs-toggle='modal' data-bs-target='#modalHapus' data-isbn='" . $book->getISBN() . "'>Hapus</a>";
                            echo "</div>";
                            echo "</div>";
                            echo "</div>";
                        }
                    }
                }
                ?>
            </div>
        </div>
        <div class="card my-3">
            <div class="card-header">
                Tambah Buku
            </div>
            <div class="card-body">
                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST">
                    <div class="mb-3">
                        <label for="inputISBN" class="form-label">ISBN</label>
                        <input type="text" class="form-control" id="inputISBN" name="isbn" required>
                    </div>
                    <div class="mb-3">
                        <label for="inputJudul" class="form-label">Judul Buku</label>
                        <input type="text" class="form-control" id="inputJudul" name="judul" required>
                    </div>
                    <div class="mb-3">
                        <label for="inputPenulis" class="form-label">Penulis</label>
                        <input type="text" class="form-control" id="inputPenulis" name="penulis" required>
                    </div>
                    <div class="mb-3">
                        <label for="inputPenerbit" class="form-label">Penerbit</label>
                        <input type="text" class="form-control" id="inputPenerbit" name="penerbit" required>
                    </div>
                    <div class="mb-3">
                        <label for="inputTahun" class="form-label">Tahun Terbit</label>
                        <input type="number" min="1900" max="2099" step="1" class="form-control" id="inputTahun" name="tahun" required>
                    </div>
                    <button type="submit" name="addBook" class="btn btn-primary">Submit</button>
                </form>
            </div>
        </div>

        <div class="card my-3">
            <div class="card-header">
                Kembalikan Buku
            </div>
            <div class="card-body">
                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST">
                    <label for="kembalikanISBN">Buku</label>
                    <select class="form-select mb-3" aria-label="Default select example" name="isbn" id="kembalikanISBN" required>
                        <?php
                        foreach ($_SESSION['library']->getBooks() as $book) {
                            if ($book->isBorrowed()) {
                                echo "<option value='" . $book->getISBN() . "'>" . $book->getTitle() . "</option>";
                            }
                        } ?>
                    </select>
                    <button type="submit" name="returnBook" class="btn btn-primary">Submit</button>
                </form>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script>
        $(document).on("click", ".btn-hapus", function() {
            var isbn = $(this).data('isbn');
            $(".modal-body #bookId").val(isbn);
        });
        $(document).on("click", ".btn-pinjam", function() {
            var isbn = $(this).data('isbn');
            $(".modal-body #modalISBN").val(isbn);
        });
    </script>
</body>

</html>