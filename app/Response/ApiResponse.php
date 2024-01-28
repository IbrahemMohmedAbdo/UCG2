<?php

namespace App\Response;
use Illuminate\Support\Facades\Response;
use Illuminate\Http\JsonResponse;

class ApiResponse
{

    private int $status;
    private string $message;
    private array $data;

    function __construct(int $st, string $msg, array $data)
    {
        $this->setStatus( $st);
        $this->setMessage( $msg);
        $this->setData( $data);
        return $this->send();
    }
    public function setStatus(int $status) :void
    {
        $this->status= $status;
    }

    public function setMessage(string $Message): void
    {
        $this->message= $Message;

    }

    public function setData(array $data): void
    {
        $this->data= $data;
    }

    public function send(): JsonResponse
    {
        return Response::json([
            'message'=>$this->message,
            'data'=>$this->data,
        ], $this->status);
    }
}
