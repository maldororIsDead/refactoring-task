<?php

require __DIR__ . '/vendor/autoload.php';

/*
Форматирование кода
Передача в конструктор не класса, а интерфейса для возможной вариативности реализации
Разбить метод statement на отдельные методыпше
*/

abstract class CustomerFactory
{

    public abstract function getMultimediaType(): MultimediaContentFactory;

    public abstract function getStatements(): FormatterFactory;

}

class Customer extends CustomerFactory
{

    /* @var string */
    protected $name;

    /* @var array */
    protected $rentals;

    /* @var Object */
    protected $rental;

    /* @var int */
    protected $totalAmount = 0;

    /* @var array */
    protected $statement = [];

    public function __construct($name)
    {
        $this->name = $name;
    }

    public function addItem(RentalTypeFactory $rental): void
    {
        $this->rentals[] = $rental;
    }

    protected function getStatementData(): array
    {
        return $this->statement;
    }

    public function calcTotalAmount(): void
    {
        $frequentRenterPoints = 0;
        $this->statement['header'] = "Rental Record for " . $this->name . "\n";

        foreach ($this->rentals as $rental) {
            $thisAmount = $rental->getAmount();

            $frequentRenterPoints++;
            if (get_class($rental) === 'NewReleaseRental' && $rental->getDaysRented() > 1) {
                $frequentRenterPoints++;
            }

            $this->statement['body'][$rental->getMovie()] = $thisAmount;
            $this->totalAmount += $thisAmount;
        }

        $this->statement['footer'][] = "Amount owed is " . $this->totalAmount . "\n";
        $this->statement['footer'][] = "You earned " . $frequentRenterPoints . " frequent renter points";
    }

    public function getMultimediaType(): MultimediaContentFactory
    {
        return new Movie($this->title);
    }

    public function getStatements(): FormatterFactory
    {
        return new StringFormatter($this->statement);
    }
}

abstract class MultimediaContentFactory
{

    public abstract function getTitle(): string;

}

class Movie extends MultimediaContentFactory
{
    /* @var string */
    private $title;

    public function __construct($title)
    {
        $this->title = $title;
    }

    public function getTitle(): string
    {
        return $this->title;
    }
}

abstract class RentalTypeFactory
{

    abstract public function getAmount(): int;

    abstract public function getMovie(): string;

    abstract public function getDaysRented(): int;

}

class ChildrenRental extends RentalTypeFactory
{
    /* @var string */
    private $movie;

    /* @var int */
    private $daysRented;

    /* @var int */
    private $movieAmount = 0;

    public function __construct($movie, $daysRented)
    {
        $this->movie = $movie;
        $this->daysRented = $daysRented;
    }

    public function getMovie(): string
    {
        return $this->movie;
    }

    public function getDaysRented(): int
    {
        return $this->daysRented;
    }

    public function getAmount(): int
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

    /* @var string */
    private $movie;

    /* @var int */
    private $daysRented;

    /* @var int */
    private $movieAmount = 0;

    public function __construct($movie, $daysRented)
    {
        $this->movie = $movie;
        $this->daysRented = $daysRented;
    }

    public function getMovie(): string
    {
        return $this->movie;
    }

    public function getDaysRented(): int
    {
        return $this->daysRented;
    }

    public function getAmount(): int
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

    /* @var string */
    private $movie;

    /* @var int */
    private $daysRented;

    /* @var int */
    private $movieAmount = 0;

    public function __construct($movie, $daysRented)
    {
        $this->movie = $movie;
        $this->daysRented = $daysRented;
    }

    public function getMovie(): string
    {
        return $this->movie;
    }

    public function getDaysRented(): int
    {
        return $this->daysRented;
    }

    public function getAmount(): int
    {
        $this->movieAmount += $this->daysRented * 3;
        return $this->movieAmount;
    }
}

abstract class FormatterFactory
{
    abstract public function format();
}

class Htmlable extends FormatterFactory
{
    /* @var array */
    protected $statement;

    public function __construct($statement)
    {
        $this->statement = $statement;
    }

    public function format(): string
    {
        $result = '<h1>' . $this->statement['header'] . '</h1>' . "\n";

        foreach ($this->statement['body'] as $title => $body) {
            $result .= '<p>' . $title . "\t" . $body . '</p>' . "\n";
        }

        $result .= '<p>' . $this->statement['footer'] . '</p>' . "\n";
        $result .= '<p>' . $this->statement['footer'] . '</p>';

        return $result;
    }

}

class StringFormatter extends FormatterFactory
{
    /* @var array */
    protected $statement;

    public function __construct($statement)
    {
        $this->statement = $statement;
    }

    public function format()
    {
        $result = '';

        foreach ($this->statement['header'] as $header) {
            $result .= $header . "\n";
        }

        foreach ($this->statement['body'] as $title => $amount) {
            $result .= $title . "\t" . $amount . "\n";
        }

        foreach ($this->statement['footer'] as $footer) {
            $result .= $footer . "\n";
        }

        return $result;
    }
}

$customer = new Customer('Дима');
$customer->addItem(new ChildrenRental('Gladiator', 1));
$customer->getMultimediaType();

$customer->addItem(new NewReleaseRental('Spiderman', 2));
$customer->calcTotalAmount();

var_dump($customer->getStatements()->format());

