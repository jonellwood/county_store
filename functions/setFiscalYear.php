<?php
class MyDateTime extends DateTime
{
    public function fiscalYear()
    {
        $result = array();
        $start = new DateTime();
        $start->setTime(0, 0, 0);
        $end = new DateTime();
        $end->setTime(23, 59, 59);
        $year = $this->format('Y');
        $start->setDate($year, 7, 1);
        if ($start <= $this) {
            $end->setDate($year + 1, 3, 31);
        } else {
            $start->setDate($year - 1, 7, 1);
            $end->setDate($year, 6, 30);
        }
        $result['start'] = $start->getTimestamp();
        $result['end'] = $end->getTimestamp();
        return $result;
    }
}

$mydate = new MyDateTime(); // will use the current date time
$year = $mydate->format('Y'); // to get the current year and
$mydate->setDate($year, 6, 30); // pass into here to set the values to apply
$result = $mydate->fiscalYear(); // the fiscalYear method too

$fystart = $result['start'];
$fyend = $result['end'];

// formatted my MySQL - not sure of any changes that need to be made for other db's
$db_fystart = date(DATE_RFC3339, $fystart);
$db_fyend = date(DATE_RFC3339, $fyend);

// Sample query for use of values - syntax is probably goofy but you get the idea.
//$sql = "SELECT total(pull_requests) FROM pull_requests WHERE pull_requests.created BETWEEN '$db_fystart' AND '$db_fyend'";