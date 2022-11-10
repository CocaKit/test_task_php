<?php 

/**
 * Класс для работы с сущность человека
 * При создании объекта класса, создается/берется запись в базе данных. 
 * Есть методы для создания/обновления, удаления записи человека.
 * Есть статитечские методы для форматирования пола и даты рождения.
 * Есть метод для получения объекта StdClass на основе параметров объекта.
 */
class Human 
{
    private int $id;

    private string $firstName;

    private string $lastName;

    private string $birthDate;

    private bool $gender;

    private string $birthTown;

    private PDO $databaseConnection;

    // аргументы при создании объекта: new Human(Id, FirstName, LastName, Array(month, day, year), Gender, Town)
    public function __construct(...$args)
    {
        $this->databaseConnection = new PDO('sqlite:' . __DIR__ .'/linux.sqlite');

        if (count($args) > 0
            && is_int($args[0])
            && ($args[0] >= 0)
        ) {
            $task = $this->databaseConnection->prepare('SELECT * FROM People WHERE Id = :id');
            $task->execute([':id' => $args[0]]);
            $rows = $task->fetchAll();

            if (count($rows) === 0
                && (count($args) === 6)
                && is_string($args[1])
                && (strlen($args[1]) > 0)
                && !preg_match('/[^\p{L}]/', $args[1])
                && is_string($args[2])
                && (strlen($args[2]) > 0)
                && !preg_match('/[^\p{L}]/', $args[2])
                && is_array($args[3])
                && (count($args[3]) === 3)
                && is_int($args[3][0])
                && ($args[3][0] > 0)
                && ($args[3][0] < 13)
                && is_int($args[3][1])
                && ($args[3][1] > 0)
                && ($args[3][1] < 32)
                && is_int($args[3][2])
                && ($args[3][2] > 1900)
                && ($args[3][2] < 2022)
                && is_bool($args[4])
                && is_string($args[5])
                && (strlen($args[5]) > 0)
            ) {
                $this->id = $args[0];
                $this->firstName = $args[1];
                $this->lastName = $args[2];
                $this->birthDate = date('c', mktime(0, 0, 0, $args[3][0], $args[3][1], $args[3][2]));
                $this->gender = $args[4];
                $this->birthTown = $args[5];

                $this->saveVars();
            } elseif (count($rows) > 0) {
                $this->id = $rows[0]['Id'];
                $this->firstName = $rows[0]['FirstName'];
                $this->lastName = $rows[0]['LastName'];
                $this->birthDate = $rows[0]['BirthDate'];
                $this->gender = $rows[0]['Gender'];
                $this->birthTown = $rows[0]['BirthTown'];
            } else {
                throw new Exception('Wrong class arguments');
            }
        } else {
            throw new Exception('Wrong class arguments');
        }
    }

    public function saveVars()
    {
        $sqlUpdate = 'UPDATE People SET FirstName = :firstName, LastName = :lastName, BirthDate = :birthDate, '
             . 'Gender = :gender, BirthTown = :birthTown WHERE id = :id; ';
        $sqlInsert = 'INSERT OR IGNORE INTO People VALUES (:id, :firstName, :lastName, :birthDate, :gender, :birthTown);';
        $varsArr = [':id'        => $this->id, 
                    ':firstName' => $this->firstName, 
                    ':lastName'  => $this->lastName, 
                    ':birthDate' => $this->birthDate, 
                    ':gender'    => $this->gender, 
                    ':birthTown' => $this->birthTown];
        $task = $this->databaseConnection->prepare($sqlUpdate);
        $task->execute($varsArr);
        $task = $this->databaseConnection->prepare($sqlInsert);
        $task->execute($varsArr);
    }

    public function deleteFromDb()
    {
        $sqlDelete = 'DELETE FROM People WHERE id = :id';
        $task = $this->databaseConnection->prepare($sqlDelete);
        $task->execute([':id' => $this->id]);
    }

    public static function birthDateToAges(string $targetDate)
    {
        if (preg_match('/^(\d{4})-(\d{2})-(\d{2})T(\d{2}):(\d{2}):(\d{2})\+(\d{2}):(\d{2})$/', $targetDate)) {
            return substr(date('c'), 0, 4) - substr($targetDate, 0, 4);
        }
        else {
            return false;
        }
    }

    public static function boolGenderToText(bool $targetGender) 
    {
        return $targetGender ? 'жен' : 'муж';
    }

    public function getStdHuman(bool $toAge, bool $toGenderText) 
    {
        $dateKey = $toAge ? 'Age' : 'BirthDate';
        $dateValue = $toAge ? self::birthDateToAges($this->birthDate) : $this->birthDate;

        $genderKey = $toGenderText ? 'GenderText' : 'GenderBool';
        $genderValue = $toGenderText ? self::boolGenderToText($this->gender) : $this->gender;

        return (object) ['Id'        => $this->id, 
                         'FirstName' => $this->firstName, 
                         'LastName'  => $this->lastName, 
                         $dateKey    => $dateValue, 
                         $genderKey  => $genderValue, 
                         'BirthTown' => $this->birthTown];
    }
}