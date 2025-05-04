<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CustomerDataResource extends JsonResource
{
    /**
     * The message to be sent
     *
     * @var string
     */
    protected $message;

    /**
     * The last messages collection
     *
     * @var array
     */
    protected $lastMessages;

    /**
     * Create a new resource instance.
     *
     * @param mixed $resource
     * @param array $lastMessages
     * @param string $message
     * @return void
     */
    public function __construct($resource, array $lastMessages, string $message)
    {
        parent::__construct($resource);
        $this->lastMessages = $lastMessages;
        $this->message = $message;
    }

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $messagesOnly = array_map(function($msg) {
            return $msg['message'];
        }, $this->lastMessages);
        return [

              'last_messages' => $messagesOnly,
            'customer_data' => [
                'id' => $this->id,
                'phone' => $this->phone,
                'suffix' => $this->suffix,
                'full_name' => $this->full_name,
                'gender' => $this->gender,
                'is_pregnant' => $this->is_pregnant,
                'is_breastfeeding' => $this->is_breastfeeding,
                'birth_date' => $this->birth_date,
                'vital' => [
                    'weight' => $this->vital->first()->weight ?? null,
                    'height' => $this->vital->first()->height ?? null,
                    'record_date' => $this->vital->first()->record_date ?? null,
                    'temperature' => $this->vital->first()->temperature ?? null,
                    'heart_rate' => $this->vital->first()->heart_rate ?? null,
                    'spo2' => $this->vital->first()->spo2 ?? null,
                ]
            ],
            'send_message' => $this->message,
            'pharmacy_id' => null
        ];
    }
}
