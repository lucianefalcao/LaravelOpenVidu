<?php

namespace SquareetLabs\LaravelOpenVidu\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Response;
use SquareetLabs\LaravelOpenVidu\Builders\PublishStreamBuilder;
use SquareetLabs\LaravelOpenVidu\Builders\RecordingPropertiesBuilder;
use SquareetLabs\LaravelOpenVidu\Builders\SessionPropertiesBuilder;
use SquareetLabs\LaravelOpenVidu\Builders\SignalPropertiesBuilder;
use SquareetLabs\LaravelOpenVidu\Builders\TokenOptionsBuilder;
use SquareetLabs\LaravelOpenVidu\Dispatchers\WebhookEventDispatcher;
use SquareetLabs\LaravelOpenVidu\Exceptions\OpenViduConnectionNotFoundException;
use SquareetLabs\LaravelOpenVidu\Exceptions\OpenViduException;
use SquareetLabs\LaravelOpenVidu\Exceptions\OpenViduInvalidArgumentException;
use SquareetLabs\LaravelOpenVidu\Exceptions\OpenViduSessionNotFoundException;
use SquareetLabs\LaravelOpenVidu\Exceptions\OpenViduStreamTypeInvalidException;
use SquareetLabs\LaravelOpenVidu\Facades\OpenVidu;
use SquareetLabs\LaravelOpenVidu\Http\Requests\GenerateTokenRequest;
use SquareetLabs\LaravelOpenVidu\Http\Requests\PublishStreamRequest;
use SquareetLabs\LaravelOpenVidu\Http\Requests\SignalRequest;
use SquareetLabs\LaravelOpenVidu\Http\Requests\StartRecordingRequest;
use SquareetLabs\LaravelOpenVidu\Http\Requests\WebhookEventRequest;
use SquareetLabs\LaravelOpenVidu\SignalProperties;

/**
 * Class SmsUpReportController
 * @package SquareetLabs\LaravelOpenVidu\Http\Controllers
 */
class OpenViduController extends Controller
{
    /**
     * @param  GenerateTokenRequest  $request
     * @return string
     * @throws OpenViduException
     */
    public function token(GenerateTokenRequest $request)
    {
        $session = OpenVidu::createSession(SessionPropertiesBuilder::build($request->get('session')));
        $token = $session->generateToken(TokenOptionsBuilder::build($request->get('tokenOptions')));
        return Response::json(['token' => $token], 200);
    }

    /**
     * @param  string  $sessionId
     * @return string
     */
    public function session(string $sessionId)
    {
        $session = OpenVidu::getSession($sessionId);
        return Response::json(['session' => $session], 200);
    }

    /**
     * @return string
     */
    public function sessions()
    {
        $sessions = OpenVidu::getActiveSessions();
        return Response::json(['sessions' => $sessions], 200);
    }

    /**
     * @param  string  $sessionId
     * @return string
     */
    public function connections(string $sessionId)
    {
        $session = OpenVidu::getSession($sessionId);
        $connections = $session->getActiveConnections();
        return Response::json(['connections' => $connections], 200);
    }

    /**
     * @param  string  $sessionId
     * @return string
     * @throws OpenViduException
     */
    public function close(string $sessionId)
    {
        $session = OpenVidu::getSession($sessionId);
        $closed = $session->close();
        return Response::json(['closed' => $closed], 200);
    }


    /**
     * @param  string  $sessionId
     * @return string
     */
    public function fetch(string $sessionId)
    {
        $session = OpenVidu::getSession($sessionId);
        $hasChanges = $session->fetch();
        return Response::json(['session' => $session, 'hasChanges' => $hasChanges], 200);
    }


    /**
     * @param  string  $sessionId
     * @return string
     */
    public function isBeingRecording(string $sessionId)
    {
        $session = OpenVidu::getSession($sessionId);
        $isBeingRecording = $session->isBeingRecorded();
        return Response::json(['isBeingRecording' => $isBeingRecording], 200);
    }


    /**
     * @param  string  $sessionId
     * @param  PublishStreamRequest  $request
     * @return string
     * @throws OpenViduException
     * @throws OpenViduSessionNotFoundException
     * @throws OpenViduStreamTypeInvalidException
     */
    public function publish(string $sessionId, PublishStreamRequest $request)
    {
        $session = OpenVidu::getSession($sessionId);
        $connection = $session->publish(PublishStreamBuilder::build($request->all()));
        return Response::json(['connection' => $connection], 200);
    }


    /**
     * @param  string  $sessionId
     * @param  string  $streamId
     * @return string
     * @throws OpenViduConnectionNotFoundException
     * @throws OpenViduException
     * @throws OpenViduSessionNotFoundException
     */
    public function forceUnpublish(string $sessionId, string $streamId)
    {
        $session = OpenVidu::getSession($sessionId);
        $session->forceUnpublish($streamId);
        return Response::json(['unpublished' => true], 200);
    }

    /**
     * @param  string  $sessionId
     * @param  string  $connectionId
     * @return string
     * @throws OpenViduException
     */
    public function forceDisconnect(string $sessionId, string $connectionId)
    {
        $session = OpenVidu::getSession($sessionId);
        $session->forceDisconnect($connectionId);
        return Response::json(['disconnected' => true], 200);
    }


    /**
     * @param  StartRecordingRequest  $request
     * @return string
     * @throws OpenViduInvalidArgumentException
     */
    public function startRecording(StartRecordingRequest $request)
    {
        $recording = OpenVidu::startRecording(RecordingPropertiesBuilder::build($request->all()));
        return Response::json(['recording' => $recording], 200);
    }


    /**
     * @param  string  $recordingId
     * @return string
     */
    public function stopRecording(string $recordingId)
    {
        $recording = OpenVidu::stopRecording($recordingId);
        return Response::json(['recording' => $recording], 200);
    }

    /**
     * @param  string  $recordingId
     * @return string
     */
    public function recording(string $recordingId)
    {
        $recording = OpenVidu::getRecording($recordingId);
        return Response::json(['recording' => $recording], 200);
    }


    /**
     * @param  string  $recordingId
     * @return string
     */
    public function deleteRecording(string $recordingId)
    {
        $recording = OpenVidu::deleteRecording($recordingId);
        return Response::json(['recording' => $recording], 200);
    }

    /**
     * @param  SignalRequest  $request
     * @return string
     * @throws OpenViduInvalidArgumentException
     */
    public function signal(SignalRequest $request)
    {
        $sent = OpenVidu::sendSignal(SignalPropertiesBuilder::build($request->all()));
        return Response::json(['sent' => $sent], 200);
    }

    /**
     * @param  WebhookEventRequest  $request
     * @return string
     */
    public function webhook(WebhookEventRequest $request)
    {
        WebhookEventDispatcher::dispatch($request->all());
        return Response::json(['success' => true], 200);
    }
}
