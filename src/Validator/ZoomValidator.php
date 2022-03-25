<?php


namespace App\Validator;

use App\Service\ZoomClient;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * Class ZoomValidator
 * @param ContainerInterface $container Application container
 * @package App\Validator
 */
class ZoomValidator extends ConstraintValidator
{
    public $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function validate($webinar, Constraint $constraint)
    {
        if (!$constraint instanceof ContainsZoomMeeting) {
            throw new UnexpectedTypeException($constraint, ContainsZoomMeeting::class);
        }

        if ($webinar->getStartDate() > new \DateTime('now')) {
            if ($webinar->getMeetingId()) {
                $meetingId = $webinar->getMeetingId();
                $zoom = new ZoomClient($this->container, $this->setZoomOptions($webinar));
                $response = $zoom->doRequest('GET', "/meetings/$meetingId/");
                if (array_key_exists('code', $response) && $response['code'] == 3001
                || array_key_exists('code',$response)) {
                    $zoomWebinarResponse = $zoom->doRequest('GET', "/webinars/$meetingId");
                    if ((array_key_exists('code',$zoomWebinarResponse) && $zoomWebinarResponse['code'] == 3001) ||
                    array_key_exists('code',$zoomWebinarResponse)){
                        $this->context->buildViolation($constraint->message)
                            ->atPath('meetingId')
                            ->addViolation();
                    }
                }
            }

        }
    }

    public function setZoomOptions($webinar)
    {
        $options = [];
        if ($webinar->getUsePartnerApiKeys() && !empty($webinar->getPartners()->toArray())) {
            foreach ($webinar->getPartners() as $partner) {
                if (!is_null($partner->getZoomApiKey()) && !is_null($partner->getZoomClientSecret())) {
                    $options = [
                        'apiKey' => $partner->getZoomApiKey(),
                        'apiSecret' => $partner->getZoomClientSecret()
                    ];
                    break;
                }
            }
        }
        return $options;
    }
}