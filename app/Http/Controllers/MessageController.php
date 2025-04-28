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
        $session_id = null;
        if(isset($request->first_message)) {
            $session = Session::create([
                'session_id' => Str::uuid(),
                'customer_id' => config('customer.id'),
            ]);
            $session_id = $session->session_id;
        } else {
            // If it's not a first message, session_id should be provided in the request
            $session_id = $request->input('session_id');
        }

        // Merge the session_id into the request
        $request->merge(['session_id' => $session_id]);

        // Validate the request
        $validator = Validator::make($request->all(), [
            'message' => 'required|string|max:255',
            'audio' => 'nullable|file|mimes:mp3,wav',
            'session_id' => 'required|exists:sessions,session_id',
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
                'type' => $request->input('type', 'sent'), // Added default 'sent' type
                'session_id' => $request->input('session_id'),
                'customer_id' => config('customer.id'),
            ]);

            if($message){
                return $this->sendResponse(
                    true,
                    'the message sent successfully',
                    [
                        'message' => $message->message,
                        'audio_url' => asset('storage/' . $message->audio),
                        'session_id' => $message->session_id,
                    ],
                    200
                );
            }
        } else {
            $message = Message::create([
                'message' => $request->input('message'),
                'type' => 'sent',
                'session_id' => $request->input('session_id'),
                'customer_id' => config('customer.id'),
            ]);

            return $this->sendResponse(
                true,
                'the message sent successfully',
                [
                    'message' => $request->input('message'),
                    'audio_url' => null,
                    'session_id' => $message->session_id,
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
