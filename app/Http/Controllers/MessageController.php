<?php

namespace App\Http\Controllers;

use App\Http\Resources\CustomerDataResource;
use App\Models\Customer;
use App\Models\Message;
use App\Models\Session;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class MessageController extends BaseController
{
    public function sendMessage(Request $request)
    {

        // Check if the request is from a customer
        $session_id = null;
        if(isset($request->first_message)) {
            $session = Session::create([
                'customer_id' => config('customer.id'),
            ]);
            $session_id = $session->id;
        } else {
            $session_id = $request->input('session_id');
        }

        // Merge the session_id into the request
        $request->merge(['session_id' => $session_id]);

        // Validate the request
        $validator = Validator::make($request->all(), [
            'message' => 'required|string',
            'audio' => 'nullable|file|mimes:mp3,wav',
            'session_id' => 'required|exists:sessions,id',
        ]);

        if ($validator->fails()) {
            return $this->sendResponse(
                false,
                'Validation error',
                $validator->errors()->first(),
                422
            );
        }

        if($request->hasFile('audio')) {
            $audio = $request->file('audio');
            $filename = time() . '_' . $audio->getClientOriginalName();
            Storage::disk('public')->put('audio/' . $filename, file_get_contents($audio));
            $request->merge(['audio' => 'audio/' . $filename]);
            $message = Message::create([
                'message' => $request->input('message'),
                'audio' => $request->input('audio'),
                'type' => $request->input('type', 'sent'),
                'session_id' => $request->input('session_id'),
                'customer_id' => config('customer.id'),
            ]);

            if($message){

                $this->send_collection_to_model($request->input('session_id') , $request->input('message'));
                $speechResponse = $this->convertTextToSpeech($request->input('message').' محمد و عبد الرحمن ');
                $audioUrl = $speechResponse->original['data']['audio_url'] ?? null;
                $messageResponse = $speechResponse->original['data']['text'];
                $audio = $speechResponse->original['data']['audio'];

                 Message::create([
                    'message' => $messageResponse,
                    'type' => 'received',
                    'session_id' => $request->input('session_id'),
                    'customer_id' => config('customer.id'),
                     'audio' => $audio
                ]);
                return $this->sendResponse(
                    true,
                    'the message sent successfully',
                    [
                        'message' => $messageResponse,
                        'audio_url' => $audioUrl,
                        'session_id' => (int)$message->session_id,
                    ],
                    200
                );
            }
        } else {
            $message = Message::create([
                'message' => $request->input('message'),
                'type' => 'sent',
                'session_id' => (int)$request->input('session_id'),
                'customer_id' => config('customer.id'),
            ]);

            if ($message){
               $collection = $this->send_collection_to_model($request->input('session_id') , $request->input('message'));
               Log::info('collection: ' . $collection);
            }
            $messageResponse = $request->input('message');
            $audioUrl = null;
//            $speechResponse  = $this->convertTextToSpeech($request->input('message').' محمد و عبد الرحمن ');
//            $audioUrl = $speechResponse->original['data']['audio_url'] ?? null;
//            $messageResponse = $speechResponse->original['data']['text'];
//            $audio = $speechResponse->original['data']['audio'];
//
//            Message::create([
//                'message' => $messageResponse,
//                'type' => 'received',
//                'session_id' => $request->input('session_id'),
//                'customer_id' => config('customer.id'),
//                'audio' => $audio
//            ]);
            return $this->sendResponse(
                true,
                'the message sent successfully',
                [
                    'message' => $messageResponse,
                    'audio_url' => $audioUrl,
                    'session_id' => (int)$message->session_id,
                ],
                200
            );
        }

        // Default return in case something fails
        return $this->sendResponse(
            false,
            'Failed to send message',
            null,
            500
        );
    }

    public function convertTextToSpeech($text, $voiceId = 'JBFqnCBsd6RMkjVDRZzb', $modelId = 'eleven_multilingual_v2')
    {

        // Make API request to ElevenLabs
        $response = Http::withHeaders([
            'xi-api-key' => config('services.elevenlabs.api_key'),
            'Content-Type' => 'application/json',
            'Accept' => 'audio/mpeg',
        ])->post("https://api.elevenlabs.io/v1/text-to-speech/{$voiceId}", [
            'text' => $text,
            'model_id' => $modelId,
            'voice_settings' => [
                'stability' => 0.5,
                'similarity_boost' => 0.5
            ]
        ]);

        // Check if the request was successful
        if ($response->successful()) {
            // Save the audio file
            $filename = 'speech_' . time() . '.mp3';
            Storage::disk('public')->put('audio/' . $filename, $response->body());

            // Return the URL to the audio file
            return $this->sendResponse(
                true,
                'Text to speech conversion successful',
                [
                    'audio_url' => asset('public/storage/audio/' . $filename),
                    'text' => $text,
                    'audio' => 'audio/' . $filename,
                ],
                200
            );
        } else {
            // Handle the error
            return $this->sendResponse(
                false,
                'Text to speech conversion failed',
                null,
                $response->status()
            );
        }
    }

    public function send_collection_to_model($session_id, $message)
    {
        $customer = Customer::with(['vital' => function($query) {
            $query->latest()->take(1);
        }])->find(config('customer.id'));

        $messages = Message::where('session_id', $session_id)
            ->where('customer_id', config('customer.id'))
            ->where('type', 'sent')
            ->latest()
            ->take(5)
            ->get()
            ->toArray();

        $resource = new CustomerDataResource($customer, $messages, $message);
        $responseData = $resource->toArray(request());

        Log::info('Exporting customer data: ' . json_encode($responseData));

//        $filename = 'customer_data_' . $session_id . '_' . time() . '.json';
//        Storage::disk('public')->put('exports/' . $filename, json_encode($responseData, JSON_PRETTY_PRINT));

        // If I want to send the data to an external API
        // $response = Http::post('https://api-endpoint.com', $responseData);

        return json_encode($responseData);
    }
}
