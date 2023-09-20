<?php
/**
 * @copyright 2023 City of Bloomington, Indiana
 * @license https://www.gnu.org/licenses/agpl.txt GNU/AGPL, see LICENSE
 */
declare (strict_types=1);
namespace Web\Views;

use Google\Service\Calendar\Events;
use Web\View;

class HomeView extends View
{
    public function __construct(Events $events, \DateTime $effectiveDate)
    {
        GLOBAL $LOCATION_MAP;
        parent::__construct();

        $meetings = [];
        foreach ($events as $e) {
            if ($e->start->dateTime) {
                $allDay = false;
                $eventStart = new \DateTime($e->start->dateTime);
                $eventEnd   = new \DateTime($e->end  ->dateTime);
            }
            else {
                $allDay = true;
                $eventStart = new \DateTime($e->start->date);
                $eventEnd   = new \DateTime($e->end  ->date);
            }

            $date     = $eventStart->format('Y-m-d');
            $event_id = $e->id;

            $matches        = [];
            $zoomLink       = '';
            $cancelled      = false;
            if (str_contains(strtolower($e->summary), 'ancel')) {
                $location  = '';
                $cancelled = true;
            }
            else {
                $location = preg_replace(array_keys($LOCATION_MAP), array_values($LOCATION_MAP), $e->location ?? '');
                preg_match('|https://\w+\.zoom\.us/./\d+(\?pwd=\w+)?|',$e->description??"",$matches);
                if  ($matches) {
                    $zoomLink = $matches[0];
                }
            }

            $meetings[$date][$event_id] = [
                'eventId'     => $e->id,
                'summary'     => $e->summary,
                'description' => $e->description,
                'location'    => $location,
                'zoomLink'    => $zoomLink,
                'start'       => $eventStart,
                'end'         => $eventEnd,
                'htmlLink'    => $e->htmlLink,
                'cancelled'   => $cancelled
            ];
        }
        $this->vars = [
            'effectiveDate' => $effectiveDate,
            'events'        => $meetings
        ];
    }

    public function render(): string
    {
        return $this->twig->render("{$this->outputFormat}/index.twig", $this->vars);
    }
}
