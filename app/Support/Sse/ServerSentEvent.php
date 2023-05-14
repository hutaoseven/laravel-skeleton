<?php

declare(strict_types=1);

namespace App\Support\Sse;

/**
 * @see https://developer.mozilla.org/zh-CN/docs/Web/API/Server-sent_events/Using_server-sent_events
 */
class ServerSentEvent implements \Stringable
{
    /**
     * @var array<string, string>
     */
    public const HEADERS = [
        'Content-Type' => 'text/event-stream',
        'Connection' => 'keep-alive',
        'Cache-Control' => 'no-cache, no-store, must-revalidate, pre-check=0, post-check=0',
        'X-Accel-Buffering' => 'no',
    ];

    /**
     * @var callable|null
     */
    private $tapper;

    private bool $headersSent = false;

    public function __construct(
        ?callable $tapper = null,
        private string|\Stringable|null $event = null,
        private string|array|\Stringable|null $data = null,
        private string|\Stringable|null $id = null,
        private string|\Stringable|null $comment = null,
        private ?int $retry = 3000,
        private int $sleep = 3,
    ) {
        $this->setTapper($tapper);
        $this->setData($data);
    }

    public function setTapper(?callable $tapper): self
    {
        $this->tapper = static function (self $serverSentEvent) use ($tapper): self {
            if (\is_callable($tapper)) {
                $tapper($serverSentEvent);
            }

            return $serverSentEvent;
        };

        return $this;
    }

    public function setEvent(\Stringable|string|null $event): self
    {
        $this->event = $event;

        return $this;
    }

    public function setData(\Stringable|array|string|null $data, $options = JSON_THROW_ON_ERROR): self
    {
        if (\is_array($data)) {
            $data = json_encode($data, $options);
        }

        $this->data = $data;

        return $this;
    }

    public function setId(\Stringable|string|null $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function setComment(\Stringable|string|null $comment): self
    {
        $this->comment = $comment;

        return $this;
    }

    public function setRetry(?int $retry): self
    {
        $this->retry = $retry;

        return $this;
    }

    public function setSleep(int $sleep): self
    {
        $this->sleep = $sleep;

        return $this;
    }

    public function sendHeaders(): self
    {
        if ($this->headersSent) {
            return $this;
        }

        $this->headersSent = true;

        // headers have already been sent by the developer
        if (headers_sent()) {
            return $this;
        }

        // headers
        foreach (self::HEADERS as $name => $value) {
            header($name.': '.$value, 0 === strcasecmp($name, 'Content-Type'));
        }

        return $this;
    }

    public function sendContent(): self
    {
        echo($this->tapper)($this);

        return $this;
    }

    public function send(): self
    {
        $this->sendHeaders();
        $this->sendContent();

        return $this;
    }

    public function __toString(): string
    {
        $event = [];
        if ($this->event !== null) {
            $event[] = "event: $this->event";
        }
        if ($this->data !== null) {
            $event[] = "data: $this->data";
        }
        if ($this->id !== null) {
            $event[] = "id: $this->id";
        }
        if ($this->comment !== null) {
            $event[] = ": $this->comment";
        }
        if ($this->retry !== null) {
            $event[] = "retry: $this->retry";
        }

        return implode(PHP_EOL, $event).PHP_EOL.PHP_EOL;
    }

    public function __invoke()
    {
        // Event loop.
        while (true) {
            try {
                // Echo server sent event.
                $this->send();
            } catch (CloseServerSentEventException $e) {
                // $e->serverSentEvent?->send();
                $e->serverSentEvent?->sendContent();

                return;
            } finally {
                // Flush the output buffer and send echoed messages to the browser.
                if (ob_get_level() > 0) {
                    ob_flush();
                }
                flush();

                // Break the loop if the client aborted the connection.
                if (connection_aborted()) {
                    return;
                }

                // Sleep seconds before running the loop again.
                sleep($this->sleep);
            }
        }
    }
}
