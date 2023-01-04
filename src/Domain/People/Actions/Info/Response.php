<?php
/**
 * @copyright 2018-2019 City of Bloomington, Indiana
 * @license http://www.gnu.org/licenses/agpl.txt GNU/AGPL, see LICENSE
 */
declare (strict_types=1);
namespace Domain\People\Actions\Info;

use Domain\People\Entities\Person;

class Response
{
    public $person;
    public $errors = [];

    public function __construct(Person $person=null, ?array $errors=null)
    {
        if ($person) { $this->person = $person; }
        if ($errors) { $this->errors = $errors; }
    }
}
