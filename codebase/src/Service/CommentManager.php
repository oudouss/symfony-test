<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Comment;
use Doctrine\ORM\EntityManagerInterface;

class CommentManager
{
    private EntityManagerInterface $manager;

    public function __construct( EntityManagerInterface $manager)
    {
        $this->manager = $manager;
    }

    public function findArticleComments($articleId, $visible = null)
    {
        /** @var CommentRepositoty $repository */
        $repository = $this->manager->getRepository(Comment::class);
        $comments=[];
            if (null !== $visible){
                $comments = $repository->findByArticleAndVisibility($articleId, $visible);
            }else{
                $comments = $repository->findBy(['article' => $articleId]);
            }  

        return (null == $comments) ?  false :  $comments;
    }

    /**
     *
     * @return Comment[]
     */
    public function findAllComments(): array
    {
        return $this->manager
            ->getRepository(Comment::class)
            ->findall();
    }

    /**
     * @param array $commentIds
     * @return Comment[]
     */
    public function findCommentsByIds($commentIds): array
    {
        $comments = [];
        foreach ($commentIds as $id){
            $comment = $this->manager->find(Comment::class,$id);
            if($comment){
                $comments[] = $comment;
            }
        }
        return $comments;
    }

    /**
     * @param Comment[] $comments
     * @param boolean $visibility
     * @return int
     */
    public function updateVisibility($comments, $visibility): int
    {
        $count = 0;
        foreach ($comments as $comment){
            $comment->setVisible($visibility);
            $this->manager->persist($comment);
            $count ++;
        }
        $this->manager->flush();
        return $count;
    }


}
