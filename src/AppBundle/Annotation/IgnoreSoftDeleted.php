<?php

namespace AppBundle\Annotation;


use Doctrine\Common\Annotations\Annotation;
use Doctrine\ORM\Mapping as ORM;

/**
 * Attention! Désactive le filtre "SoftDeletable" pour toute l'action du controller.
 * Par exemple, un garage non supprimé afficherait les véhicules supprimé(s)
 * A utiliser avec précaution. Il est recommander d'utiliser @ORM\Entity("entity", expr="repository.findIgnoreSoftdeleted...(url_param)")
 * avec la méthode "findIgnoreSoftdeleted..." présente dans le repository de l'entité via le SoftDeletableEntityRepositoryTrait
 *
 * @Annotation
 * @Annotation\Target({"CLASS", "METHOD"})
 */
class IgnoreSoftDeleted extends Annotation
{
}