<?php

require __DIR__ . '/vendor/autoload.php';

/*
Форматирование кода
Передача в конструктор не класса, а интерфейса для возможной вариативности реализации
Разбить метод statement на отдельные методыпше
*/

abstract class CustomerFactory
{

    public abstract function getMultimediaType();

    public abstract function getStatements();

}

class Customer extends CustomerFactory
{

    protected $title;
    protected $name;
    protected $rentals;
    protected $rental;
    protected $totalAmount = 0;

    public function __construct($name)
    {
        $this->name = $name;
    }

    public function addItem(RentalTypeFactory $rental) {
        $this->rentals[] = $rental;
    }

    public function calcTotalAmount() {
        $frequentRenterPoints = 0;
        $thisAmount = 0;
        $result = "Rental Record for " . $this->name . "\n";

        foreach ($this->rentals as $rental) {
            $thisAmount = $rental->getAmount();

            $frequentRenterPoints++;
            $frequentRenterPoints += $this->getFrequentRenterPoints($rental);

            $result .= "\t" . $rental->getMovie() . "\t" . $thisAmount . "\n";
            $this->totalAmount +=  $thisAmount;
        }
        $result .= "Amount owed is " . $this->totalAmount . "\n";
        $result .= "You earned " . $frequentRenterPoints . " frequent renter points";

        return $result;
    }

    public function getMultimediaType()
    {
        return new Movie($this->title);
    }

    public function getStatements()
    {
        return new toHTMLStatement;
    }

    public function getFrequentRenterPoints($rental)
    {
        return ($rental == 'NewReleaseRental') && ($rental->getDaysRented() > 1) ? 2 : 1;
    }

}

abstract class MultimediaContentFactory
{

    public abstract function getTitle();

}

class Movie extends MultimediaContentFactory
{

    private $title;

    public function __construct($title)
    {
        $this->title = $title;
    }

    public function getTitle()
    {
        return $this->title;
    }
}

abstract class RentalTypeFactory
{

    abstract public function getAmount();
    abstract public function getMovie();
    abstract public function getDaysRented();

}

class ChildrenRental extends RentalTypeFactory
{
    private $movie;
    private $daysRented;
    private $movieAmount = 0;

    public function __construct($movie, $daysRented)
    {
        $this->movie = $movie;
        $this->daysRented = $daysRented;
    }

    public function getMovie() {
        return $this->movie;
    }

    public function getDaysRented() {
        return $this->daysRented;
    }

    public function getAmount()
    {
        $this->movieAmount += 1.5;
        if ($this->daysRented > 3) {
            $this->movieAmount += ($this->daysRented - 3) * 1.5;
        }
        return $this->movieAmount;
    }
}

class RegularRental extends RentalTypeFactory
{

    private $daysRented;
    protected $movieAmount = 0;
    protected $movie;

    public function __construct($movie, $daysRented)
    {
        $this->movie = $movie;
        $this->daysRented = $daysRented;
    }

    public function getMovie() {
        return $this->movie;
    }

    public function getDaysRented() {
        return $this->daysRented;
    }

    public function getAmount()
    {
        $this->movieAmount += 2;
        if ($this->daysRented > 2) {
            $this->movieAmount += ($this->daysRented - 2) * 1.5;
        }
        return $this->movieAmount;
    }
}

class NewReleaseRental extends RentalTypeFactory
{

    private $daysRented;
    protected $movieAmount = 0;
    protected $movie;

    public function __construct($movie, $daysRented)
    {
        $this->movie = $movie;
        $this->daysRented = $daysRented;
    }

    public function getMovie() {
        return $this->movie;
    }

    public function getDaysRented() {
        return $this->daysRented;
    }

    public function getAmount()
    {
        $this->movieAmount += $this->daysRented * 3;
        return $this->movieAmount;
    }
}

abstract class StatementFactory
{

}

class toHTMLStatement extends StatementFactory
{

}

class toStringStatement extends StatementFactory
{

}

$customer = new Customer('Дима');
$customer->addItem(new RegularRental('Gladiator', 1));
$customer->getMultimediaType();
$customer->addItem(new NewReleaseRental('Spiderman', 2));
$movie = $customer->calcTotalAmount();

var_dump($movie);

