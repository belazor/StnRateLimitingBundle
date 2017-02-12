<?php

namespace Stn\RateLimitingBundle\Component;

use Exception;
use Throwable;
use Symfony\Component\HttpFoundation\Response;

/**
 * Object describing an API-Problem payload.
 */
class ApiProblem
{
    /**
     * Content type for api problem response
     */
    const CONTENT_TYPE = 'application/problem+json';

    /**
     * Additional details to include in report.
     *
     * @var array
     */
    protected $additionalDetails = [];

    /**
     * URL describing the problem type; defaults to HTTP status codes.
     *
     * @var string
     */
    protected $type = 'http://www.w3.org/Protocols/rfc2616/rfc2616-sec10.html';

    /**
     * Description of the specific problem.
     *
     * @var string|Exception|Throwable
     */
    protected $detail = '';

    /**
     * HTTP status for the error.
     *
     * @var int
     */
    protected $status;

    /**
     * Normalized property names for overloading.
     *
     * @var array
     */
    protected $normalizedProperties = [
        'type' => 'type',
        'status' => 'status',
        'title' => 'title',
        'detail' => 'detail',
    ];

    /**
     * Title of the error.
     *
     * @var string
     */
    protected $title;

    /**
     * Constructor.
     *
     * Create an instance using the provided information. If nothing is
     * provided for the type field, the class default will be used;
     * if the status matches any known, the title field will be selected
     * from Response::$statusTexts as a result.
     *
     * @param int    $status
     * @param string|Exception|Throwable $detail
     * @param string $type
     * @param string $title
     * @param array  $additional
     */
    public function __construct($status, $detail, $type = null, $title = null, array $additional = [])
    {
        // Ensure a valid HTTP status
        if (! is_numeric($status)
            || ($status < 100)
            || ($status > 599)
        ) {
            $status = 500;
        }

        $this->status = $status;
        $this->detail = $detail;
        $this->title = $title;

        if (null !== $type) {
            $this->type = $type;
        }

        $this->additionalDetails = $additional;
    }

    /**
     * Retrieve properties.
     *
     * @param string $name
     * @return mixed
     * @throws \InvalidArgumentException
     */
    public function __get($name)
    {
        $normalized = strtolower($name);
        if (in_array($normalized, array_keys($this->normalizedProperties))) {
            $prop = $this->normalizedProperties[$normalized];

            return $this->{$prop};
        }

        if (isset($this->additionalDetails[$name])) {
            return $this->additionalDetails[$name];
        }

        if (isset($this->additionalDetails[$normalized])) {
            return $this->additionalDetails[$normalized];
        }

        throw new \InvalidArgumentException(sprintf(
            'Invalid property name "%s"',
            $name
        ));
    }

    /**
     * Cast to an array.
     *
     * @return array
     */
    public function toArray()
    {
        $problem = [
            'type' => $this->type,
            'title' => $this->getTitle(),
            'status' => $this->getStatus(),
            'detail' => $this->getDetail(),
        ];
        // Required fields should always overwrite additional fields
        return array_merge($this->additionalDetails, $problem);
    }

    /**
     * Retrieve the API-Problem detail.
     *
     * If an exception was provided, creates the detail message from it;
     * otherwise, detail as provided is used.
     *
     * @return string
     */
    protected function getDetail()
    {
        if ($this->detail instanceof Throwable || $this->detail instanceof Exception) {
            return $this->createDetailFromException();
        }

        return $this->detail;
    }

    /**
     * Retrieve the API-Problem HTTP status code.
     *
     * If an exception was provided, creates the status code from it;
     * otherwise, code as provided is used.
     *
     * @return string
     */
    protected function getStatus()
    {
        if ($this->detail instanceof Throwable || $this->detail instanceof Exception) {
            $this->status = $this->createStatusFromException();
        }

        return $this->status;
    }

    /**
     * Retrieve the title.
     *
     * If the default $type is used, and the $status is found in
     * Response::$statusTexts, then use the matching title.
     *
     * If no title was provided, and the above conditions are not met, use the
     * string 'Unknown'.
     *
     * Otherwise, use the title provided.
     *
     * @return string
     */
    protected function getTitle()
    {
        if (null !== $this->title) {
            return $this->title;
        }

        if (null === $this->title
            && $this->type == 'http://www.w3.org/Protocols/rfc2616/rfc2616-sec10.html'
            && array_key_exists($this->getStatus(), Response::$statusTexts)
        ) {
            return Response::$statusTexts[$this->status];
        }

        if ($this->detail instanceof Throwable || $this->detail instanceof Exception) {
            return get_class($this->detail);
        }

        if (null === $this->title) {
            return 'Unknown';
        }

        return $this->title;
    }

    /**
     * Create detail message from an exception.
     *
     * @return string
     */
    protected function createDetailFromException()
    {
        return $this->detail->getMessage();
    }

    /**
     * Create HTTP status from an exception.
     *
     * @return int
     */
    protected function createStatusFromException()
    {
        /** @var Exception|Throwable $e */
        $e = $this->detail;
        $status = $e->getCode();

        if ($status) {
            return $status;
        }

        return 500;
    }
}
