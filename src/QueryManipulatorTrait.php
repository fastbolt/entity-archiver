<?php

namespace Fastbolt\EntityArchiverBundle;

use DateTime;

trait QueryManipulatorTrait
{
    /**
     * @param string $query
     *
     * @return string
     */
    protected function getConditionTemplate(string $query): string
    {
        return str_contains($query, ' WHERE ') ? ' AND ' : ' WHERE ';
    }

    /**
     * @param DateTime $date
     *
     * @return string
     */
    protected function formatDate(DateTime $date): string
    {
        return $date->format('Y-m-d H:i:s');
    }

    /**
     * @param string $query
     *
     * @return string
     */
    protected function removeSpecialChars(string $query): string
    {
        return preg_replace('/[*\/+#\\\\]/', '', $query);
    }

    protected function escapeQuotationMarks(string $string): string
    {
        $string = preg_replace('/["]/', '\\\\\"', $string);
        return preg_replace("/[']/", "\'", $string);
    }
}
