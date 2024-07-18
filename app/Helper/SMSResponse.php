<?php

namespace App\Helper;

class SMSResponse
{
    private $status, $replyMessage, $requestId, $requestTime, $data;

    /**
     * @param $status
     * @param $replyMessage
     * @param $requestId
     * @param $requestTime
     * @param $data
     */
    public function __construct($status, $replyMessage, $requestId, $requestTime, $data)
    {
        $this->status = $status;
        $this->replyMessage = $replyMessage;
        $this->requestId = $requestId;
        $this->requestTime = $requestTime;
        $this->data = $data;
    }

    /**
     * @return mixed
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param mixed $status
     */
    public function setStatus($status): void
    {
        $this->status = $status;
    }

    /**
     * @return mixed
     */
    public function getReplyMessage()
    {
        return $this->replyMessage;
    }

    /**
     * @param mixed $replyMessage
     */
    public function setReplyMessage($replyMessage): void
    {
        $this->replyMessage = $replyMessage;
    }

    /**
     * @return mixed
     */
    public function getRequestId()
    {
        return $this->requestId;
    }

    /**
     * @param mixed $requestId
     */
    public function setRequestId($requestId): void
    {
        $this->requestId = $requestId;
    }

    /**
     * @return mixed
     */
    public function getRequestTime()
    {
        return $this->requestTime;
    }

    /**
     * @param mixed $requestTime
     */
    public function setRequestTime($requestTime): void
    {
        $this->requestTime = $requestTime;
    }

    /**
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param mixed $data
     */
    public function setData($data): void
    {
        $this->data = $data;
    }
}
