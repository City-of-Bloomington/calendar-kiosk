<?php
/**
 * @copyright 2023 City of Bloomington, Indiana
 * @license http://www.gnu.org/licenses/agpl.txt GNU/AGPL, see LICENSE
 */
declare (strict_types=1);

namespace Web;

use Google\Service\Calendar\Events;

class HomeController extends Controller
{
    public function __invoke(array $params): View
    {
        $start  = new \DateTime('midnight');
        $end    = new \DateTime('tomorrow');
        $events = GoogleGateway::events(GOOGLE_CALENDAR_ID, $start, $end);

        return new Views\HomeView($events);
    }
}
