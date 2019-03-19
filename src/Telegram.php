<?php
namespace AliN11\Telegram;

use Ixudra\Curl\CurlService;

class Telegram
{

    protected $token;
    protected $curl;

    const TELEGRAM_BOT_API_URL = 'https://api.telegram.org/bot';
    const TELEGRAM_BOT_API_URL_FILE = 'https://api.telegram.org/file/bot';


    public function __construct($token = null)
    {
        $this->curl = new CurlService();

        if ($token) {
            $this->token = $token;
        }
    }

    public function curl($to)
    {
        return $this->curl->to($to); // ->withProxy('127.0.0.1', '9090', 7);
    }

    public function setToken(string $token)
    {
        $this->token = $token;
    }

    public function getFileInfo($file_id)
    {
        $result = $this->curl(self::TELEGRAM_BOT_API_URL . $this->token . '/getFile?file_id=' . $file_id)
            ->get();

        return json_decode($result);
    }

    public function getFile($file)
    {
        return $this->curl(
            self::TELEGRAM_BOT_API_URL_FILE
            . $this->token
            . '/' . $file->file_path
            . '?file_id=' . $file->file_id
        )->get();
    }

    public function getChatAdministrators($chatId)
    {
        $result = json_decode($this->curl(
            self::TELEGRAM_BOT_API_URL
            . $this->token
            . '/getChatAdministrators?chat_id=' . $chatId
        )->get());

        if (!empty($result) && $result->ok) {
            return $result->result;
        }
    }

    public function isChatAdministrator($chatId)
    {
        // ...
    }
}