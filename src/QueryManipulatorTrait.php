<?php

namespace Fastbolt\EntityArchiverBundle;

use DateTime;

trait QueryManipulatorTrait
{
    protected function getConditionTemplate(string $query): string
    {
        return str_contains($query, ' WHERE ') ? ' AND ' : ' WHERE ';
    }

    protected function formatDate(DateTime $date): string
    {
        return $date->format('Y-m-d H:i:s');
    }

    protected function removeSpecialChars(string $query): string {
        return preg_replace('/[*\/+#\\\\]/', '', $query);
    }
}
