<?php

namespace GeoSocio\Core\ArgumentResolver;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ArgumentValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\Serializer\Encoder\DecoderInterface;

class InputResolver implements ArgumentValueResolverInterface
{

    /**
     * @var DecoderInterface
     */
    protected $decoder;

    public function __construct(DecoderInterface $decoder)
    {
        $this->decoder = $decoder;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(Request $request, ArgumentMetadata $argument) : bool
    {
        if ($argument->getType() !== "array") {
            return false;
        }

        if ($argument->getName() !== 'input') {
            return false;
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function resolve(Request $request, ArgumentMetadata $argument)
    {
        yield $this->decoder->decode($request->getContent(), $request->getRequestFormat('json'));
    }
}
