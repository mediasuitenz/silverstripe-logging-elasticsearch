<?php

/*
 * This file is a modification of a standard monolog handler to be compatible with the latest version of Elastica. So as can be used in SS4 projects
 *
 * (c) Robbie Mcclintock <robbie@mediasuite.co.nz>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mediasuite\MsSsLoggingElasticsearch;

use Elastica\Document;
use Monolog\Formatter\NormalizerFormatter;

/**
 * Format a log message into an Elastica Document
 *
 * @author Jelle Vink <jelle.vink@gmail.com>
 */
class CustomESFormatter extends NormalizerFormatter
{
    /**
     * @var string Elastic search index name
     */
    protected $index;

    /**
     * @var string Elastic search document type
     */
    protected $type;

    /**
     * @param string $index Elastic Search index name
     * @param string $type  Elastic Search document type
     */
    public function __construct($index, $type)
    {
        // elasticsearch requires a ISO 8601 format date with optional millisecond precision.
        parent::__construct('Y-m-d\TH:i:s.uP');

        $this->index = $index;
        $this->type = $type;
    }

    /**
     * {@inheritdoc}
     */
    public function format(array $record)
    {
        $record = parent::format($record);

        return $this->getDocument($record);
    }

    /**
     * Getter index
     * @return string
     */
    public function getIndex()
    {
        return $this->index;
    }

    /**
     * Getter type
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Convert a log message into an Elastica Document
     *
     * @param  array    $record Log message
     * @return Document
     */
    protected function getDocument($record)
    {
        $document = new Document();
        $document->setData($record);
        if (method_exists($document, 'setType')) {
            $document->setType($this->type);
        }
        $document->setIndex($this->index);

        return $document;
    }
}
