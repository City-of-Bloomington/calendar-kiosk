<?php
/**
 * @copyright 2017-2023 City of Bloomington, Indiana
 * @license http://www.gnu.org/licenses/agpl.txt GNU/AGPL, see LICENSE
 */
namespace Web;

class GoogleGateway
{
    private static function getClient()
    {
        static $client = null;

        if (!$client) {
            $client = new \Google_Client();
            $client->setAuthConfig(GOOGLE_CREDENTIALS_FILE);
            $client->setScopes([\Google_Service_Calendar::CALENDAR_READONLY]);
            $client->setSubject(GOOGLE_USER_EMAIL);
        }
        return $client;
    }

    /**
     * @see https://developers.google.com/calendar/v3/reference/calendars/get
     * @throws Google_Service_Exception
     */
    public static function calendar(string $calendar_id): \Google_Service_Calendar_Calendar
    {
        $service = new \Google_Service_Calendar(self::getClient());
        return $service->calendars->get($calendar_id);
    }

    /**
     * @see https://developers.google.com/calendar/v3/reference/events/get
     * @throws Google_Service_Exception
     */
    public static function event(string $calendar_id, string $event_id): \Google_Service_Calendar_Event
    {
        $service = new \Google_Service_Calendar(self::getClient());
        return $service->events->get($calendar_id, $event_id);
    }

    /**
     * @see https://developers.google.com/google-apps/calendar/v3/reference/events/list
     * @param  string   $calendarId
     * @param  DateTime $start
     * @param  DateTime $end
     * @param  boolean  $singleEvents
     * @param  int      $maxResults
     * @return Google_Service_Calendar_EventList
     */
    public static function events($calendarId, \DateTime $start=null, \DateTime $end=null, $singleEvents=true, ?int $maxResults=null)
    {
        $FIELDS = 'description,end,endTimeUnspecified,htmlLink,id,location,'
                . 'originalStartTime,recurrence,recurringEventId,sequence,'
                . 'start,summary,attendees,organizer';

        $opts = [
            'fields'       => "items($FIELDS)",
            'singleEvents' => $singleEvents,
            'maxResults'   => $maxResults
        ];
        if ($singleEvents) { $opts['orderBy'] = 'startTime'; }

        if ($start) { $opts['timeMin'] = $start->format(\DateTime::RFC3339); }
        if ($end  ) { $opts['timeMax'] = $end  ->format(\DateTime::RFC3339); }

        $service = new \Google_Service_Calendar(self::getClient());
        $events  = $service->events->listEvents($calendarId, $opts);
        return $events;
    }

    public static function limitEvents(\Google_Service_Calendar_Events $events, int $maxevents): array
    {
        $display = [];
        $count   = 0;
        foreach ($events as $e) {
            if (++$count > $maxevents) { break; }

            $display[] = $e;
        }
        return $display;
    }
}
