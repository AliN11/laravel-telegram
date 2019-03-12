<?php
namespace AliN11\Telegram;

use Ixudra\Curl\CurlService;

class Request
{

    const TELEGRAM_BOT_API_URL = 'https://api.telegram.org/bot';

    public $rawRequest;

    private $curl;

    private $validRequestTypes = [
        'text',
        'photo',
        'video',
        'animation',
        'document',
        'sticker',
        'voice',
        'audio',
    ];

    public function __construct()
    {
        $this->curl = new CurlService();
    }

    public function getUpdates(string $token = null)
    {
        /* $request = $this->curl
            ->to(self::TELEGRAM_BOT_API_URL . $token . '/getUpdates')
            ->withProxy('127.0.0.1', 8580)
            ->get(); */
        $request = file_get_contents('request.json');
        $this->parseRequest($request);

        print_r($this);

    }

    private function parseRequest($request)
    {
        $request = end(json_decode($request)->result);

        $this->rawRequest = $request;
        $this->type = $this->type($request);
        $this->text = $this->text($request);
        $this->from = $this->from($request);
    }


    public function type($request = null)
    {
        $request = is_null($request) ? $this->rawRequest : $request;

        foreach ($this->validRequestTypes as $type_id => $type) {
            if (isset($request->message)) {
                if (array_key_exists($type, $request->message)) {
                    return $type;
                }
            }
        }

        if ($this->isCallbackQuery($request)) {
            return 'callback_query';
        } else if($this->isInlineQuery($request)) {
            return 'inline_query';
        } else if(isset($request->chosen_inline_result)) {
            return 'chosen_inline_result';
        }
    }


    public function text($request = null)
    {
        $request = is_null($request) ? $this->rawRequest : $request;
        $messageType = $this->type($request);

        if ($messageType == 'text') {
            return $request->message->text;
        } else if (in_array($messageType, $this->validRequestTypes)) {
            if (!empty($request->message->caption)) {
                return $request->message->caption;
            }
        } else if($this->isCallbackQuery($request)) {
            return $request->callback_query->data;
        } else if($this->isInlineQuery($request)) {
            return $request->inline_query->query;
        }
    }

    public function from($request = null)
    {
        $request = is_null($request) ? $this->rawRequest : $request;

        if ($this->isInlineQuery($request)) {
            return $request->inline_query->from;
        } else if ($this->isCallbackQuery($request)) {
            return $request->callback_query->from;
        }

        return $request->message->from;
    }



    private function isInlineQuery($request = null)
    {
        $request = is_null($request) ? $this->rawRequest : $request;

        return isset($request->inline_query);
    }


    private function isCallbackQuery($request = null)
    {
        $request = is_null($request) ? $this->rawRequest : $request;

        return isset($request->callback_query);
    }
}