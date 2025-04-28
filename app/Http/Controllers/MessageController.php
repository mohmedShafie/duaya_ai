<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\Session;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class MessageController extends BaseController
{
    public function sendMessage(Request $request)
    {
        // Check if the request is from a customer
        if(isset($request->first_message)){
            $session = Session::create([
                'session_id' => Str::uuid(),
                'customer_id' => config('customer.id'),
            ]);
        }
        $request->merge(['session_id' => $session->session_id]);
                     // Validate the request
                $validator = Validator::make($request->all(), [
                    'message' => 'required|string|max:255',
                    'audio' => 'nullable|file|mimes:mp3,wav',
                    'type' => 'required|string|in:sent,received',
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
                        'type' => $request->input('type'),
                        'session_id' => $request->input('session_id'),
                        'customer_id' => config('customer.id'),
                    ]);
                    if($message){
                        return response()->json([
                            'message' =>$message->message,
                            'audio_url' => asset('storage/' . $message->audio),
                        ]);
                    }

                }else{
                    return $this->sendResponse(
                        true,
                        'the message sended successfully',
                        ['message' => $request->input('message')],
                        200
                    );
                }
    }

    public function convertTextToSpeech(Request $request)
    {
        // Validate the request
        $request->validate([
            'text' => 'required|string',
            'voice_id' => 'required|string',
        ]);

        $text = $request->input('text');
        $voiceId = $request->input('voice_id', 'JBFqnCBsd6RMkjVDRZzb'); // Default voice ID
        $modelId = $request->input('model_id', 'eleven_multilingual_v2'); // Default model ID

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
                    'audio_url' => asset('storage/audio/' . $filename),
                    'text' => $text,
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
}
