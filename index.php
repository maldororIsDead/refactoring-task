<?php

require __DIR__ . '/vendor/autoload.php';

/*
Форматирование кода
Передача в конструктор не класса, а интерфейса для возможной вариативности реализации
Разбить метод statement на отдельные методыпше
*/

class Customer
{
    /* @var string */
    protected $name;

    /* @var array */
    protected $rentals;

    /* @var int */
    protected $totalAmount = 0;

    /* @var int */
    protected $frequentRenterPoints = 0;

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    public function addItem(Amountable $rental): void
    {
        $this->rentals[] = $rental;
    }

    protected function calcCustomerData(): void
    {
        foreach ($this->rentals as $rental) {
            $thisAmount = $rental->getAmount();
            $this->frequentRenterPoints++;

            if ($rental instanceof NewReleaseMovie && $rental->getDaysRented() > 1) {
                $this->frequentRenterPoints++;
            }

            $this->totalAmount += $thisAmount;
        }
    }

    public function getCustomerData(): array
    {
        $this->calcCustomerData();

        $rentals = array_map(function ($rental) {
            return [$rental->getTitle() => $rental->getAmount()];
        }, $this->rentals);

        $data = [
            'name' => $this->name,
            'rentals' => $rentals,
            'totalAmount' => $this->totalAmount,
            'frequentRenterPoints' => $this->frequentRenterPoints
        ];

        return $data;
    }
}

class Statement
{
    public static function getStatement(string $type, array $data)
    {
        $formatData = FormatFactory::create($type);
        return $formatData->getFormattedContent($data);
    }
}

interface MultimediaContentable
{
    public function getTitle(): string;

    public function getDaysRented(): int;
}

class Movie implements MultimediaContentable
{
    /* @var string */
    protected $title;

    /* @var int */
    protected $daysRented;

    /* @var int */
    protected $movieAmount = 0;

    public function __construct(string $title, int $daysRented)
    {
        $this->title = $title;
        $this->daysRented = $daysRented;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getDaysRented(): int
    {
        return $this->daysRented;
    }
}

interface Amountable
{
    public function getAmount(): int;
}

class ChildrenMovie extends Movie implements Amountable
{
    public function getAmount(): int
    {
        $this->movieAmount += 1.5;
        if ($this->daysRented > 3) {
            $this->movieAmount += ($this->daysRented - 3) * 1.5;
        }
        return $this->movieAmount;
    }
}

class RegularMovie extends Movie implements Amountable
{
    public function getAmount(): int
    {
        $this->movieAmount += 2;
        if ($this->daysRented > 2) {
            $this->movieAmount += ($this->daysRented - 2) * 1.5;
        }
        return $this->movieAmount;
    }
}

class NewReleaseMovie extends Movie implements Amountable
{
    public function getAmount(): int
    {
        $this->movieAmount += $this->daysRented * 3;
        return $this->movieAmount;
    }
}

class FormatFactory
{
    public static function create(string $type)
    {
        $product = ucfirst($type) . 'Formatter';

        if (class_exists($product)) {
            return new $product;
        } else {
            throw new Exception("This class isn`t exist");
        }
    }
}

interface Formatable
{
    public function format(array $data): void;

    public function getFormattedContent(array $data): string;
}

class HTMLFormatter implements Formatable
{
    protected $content;

    public function format(array $data): void
    {
        ob_start();
        include('statement.view.html');
        $this->content = ob_get_contents();
        ob_end_clean();
    }

    public function getFormattedContent(array $data): string
    {
        $this->format($data);
        return $this->content;
    }

}

class StringFormatter implements Formatable
{
    protected $content;

    public function format(array $data): void
    {
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $this->format($value);
            } else {
                $this->content .= PHP_EOL . " \t" . $key . ": " . $value . PHP_EOL;
            }
        }
    }

    public function getFormattedContent(array $data): string
    {
        $this->format($data);
        return $this->content;
    }
}

$customer = new Customer('Дима');

$customer->addItem(new ChildrenMovie('Gladiator', 1));
$customer->addItem(new NewReleaseMovie('Spiderman', 2));

$statement = Statement::getStatement('string', $customer->getCustomerData());

echo $statement;

