<?php


namespace App\Message;


class PostViewsIncerement
{
    private int $postId;

    public function __construct(int $postId)
    {
        $this->postId = $postId;
    }

    /**
     * @return int
     */
    public function getPostId(): int
    {
        return $this->postId;
    }
}