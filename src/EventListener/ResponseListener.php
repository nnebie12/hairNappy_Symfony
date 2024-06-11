<?php
// src/EventListener/ResponseListener.php

namespace App\EventListener;

use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpFoundation\JsonResponse;

class ResponseListener
{
    public function onKernelResponse(ResponseEvent $event)
    {
        $response = $event->getResponse();

        // Vérifiez si la réponse est un JsonResponse
        if ($response instanceof JsonResponse) {
            $content = $response->getContent();

            // Nettoyez les messages de dépréciation PHP du contenu JSON
            $jsonMatch = preg_match('/\[{.*}\]/s', $content, $matches);
            if ($jsonMatch && isset($matches[0])) {
                $cleanJson = $matches[0];
                $response->setContent($cleanJson);
            }
        }
    }
}
