<?php

namespace App\Serializer\Normalizer;

use App\Entity\Appointment;
use Symfony\Component\Serializer\Normalizer\ContextAwareNormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\SerializerAwareInterface;
use Symfony\Component\Serializer\SerializerAwareTrait;

class AppointmentNormalizer implements ContextAwareNormalizerInterface, SerializerAwareInterface
{
    use SerializerAwareTrait;

    public function normalize($object, $format = null, array $context = [])
    {
        if (!$this->supportsNormalization($object, $format, $context)) {
            return null;
        }

        // Use serializer directly
        $data = $this->serializer->normalize($object, $format, $context);

        // Custom serialization logic
        if ($object instanceof Appointment) {
            $data['user'] = $this->serializer->normalize($object->getUser(), $format, ['groups' => 'user:read']);
        }

        return $data;
    }

    public function supportsNormalization($data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof Appointment;
    }
}
