<?php

namespace App\Factory\Dtos;

class ConfirmTransactionDto
{
    private $responseBody = '';
    private $status;
    private $remoteCode;
    private $completed;

    public function getResponseBody()
    {
        return $this->responseBody;
    }

    public function setResponseBody($responseBody)
    {
        $this->responseBody = $responseBody;
    }

    public function getStatus()
    {
        return $this->status;
    }

    public function setStatus($status)
    {
        $this->status = $status;
    }

    public function getRemotedCode()
    {
        return $this->remoteCode;
    }

    public function setRemotedCode($remoteCode)
    {
        $this->remoteCode = $remoteCode;
    }

    public function isCompleted()
    {
        return $this->completed;
    }

    public function setCompleted($completed)
    {
        $this->completed = $completed;
    }

}