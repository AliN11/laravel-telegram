<?php
namespace AliN11\Telegram;

use Ixudra\Curl\CurlService;

class Request extends Telegram
{
    public $messageBody;
    public $rawRequest;

    private $validRequestTypes = [
        'text',
        'photo',
        'video',
        'animation',
        'document',
        'sticker',
        'voice',
        'audio',
        'inline_query',
        'callback_query',
        'chosen_inline_result'
    ];

    public function getUpdates()
    {
        $request = $this->curl(self::TELEGRAM_BOT_API_URL . $this->token . '/getUpdates')->get();

        $this->parseRequest($request);

        return $this;
    }

    private function parseRequest($request)
    {
        $request = end(json_decode($request)->result);

        $this->rawRequest  = $request;
        $this->messageBody = $this->messageBody();
        $this->type        = $this->type();
        $this->text        = $this->text();
        $this->from        = $this->from();
    }


    public function messageBody()
    {
        $request = $this->rawRequest;

        if ($this->isChannelPost()) {
            return $request->channel_post;
        } else if($this->isCallbackQuery()) {
            return $request->callback_query;
        } else if($this->isInlineQuery()) {
            return $request->inline_query;
        } else {
            return $request->message;
        }
    }

    public function type()
    {
        foreach ($this->validRequestTypes as $type) {
            if (isset($this->messageBody->{$type}) || isset($this->rawRequest->{$type})) {
                return $type;
            }
        }
    }


    public function text($request = null)
    {
        $messageBody = $this->messageBody;
        $text = '';

        if (isset($messageBody->text)) {
            $text = $messageBody->text;
        } else if (isset($messageBody->caption)) {
            $text = $messageBody->caption;
        } else if($this->isCallbackQuery()) {
            $text = $messageBody->data;
        } else if($this->isInlineQuery()) {
            $text = $messageBody->query;
        }

        return $this->validateText($text);
    }

    public function from()
    {
        return isset($this->messageBody->chat)
            ? $this->messageBody->chat
            : $this->messageBody->from;
    }

    public function isInlineQuery()
    {
        return isset($this->rawRequest->inline_query);
    }

    public function isCallbackQuery()
    {
        return isset($this->rawRequest->callback_query);
    }

    public function isChannelPost()
    {
        return isset($this->rawRequest->channel_post);
    }

    public function photos()
    {
        if ($this->type == 'photo') {
            return $this->messageBody->photo;
        }
    }

    public function isPrivateMessage()
    {
        return $this->from->type === 'private';
    }

    public function validateText($text)
    {
        if (substr($text, 0, 1) == '/') {
            $text = substr($text, 1);
        }
        if (substr($text, 0, 6) == 'start ') {
            $text = substr($text, 6);
        }
        // return convert_numbers($text);
        return $text;
    }

    public function isForwardedMessage()
    {
        return !empty($this->messageBody->forward_from_chat);
    }

    public function forwardedMessageInfo()
    {
        return $this->isForwardedMessage() ? $this->messageBody->forward_from_chat : null;
    }
}