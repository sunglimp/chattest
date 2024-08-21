<?php

namespace App\Widgets;

use Arrilot\Widgets\AbstractWidget;

class ChatTermination extends AbstractWidget
{
    /**
     * The configuration array.
     *
     * @var array
     */
    protected $config = [];

    /**
     * The number of seconds before each reload.
     *
     * @var int|float
     */
//    public $reloadTimeout = 10;

    /**
     * Treat this method as a controller action.
     * Return view() or other content to display.
     */
    public function run()
    {
        //

        return view('widgets.chat_termination', [
            'config' => $this->config,
        ]);
    }

}
