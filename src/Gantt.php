<?php

namespace MichelMelo\LaravelGantt;

use MichelMelo\LaravelGantt\Calendar\Calendar;

class Gantt
{
    public $cal       = null;
    public $data      = [];
    public $first     = false;
    public $last      = false;
    public $options   = [];
    public $cellstyle = false;
    public $blocks    = [];
    public $months    = [];
    public $days      = [];
    public $seconds   = 0;

    public function __construct($data, $params=[])
    {
        $defaults = [
            'title'      => false,
            'cellwidth'  => 40,
            'cellheight' => 40,
            'today'      => true,
        ];

        $this->options = array_merge($defaults, $params);
        $this->cal     = new Calendar();
        $this->data    = $data;
        $this->seconds = 60 * 60 * 24;

        $this->cellstyle = 'style="width: ' . $this->options['cellwidth'] . 'px; height: ' . $this->options['cellheight'] . 'px"';

        // parse data and find first and last date
        $this->parse();
    }

    public function parse()
    {
        foreach ($this->data as $d) {
            // strtotime($d['start'])

            $this->blocks[] = $d;

            //获取最开始跟最结尾的.
            foreach ($d['date'] as $value) {
                if (! isset($start)) {
                    $start = strtotime($value['start']);
                }
                if (strtotime($value['start']) <= $start) {
                    $start = strtotime($value['start']);
                }

                if (! isset($end)) {
                    $end = strtotime($value['end']);
                }
                if (strtotime($value['end']) >= $end) {
                    $end = strtotime($value['end']);
                }
            }

            if ($start) {
                if (! $this->first || $this->first > $start) {
                    $this->first = $start;
                }
                if (! $this->last || $this->last < $end) {
                    $this->last = $end;
                }
            }
        }

        $this->first = $this->cal->date($this->first);
        $this->last  = $this->cal->date($this->last);

        $current = $this->first->month();
        $lastDay = $this->last->month()->lastDay()->timestamp;

        // build the months
        while ($current->lastDay()->timestamp <= $lastDay) {
            $month          = $current->month();
            $this->months[] = $month;
            foreach ($month->days() as $day) {
                $this->days[] = $day;
            }
            $current = $current->next();
        }
    }

    public function render()
    {
        $html = [];

        // common styles
        $cellstyle  = 'style="line-height: ' . $this->options['cellheight'] . 'px; height: ' . $this->options['cellheight'] . 'px"';
        $wrapstyle  = 'style="width: ' . $this->options['cellwidth'] . 'px"';
        $totalstyle = 'style="width: ' . (count($this->days) * $this->options['cellwidth']) . 'px"';
        // start the diagram
        $html[] = '<figure class="gantt">';

        // set a title if available
        if ($this->options['title']) {
            $html[] = '<figcaption>' . $this->options['title'] . '</figcaption>';
        }

        // sidebar with labels
        $html[] = '<aside>';
        $html[] = '<ul class="gantt-labels" style="margin-top: ' . (($this->options['cellheight'] * 2) + 1) . 'px">';
        foreach ($this->blocks as $i => $block) {
            $html[] = '<li class="gantt-label"><strong ' . $cellstyle . '>' . $block['label'] . '</strong></li>';
        }
        $html[] = '</ul>';
        $html[] = '</aside>';

        // data section
        $html[] = '<section class="gantt-data">';

        // data header section
        $html[] = '<header>';

        // months headers
        $html[] = '<ul class="gantt-months" ' . $totalstyle . '>';
        foreach ($this->months as $month) {
            $html[] = '<li class="gantt-month" style="width: ' . ($this->options['cellwidth'] * $month->countDays()) . 'px"><strong ' . $cellstyle . '>' . $month->name() . '</strong></li>';
        }
        $html[] = '</ul>';

        // days headers
        $html[] = '<ul class="gantt-days" ' . $totalstyle . '>';
        foreach ($this->days as $day) {
            $weekend = ($day->isWeekend()) ? ' weekend' : '';
            $today   = ($day->isToday()) ? ' today' : '';

            $html[] = '<li class="gantt-day' . $weekend . $today . '" ' . $wrapstyle . '><span ' . $cellstyle . '>' . $day->padded() . '</span></li>';
        }
        $html[] = '</ul>';

        // end header
        $html[] = '</header>';

        // main items
        $html[] = '<ul class="gantt-items" ' . $totalstyle . '>';

        foreach ($this->blocks as $i => $block) {
            $html[] = '<li class="gantt-item">';

            // days
            $html[] = '<ul class="gantt-days">';
            foreach ($this->days as $day) {
                $weekend = ($day->isWeekend()) ? ' weekend' : '';
                $today   = ($day->isToday()) ? ' today' : '';

                $html[] = '<li class="gantt-day' . $weekend . $today . '" ' . $wrapstyle . '><span ' . $cellstyle . '>' . $day . '</span></li>';
            }
            $html[] = '</ul>';
            // the block
            foreach ($block['date'] as $value) {
                $days   = ((strtotime($value['end']) - strtotime($value['start'])) / $this->seconds);
                $offset = ((strtotime($value['start']) - $this->first->month()->timestamp) / $this->seconds);
                $top    = round($i * ($this->options['cellheight'] + 1));
                $left   = round($offset * $this->options['cellwidth']);
                $width  = round($days * $this->options['cellwidth'] - 9);
                $height = round($this->options['cellheight'] - 8);
                $class  = ($value['class']) ? ' ' . $value['class'] : '';
                $html[] = '<span class="gantt-block' . $class . '" style="left: ' . $left . 'px; width: ' . $width . 'px; height: ' . $height . 'px"><strong class="gantt-block-label">' . $days . '</strong></span>';
            }
            $html[] = '</li>';
        }

        $html[] = '</ul>';

        if ($this->options['today']) {
            // today
            $today  = $this->cal->today();
            $offset = (($today->timestamp - $this->first->month()->timestamp) / $this->seconds);
            $left   = round($offset * $this->options['cellwidth']) + round(($this->options['cellwidth'] / 2) - 1);

            if ($today->timestamp > $this->first->month()->firstDay()->timestamp && $today->timestamp < $this->last->month()->lastDay()->timestamp) {
                $html[] = '<time style="top: ' . ($this->options['cellheight'] * 2) . 'px; left: ' . $left . 'px" datetime="' . $today->format('Y-m-d') . '">Today</time>';
            }
        }

        // end data section
        $html[] = '</section>';

        // end diagram
        $html[] = '</figure>';

        return implode('', $html);
    }

    public function __toString()
    {
        return $this->render();
    }
}
