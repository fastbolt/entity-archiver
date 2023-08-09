<?php

namespace Fastbolt\EntityArchiverBundle\Filter;

use Fastbolt\EntityArchiverBundle\Model\EntityArchivingConfiguration;

interface EntityArchivingFilterInterface
{
    /**
     * Returns the name of the filter as written in the entity-archiver.yaml
     *
     * @return string
     */
    public function getName(): string;

    /**
     * Modifies the passed query string, usually by adding a condition at the end of it
     *
     * @param string                       $query
     * @param EntityArchivingConfiguration $configuration
     *
     * @return void
     */
    public function apply(
        string &$query,
        EntityArchivingConfiguration $configuration
    ): void;
}
