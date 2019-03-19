<?php
namespace AliN11\Telegram;

use CURLFile;
use Ixudra\Curl\CurlService;
use AliN11\Telegram\Keyboard;

class Response extends Telegram
{


    const TELEGRAM_BOT_API_URL = 'https://api.telegram.org/bot';

    /**
     *  Web preview availability for Telegram chat
     *
     * @var boolean
     */
    public $webPreview = true;

    /**
     * Message notifiocation
     *
     * @var boolean
     */
    public $notify = true;

    /**
     * Message parse mode. HTML|Markdown
     *
     * @var string
     */
    public $parseMode = 'HTML';

    /**
     * Text message value.
     * @see https://core.telegram.org/bots/api#sendmessage
     *
     * @var string
     */
    public $text = '';

    /**
     * Caption for media messages
     * @see https://core.telegram.org/bots/api#sendphoto
     *
     * @var string
     */
    public $caption = '';

    /**
     * Telegram bot token
     *
     * @var string
     */
    protected $bot = null;

    /**
     * User chat id (Message receiver)
     *
     * @var integer
     */
    protected $chatId = null;

    /**
     * Path to a local file to send media to the user
     *
     * @var string
     */
    protected $localFile = null;

    /**
     * Patht to a remote file to send media to the user
     *
     * @var string
     */
    protected $remoteFile = null;

    protected $replyMarkup = null;


    /**
     * List of basic methods. Organized here to reduce duplication
     * @see https://core.telegram.org/bots/api
     *
     * @var array
     */
    private $requestMethods = [
        'sendMessage'  => ['parameter' => ''],
        'sendPhoto'    => ['parameter' => 'photo'],
        'sendAudio'    => ['parameter' => 'audio'],
        'sendDocument' => ['parameter' => 'document'],
        'SendVideo'    => ['parameter' => 'video'],
        'SendVoice'    => ['parameter' => 'voice'],
    ];


    /**
     * to be completed...
     *
     * @param string $token
     */
    public function __construct(string $token = null)
    {
        if ($token) {
            $this->token = $token;
        }

        $this->curl = new CurlService();
    }


    /**
     * Some basic request methods are similar to each other.
     * To reduce code duplication we use magic methods
     *
     * @param string $name
     * @param mixed $arguments
     * @return mixed
     */
    public function __call($name, $arguments)
    {
        if (array_key_exists($name, $this->requestMethods)) {
            return $this->sendResponse(
                $name,
                $this->requestMethods[$name]['parameter']
            );
        }
    }


    /**
     * @see __call()
     * Completes __call() method
     *
     * @param string $method
     * @param string $parameter
     * @return mixed
     */
    private function sendResponse(string $method, string $parameter)
    {
        $result = $this->curl($this->basePath() . '/' . $method);

        if ($this->localFile) {
            $this->photo = null;
            $result = $result->withFile($parameter, realpath($this->localFile));
        }

        return $result->withData($this->dataProvider())->post();
    }


    /**
     * Prepare and return Telegram bot api url
     *
     * @return string
     */
    private function basePath(): string
    {
        return self::TELEGRAM_BOT_API_URL . $this->token;
    }

    /**
     * Set parse mode
     *
     * @param string $mode
     * @return Response
     */
    public function parseMode(string $mode): Response
    {
        $this->parseMode = $mode;
        return $this;
    }


    /**
     * Set notification type
     *
     * @return Response
     */
    public function doNotNotify(): Response
    {
        $this->notify = false;
        return $this;
    }

    /**
     * Disable Web Preview
     *
     * @return Response
     */
    public function withoutWebPreview(): Response
    {
        $this->webPreview = false;
        return $this;
    }


    /**
     * Set text for sendMessage method
     *
     * @param string $text
     * @return Response
     */
    public function text(string $text): Response
    {
        $this->text = $text;
        return $this;
    }

    /**
     * Set caption for media messages
     *
     * @param string $caption
     * @return Response
     */
    public function caption(string $caption): Response
    {
        $this->caption = $caption;
        return $this;
    }


    /**
     * Set bot token
     *
     * @param string $token
     * @return void
     */
    public function setToken(string $token)
    {
        $this->bot = $token;
    }


    /**
     * Specify chat id
     *
     * @param int $chat_id
     * @return Response
     */
    public function to(int $chat_id): Response
    {
        $this->chatId = $chat_id;

        return $this;
    }

    /**
     * Get chat id
     *
     * @return int
     */
    private function chatId(): int
    {
        return $this->chatId;
    }


    /**
     * Attach media file from remote URL to send to the user
     *
     * @param string $url
     * @return Response
     */
    public function mediaFromUrl(string $url): Response
    {
        $this->remoteFile = $url;

        return $this;
    }


    /**
     * Attach media file from local to send to the user
     *
     * @param string $path
     * @return Response
     */
    public function mediaFromLocal(string $path)
    {
        $this->localFile = $path;

        return $this;
    }


    /**
     * Provide data which is common in all requests
     *
     * @return array
     */
    private function dataProvider(): array
    {
        $data = [
            'chat_id' => $this->chatId(),
            'text' => $this->text,
            'caption' => $this->caption,
            'disable_web_page_preview' => !$this->webPreview,
            'disable_notification' => !$this->notify,
            'parse_mode' => $this->parseMode
        ];

        if ($this->replyMarkup) {
            $data['reply_markup'] = json_encode($this->replyMarkup);
        }

        print_r($data);
        return $data;
    }

    public function keyboard(Keyboard $keyboard): Response
    {
        $this->replyMarkup = $keyboard->get();

        return $this;
    }
}