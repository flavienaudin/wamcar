<?php


namespace AppBundle\Form\Validator\Constraints;


use GoogleApi\GoogleYoutubeApiService;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class YoutubePlaylistExistingValidator extends ConstraintValidator
{

    /** @var GoogleYoutubeApiService */
    private $googleYoutubeApiService;
    /** @var TranslatorInterface */
    private $translation;

    /**
     * YoutubePlaylistExistingValidator constructor.
     * @param GoogleYoutubeApiService $googleYoutubeApiService
     * @param TranslatorInterface $translation
     */
    public function __construct(GoogleYoutubeApiService $googleYoutubeApiService, TranslatorInterface $translation)
    {
        $this->googleYoutubeApiService = $googleYoutubeApiService;
        $this->translation = $translation;
    }


    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof YoutubePlaylistExisting) {
            throw new UnexpectedTypeException($constraint, YoutubePlaylistExisting::class);
        }

        if (!is_string($value)) {
            throw new UnexpectedTypeException($value, 'string');
        }

        $playlistData = $this->googleYoutubeApiService->fetchPlayListData($value);
        if ($playlistData->getPageInfo()->getTotalResults() === 0) {
            $this->context->buildViolation($this->translation->trans($constraint->message, [], "validations"))
                ->atPath('playlistId')
                ->addViolation();
        }
    }
}