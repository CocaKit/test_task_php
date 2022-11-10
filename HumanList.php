<?php

require_once('Human.php');

if (!class_exists('Human')) {
    exit("Class Human dont exists");
}

/**
 * Класс для работы со списком сущностей леюдей.
 * При создании объекта, формируется массив из id класса Human. 
 * Есть методы для получения и удаления списка объектов по ранее найденым id
 */
class HumanList 
{
    private array $peopleIdList;
    private PDO $databaseConnection;

    public function __construct(string $operator, int $num)
    {
        if (in_array($operator, ['=', '>', '<', '!='])) {
            $this->databaseConnection = new PDO('sqlite:' . __DIR__ . '/linux.sqlite');

            $sql = 'SELECT id FROM People WHERE id ' . $operator . ' :num';
            $task = $this->databaseConnection->prepare($sql);
            $task->execute([':num' => $num]);
            $rows = $task->fetchAll();
            foreach ($rows as $row) {
                $this->peopleIdList[] = $row['Id'];
            }
        } else {
            throw new Exception("Wrong operator");
        }
    }

    public function getPeopleList()
    {
        $peopleList = [];
        foreach ($this->peopleIdList as $humanId) {
            $peopleList[] = new Human($humanId);
        }

        return $peopleList;
    }

    public function deletePeopleList()
    {
        $peopleList = $this->getPeopleList();
        foreach ($peopleList as $human) {
            $human->deleteFromDb();
        }
    }
}

