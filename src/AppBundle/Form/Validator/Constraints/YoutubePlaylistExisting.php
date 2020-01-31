<?php


namespace AppBundle\Form\Validator\Constraints;


use Symfony\Component\Validator\Constraint;

class YoutubePlaylistExisting extends Constraint
{
    public $message = 'constraint.youtube.playlist.notfound';
}