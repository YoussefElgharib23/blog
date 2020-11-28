<?php


namespace App\Message;


class StoreUserComment
{
    private int $userId;
    private int $postId;
    private string $commentContent;

    public function __construct(
        int $userId,
        int $postId,
        string $commentContent
    )
    {
        $this->userId = $userId;
        $this->postId = $postId;
        $this->commentContent = $commentContent;
    }

    /**
     * @return int
     */
    public function getUserId(): int
    {
        return $this->userId;
    }

    /**
     * @return int
     */
    public function getPostId(): int
    {
        return $this->postId;
    }

    /**
     * @return string
     */
    public function getCommentContent(): string
    {
        return $this->commentContent;
    }
}