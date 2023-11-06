<?php
namespace Marion\Traits;

trait ApiResponse{


    function response($data,int $status_code){
        header('Content-Type: application/json; charset=utf-8');
        http_response_code($status_code);
        echo json_encode([
            'data' => $data,
            'code' => $status_code
        ]);
        exit;
    }

    function successResponse($data){
        $this->response($data,200);
    }
}