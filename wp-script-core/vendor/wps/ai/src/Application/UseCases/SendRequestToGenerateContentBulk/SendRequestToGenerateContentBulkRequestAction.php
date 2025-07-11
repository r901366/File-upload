<?php

namespace WPS\Ai\Application\UseCases\SendRequestToGenerateContentBulk;

class SendRequestToGenerateContentBulkRequestAction
{
    /**
     * @var string
     */
    private $actionName;

    /**
     * @var string
     */
    private $videoId;

    /**
     * @var string
     */
    private $partnerId;

    /**
     * @var string
     */
    private $videoTitle;

    /**
     * Constructor.
     *
     * @param string $actionName Action name.
     * @param string $videoId Video ID.
     * @param string $partnerId Partner ID.
     * @param string $videoTitle Video title.
     */
    public function __construct($actionName, $videoId, $partnerId, $videoTitle)
    {
        $this->actionName = $actionName;
        $this->videoId = $videoId;
        $this->partnerId = $partnerId;
        $this->videoTitle = $videoTitle;
    }

    /**
     * Get the action name.
     *
     * @return string
     */
    public function getActionName()
    {
        return $this->actionName;
    }

    /**
     * Get the video ID.
     *
     * @return string
     */
    public function getVideoId()
    {
        return $this->videoId;
    }

    /**
     * Get the partner ID.
     *
     * @return string
     */
    public function getPartnerId()
    {
        return $this->partnerId;
    }

    /**
     * Get the video title.
     *
     * @return string
     */
    public function getVideoTitle()
    {
        return $this->videoTitle;
    }
}
