<?php
namespace AliN11\Telegram;


class keyboard
{

    private $data;
    private $resizeable = true;
    private $oneTimeKeyboard = true;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function get(): array
    {
        return [
            'keyboard' => $this->data,
            'resize_keyboard' => true,
            'one_time_keyboard' => true
        ];
    }

}