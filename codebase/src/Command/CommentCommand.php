<?php

namespace App\Command;

use App\Service\CommentManager;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CommentCommand extends Command
{
    private CommentManager $commentManager;

    protected static $defaultName = 'app:article:comment';
    protected static $defaultDescription = "Get Article comments and manage each comment's visibility";

    public function __construct(CommentManager $commentManager)
    {
        parent::__construct();
        $this->commentManager = $commentManager;
    }
    protected function configure(): void
    {
        $this->addOption(
            'article',
            'a',
            InputOption::VALUE_OPTIONAL,
            'What article id do you want to show Comments for (If not set, the list of all comments will be rendered)?',
            null
        );
        $this->addOption(
            'comment',
            'c',
            InputOption::VALUE_IS_ARRAY | InputOption::VALUE_REQUIRED,
            'What comment id do you want to select (separate multiple ids with a space)?'
        );
        $this->addOption(
            'list',
            'l',
            InputOption::VALUE_NONE,
            'If set, the list of comments will be rendered'
        );
        $this->addOption(
            'show',
            null ,
            InputOption::VALUE_NEGATABLE,
            'Set the comments visibility (--show to make it visible | --no-show otherwise)'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $commentsList= [];
        $updatedComments= 0;
        $errors= 0;
        $errorMessage1= '';
        $errorMessage2= '';
        $errorMessage3= '';
        $errorMessage4= '';
        $successMessage= '';
        $articleId = $input->getOption('article');
        $comments = $input->getOption('comment');
        $visibility = $input->getOption('show');
        $renderResultsList = false;
        $commentIdsString = '';
        foreach($comments as $id){
            $commentIdsString.= ' | '.$id;
        }
        $styler = new SymfonyStyle($input, $output);
        
        if ( null !== $comments && count($comments) > 0) {
            $styler->title(sprintf('Getting Comments to update...'));
            $commentsList = $this->commentManager->findCommentsByIds($comments);
            if( false=== $commentsList){
                $errorMessage1= "Error:  No results found for the specified Comments #IDS: $commentIdsString";
                $errors = 1;
            }
            $renderResultsList=true;
            $successMessage= 'Success: Loaded specified Comments!';
            if (null !== $visibility) {
                $updatedComments = $this->commentManager->updateVisibility($commentsList, $visibility);
                $endMessage= 'The command has successfully ended. Updated ' . $updatedComments . ' Results';
                if ($updatedComments <= 0) {
                    $errorMessage2="Error: Something went wrong while Updating Comments visibility!";
                    $errors = 2;
                }
                if(false===$input->getOption('list')){
                    $styler->success($endMessage);
                    return Command::SUCCESS;
                }
            }

        }else{
            if ( null !== $articleId ) {
                $styler->title(sprintf('Getting Comments for article #ID: %s', $articleId));
                $commentsList = $this->commentManager->findArticleComments($articleId);
                if( false=== $commentsList){
                    $errorMessage3= "Error: No comments found for article #ID:  $articleId";
                    $errors = 3;
                }
                $successMessage= "Success: Loaded All Comments for Article #ID: $articleId";
            }else{
                $styler->title(sprintf('Getting All Comments'));
                $commentsList = $this->commentManager->findAllComments();
                if( false=== $commentsList){
                    $errorMessage4= "Error: No comments found in database";
                    $errors = 4;
                }
                $successMessage= "Success: Loaded All Comments from database";
            }
            $renderResultsList=true;
        }
        if ($errors>0) {
            $errorMessage = 'errorMessage'.$errors;
            $styler->error($$errorMessage);
            return Command::FAILURE;
        }
        $endMessage= (count($commentsList)>0) ? 
        'The command has successfully ended. Found ' . count($commentsList) . ' Results':
        'The command has successfully ended. No Results Found!';
        $this->renderProgressBar($styler, $commentsList, $successMessage);
        
        if (true === $renderResultsList){
            $this->renderResultsInTableFormat($commentsList, $styler, $endMessage);
        }
        $styler->info($endMessage);
        return Command::SUCCESS;

    }

    private function renderResultsInTableFormat($commentsList, $styler){
        $headers = ['#ID', 'User name', 'User Email', 'Message', 'Visible', 'Created At'];
        $rows = [];
        foreach ($commentsList as $comment) {
            $visible = ($comment->getVisible()) ? 'Visible' : 'Invisible';
            $rows[] = [
                $comment->getId(), 
                $comment->getUserName(), 
                $comment->getUserEmail(), 
                $comment->getMessage(), 
                $visible, 
                $comment->getCreatedAt()->format('M, d Y')
            ];
        }
        $table = $styler->createTable();
        $table
            ->setHeaders($headers)
            ->setRows($rows)
            ->setColumnWidths([5, 20, 30, 100, 10, 10])
            ->setColumnMaxWidth(0, 5)
            ->setColumnMaxWidth(1, 30)
            ->setColumnMaxWidth(2, 40)
            ->setColumnMaxWidth(3, 100)
            ->setColumnMaxWidth(4, 10)
            ->setColumnMaxWidth(5, 20)
            ->render()
        ;
    }

    private function renderProgressBar ($styler, $commentsList, $successMessage){
        $styler->progressStart(count($commentsList));
        for ($i = 1; $i <= count($commentsList); $i++) {
            $styler->progressAdvance(1);
        }
        $styler->progressFinish();
        $styler->success($successMessage);
    }
    
}
